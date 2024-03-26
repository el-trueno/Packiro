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

class ProLibItemIndexer extends EntityIndexer
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
        return 'pc_pro_lib_item.indexer';
    }

    public function iterate($offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new ProLibItemIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $entityEvent = $event->getEventByEntityName(ProLibItemDefinition::ENTITY_NAME);
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

        return new ProLibItemIndexingMessage(array_values($ids), null, $event->getContext(), \count($ids) > 20);
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

        $sql = 'SELECT 
LOWER(HEX(#entity#.id)) AS id,
LOWER(HEX(#entity#.pc_pro_lib_group_id)) AS proLibGroupId
FROM #entity#
WHERE #entity#.id IN (:ids) AND #entity#.version = 0;';
        $sql = str_replace(['#entity#'], [ProLibItemDefinition::ENTITY_NAME], $sql);

        $data = $this->connection->fetchAll(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => Connection::PARAM_STR_ARRAY]
        );

        foreach ($data as $item) {
            /* Count all not null versions = new version */
            $sql = 'SELECT COUNT(*) AS version FROM #entity# WHERE #entity#.pc_pro_lib_group_id = :proLibGroupId AND #entity#.version IS NOT NULL;';
            $sql = str_replace(['#entity#'], [ProLibItemDefinition::ENTITY_NAME], $sql);

            $data = $this->connection->fetchAll($sql, ['proLibGroupId' => Uuid::fromHexToBytes($item['proLibGroupId'])]);

            /* Set version to current item */
            $sql = 'UPDATE #entity# SET version = :version WHERE id = :id;';
            $sql = str_replace(['#entity#'], [ProLibItemDefinition::ENTITY_NAME], $sql);
            $this->connection->executeStatement($sql, ['id' => Uuid::fromHexToBytes($item['id']), 'version' => $data[0]['version']]);

            /* Set version and last version id count to current group */
            $sql = 'UPDATE #entity# SET version_count = :version, last_version_item_id = :id WHERE id = :proLibGroupId;';
            $sql = str_replace(['#entity#'], [ProLibGroupDefinition::ENTITY_NAME], $sql);
            $this->connection->executeStatement($sql, [
                'id' => Uuid::fromHexToBytes($item['id']),
                'proLibGroupId' => Uuid::fromHexToBytes($item['proLibGroupId']),
                'version' => $data[0]['version'],
            ]);
        }

        $context = $message->getContext();

        $this->eventDispatcher->dispatch(new ProLibItemIndexerEvent($ids, $context));
    }

    private function getIterator(?array $offset): IterableQuery
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);
    }
}
