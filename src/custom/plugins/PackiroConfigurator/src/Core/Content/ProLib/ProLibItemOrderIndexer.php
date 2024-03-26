<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProLibItemOrderIndexer extends EntityIndexer
{
    public function __construct(
        private Connection $connection,
        private IteratorFactory $iteratorFactory,
        private EntityRepositoryInterface $repository,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getName(): string
    {
        return 'pc_pro_lib_item_order.indexer';
    }

    public function iterate($offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new ProLibItemOrderIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $entityEvent = $event->getEventByEntityName(ProLibItemOrderDefinition::ENTITY_NAME);
        if (!$entityEvent) {
            return null;
        }

        $ids = $entityEvent->getIds();

        foreach ($entityEvent->getWriteResults() as $result) {
            if (!$result->getExistence()) {
                continue;
            }
        }

        if (empty($ids)) {
            return null;
        }

        return new ProLibItemOrderIndexingMessage(array_values($ids), null, $event->getContext(), \count($ids) > 20);
    }

    public function getTotal(): int
    {
        return $this->getIterator(null)->fetchCount();
    }

    public function getDecorated(): EntityIndexer
    {
        throw new DecorationPatternException(self::class);
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();
        $ids = array_unique(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $sql = <<<SQL
SELECT 
    LOWER(HEX(`pc_pro_lib_item_order`.`id`)) AS id,
    LOWER(HEX(`pc_pro_lib_item_order`.`order_id`)) as orderId,
    LOWER(HEX(`pc_pro_lib_item`.`pc_pro_lib_group_id`)) as proLibGroupId,
    LOWER(HEX(`pc_pro_lib_item`.`id`)) as proLibItemId
FROM `pc_pro_lib_item_order`
LEFT JOIN `pc_pro_lib_item`
ON `pc_pro_lib_item`.`id` = `pc_pro_lib_item_order`.`pc_pro_lib_item_id`
    
WHERE `pc_pro_lib_item_order`.`id` IN (:ids);
SQL;
        $data = $this->connection->fetchAll(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => Connection::PARAM_STR_ARRAY]
        );

        foreach ($data as $item) {
            /* If null, skip indexer */
            if (!$item['proLibItemId'] || !$item['orderId'] || !$item['proLibGroupId']) {
                continue;
            }

            /* Set last order id and last item id */
            $sql = <<<SQL
UPDATE `pc_pro_lib_group` 
SET 
    `last_order_item_id` = :proLibItemId, 
    `last_item_order_id` = :id 
WHERE `id` = :proLibGroupId;
SQL;
            $this->connection->executeStatement($sql, [
                'id' => Uuid::fromHexToBytes($item['id']),
                'proLibItemId' => Uuid::fromHexToBytes($item['proLibItemId']),
                'proLibGroupId' => Uuid::fromHexToBytes($item['proLibGroupId']),
                'orderId' => Uuid::fromHexToBytes($item['orderId']),
            ]);
        }

        $context = $message->getContext();

        $this->eventDispatcher->dispatch(new ProLibItemOrderIndexerEvent($ids, $context));
    }

    private function getIterator(?array $offset): IterableQuery
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);
    }
}
