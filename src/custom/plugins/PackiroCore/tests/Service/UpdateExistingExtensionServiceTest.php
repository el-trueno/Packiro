<?php

declare(strict_types=1);

namespace Packiro\Core\Tests\Service;

use Packiro\Core\DAL\Struct\FillUpExistingExtensionStruct;
use Packiro\Core\Service\UpdateExistingExtensionService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Uuid\Uuid;

class UpdateExistingExtensionServiceTest extends TestCase
{
    protected UpdateExistingExtensionService $updateExistingExtensionService;
    protected EntityRepository $productRepository;
    protected EntityRepository $extensionRepository;
    protected FillUpExistingExtensionStruct $fillUpExistingExtension;
    protected EntitySearchResult $entitySearchResult;

    protected string $status = '';

    protected function setUp(): void
    {
        $this->fillUpExistingExtension = $this->createMock(FillUpExistingExtensionStruct::class);
        $this->productRepository = $this->createMock(EntityRepository::class);
        $this->entitySearchResult = $this->createMock(EntitySearchResult::class);
        $this->extensionRepository = $this->createMock(EntityRepository::class);
        $entityWrittenContainerEvent = $this->createMock(EntityWrittenContainerEvent::class);
        $this->extensionRepository->method('search')->willReturn($this->entitySearchResult);
        $this->updateExistingExtensionService = new UpdateExistingExtensionService();
        $this->productRepository->method('upsert')->will(
            $this->returnCallback(function () use ($entityWrittenContainerEvent) {
                $this->status .= 'inserted';

                return $entityWrittenContainerEvent;
            })
        );
        $this->extensionRepository->method('delete')->will(
            $this->returnCallback(
                function () use ($entityWrittenContainerEvent) {
                    $this->status = 'first deleted then ';

                    return $entityWrittenContainerEvent;
                }
            )
        );
        parent::setUp();
    }

    public function testReloadWithoutExtension(): void
    {
        $this->entitySearchResult->method('first')->willReturn(null);
        $this->updateExistingExtensionService->reload(
            $this->fillUpExistingExtension,
            $this->productRepository,
            $this->extensionRepository,
            'test1',
            Context::createDefaultContext()
        );
        $this->assertEquals('inserted', $this->status);
    }

    public function testReloadWithExtension(): void
    {
        $extensionEntity = $this->createMock(Entity::class);
        $this->entitySearchResult->method('first')->willReturn($extensionEntity);
        $this->updateExistingExtensionService->reload(
            $this->fillUpExistingExtension,
            $this->productRepository,
            $this->extensionRepository,
            'test2',
            Context::createDefaultContext()
        );
        $this->assertEquals('first deleted then inserted', $this->status);
    }
}
