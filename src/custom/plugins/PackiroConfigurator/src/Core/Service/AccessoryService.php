<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Service;

use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryGroupCollection;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryGroupDefinition;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionCollection;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionDefinition;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionEntity;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\SalesChannelAccessoryOptionEntity;
use Kuniva\PackiroConfigurator\Core\Content\Product\PcProductEntity;
use Kuniva\PackiroConfigurator\Core\Framework\BusinessDayCalculator\BusinessDayCalculator;
use Monolog\Logger;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

class AccessoryService
{
    private ?SalesChannelContext $salesChannelContext = null;
    private ?Context $context = null;
    private ?AccessoryGroupCollection $accessoryGroups = null;

    public function __construct(
        private Logger $logger,
        private DefinitionInstanceRegistry $definitionInstanceRegistry,
        private SystemConfigService $systemConfigService,
        private AbstractProductPriceCalculator $productPriceCalculator,
        private OrderConverter $orderConverter,
        private AbstractCartPersister $cartPersister
    ) {
        $this->context = Context::createDefaultContext();
    }

    public function init(): void
    {
        if (!$this->accessoryGroups) {
            /* Clone AccessoryGroupCollection to prevent recursion but hold all required data */
            $accessoryGroupRepository = $this->definitionInstanceRegistry->getRepository(AccessoryGroupDefinition::ENTITY_NAME);
            /** @var AccessoryGroupCollection $accessoryGroups */
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('active', 1));

            $accessoryGroups = $accessoryGroupRepository->search($criteria, $this->context)->getEntities();
            $this->accessoryGroups = $accessoryGroups;
        }
    }

    public function getAccessoryOptionIdsByProperty(
        ?array $payload = null,
        string $propertyName = 'technicalName',
        bool $flatten = true
    ): ?array {
        if (empty($payload)) {
            return null;
        }

        if (empty($payload[0][$propertyName])) {
            return null;
        }

        $filterValues = array_map(
            fn(array $item): string => $item[$propertyName],
            $payload
        );

        $criteria = new Criteria();
        $criteria->setLimit(count($payload));
        $criteria->addFilter(new EqualsAnyFilter($propertyName, $filterValues));

        $accessoryOptionRepository = $this->definitionInstanceRegistry->getRepository(AccessoryOptionDefinition::ENTITY_NAME);

        $ids = $accessoryOptionRepository->searchIds($criteria, $this->context)->getIds();

        if ($flatten) {
            return array_map(function ($id) {
                return ['id' => $id];
            }, $ids);
        }

        return $ids;
    }

    public function enrichCalculatedPrice(iterable $accessoryOptions): void
    {
        /** @var SalesChannelAccessoryOptionEntity $accessoryOption */
        foreach ($accessoryOptions as $accessoryOption) {
            $accessoryOption->setCalculatedPrices(new PriceCollection());
        }

        $this->productPriceCalculator->calculate($accessoryOptions, $this->salesChannelContext);
    }

    /**
     * @deprectaed Replaced by LineItemFactory
     */
    public function addLineItem(Request $request, Cart $cart, SalesChannelContext $salesChannelContext): bool
    {
        /** @var RequestDataBag|null $lineItemsData */
        $lineItemsData = $request->get('lineItems');
        if (!$lineItemsData) {
            throw new MissingRequestParameterException('lineItems');
        }

        foreach ($lineItemsData as $lineItemData) {
            if (!isset($lineItemData['accessoryGroups'])) {
                return false;
            }
        }

        return true;
    }

    public function enrichSalesChannelProductCriteria(?Criteria $criteria = null, ?SalesChannelContext $salesChannelContext = null): Criteria
    {
        if (!$criteria) {
            $criteria = new Criteria();
        }

        $criteria->addAssociations([
            'accessoryOptions.accessoryGroup',
            'accessoryOptions.media',
        ]);

        $accessoryOptionsCriteria = $criteria->getAssociation('accessoryOptions');
        $accessoryOptionsCriteria->addFilter(new EqualsFilter('accessoryGroup.active', 1));

        return $criteria;
    }

    public function overrideSalesChannelAccessory(AccessoryOptionEntity $accessoryOption, SalesChannelContext $salesChannelContext): void
    {
        $this->setSalesChannelContext($salesChannelContext);
    }

    public function overrideSalesChannelProduct(SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): void
    {
        $this->setSalesChannelContext($salesChannelContext);

        $this->init();

        /** @var AccessoryGroupCollection $accessoryGroups */
        $accessoryGroups = $product->getExtension('accessoryGroups');
        if ($accessoryGroups && $accessoryGroups->count()) {
            return;
        }

        if ($product->hasExtension('accessoryOptions') && !$product->hasExtension('accessoryGroups')) {
            /** @var AccessoryOptionCollection $accessoryOptions */
            $accessoryOptions = $product->getExtension('accessoryOptions');
            /** @var PcProductEntity $pcProduct */
            $pcProduct = $product->getExtension('pcProduct');

            $this->enrichAccessoryOptions($accessoryOptions, $pcProduct);

            $accessoryGroups = $accessoryOptions->getTransformed(clone $this->accessoryGroups);
            $product->addExtension('accessoryGroups', $accessoryGroups);
        }
    }

    public function setSalesChannelContext(?SalesChannelContext $salesChannelContext): void
    {
        $this->salesChannelContext = $salesChannelContext;
        $this->context = $salesChannelContext->getContext();
    }

    public function setContext(?Context $context): void
    {
        $this->context = $context;
    }

    private function enrichAccessoryOptions(AccessoryOptionCollection $accessoryOptions, ?PcProductEntity $pcProduct = null): void
    {
        if ($pcProduct) {
            if ($pcProduct->getNotAvailable() && is_array($pcProduct->getNotAvailable())) {
                foreach ($pcProduct->getNotAvailable() as $accessoryOptionId) {
                    /** @var SalesChannelAccessoryOptionEntity $accessoryOption */
                    $accessoryOption = $accessoryOptions->get($accessoryOptionId);
                    if (!$accessoryOption) {
                        continue;
                    }
                    $accessoryOption->setAvailable(false);
                    $accessoryOption->setDisabled(true);
                }
            }

            if ($pcProduct->getNotCombinable() && is_array($pcProduct->getNotCombinable())) {
                foreach ($pcProduct->getNotCombinable() as $accessoryOptionIds) {
                    foreach ($accessoryOptionIds as $accessoryOptionId) {
                        /** @var SalesChannelAccessoryOptionEntity $accessoryOption */
                        $accessoryOption = $accessoryOptions->get($accessoryOptionId);
                        if (!$accessoryOption) {
                            continue;
                        }
                        $accessoryOption->setNotCombinableWith(
                            array_diff($accessoryOptionIds, [$accessoryOptionId])
                        );
                    }
                }
            }
        }

        /** @var SalesChannelAccessoryOptionEntity $accessoryOption */
        foreach ($accessoryOptions as $accessoryOption) {
            if ($accessoryOption->getDeliveryDays()) {
                $calculator = new BusinessDayCalculator(new \DateTime(), [], [
                    BusinessDayCalculator::SATURDAY,
                    BusinessDayCalculator::SUNDAY,
                ]);
                $calculator->addBusinessDays($accessoryOption->getDeliveryDays());

                $accessoryOption->setCalculatedDeliveryDate($calculator->getDateTimeImmutable());
            }
        }
    }
}
