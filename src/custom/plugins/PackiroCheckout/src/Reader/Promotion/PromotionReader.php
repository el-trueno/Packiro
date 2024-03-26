<?php

declare(strict_types=1);

namespace Packiro\Checkout\Reader\Promotion;

use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Promotion\Cart\CartPromotionsDataDefinition;
use Shopware\Core\Checkout\Promotion\Gateway\PromotionGatewayInterface;
use Shopware\Core\Checkout\Promotion\Gateway\Template\PermittedGlobalCodePromotions;
use Shopware\Core\Checkout\Promotion\Gateway\Template\PermittedIndividualCodePromotions;
use Shopware\Core\Checkout\Promotion\PromotionCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PromotionReader implements PromotionReaderInterface
{
    private const DEFAULT_ASSOCIATIONS = [
        'personaRules',
        'personaCustomers',
        'cartRules',
        'orderRules',
        'discounts.discountRules',
        'discounts.promotionDiscountPrices',
        'setgroups',
        'setgroups.setGroupRules',
    ];

    public function __construct(
        private PromotionGatewayInterface $promotionGateway,
    ) {
    }

    public function findPromotionsByCodes(
        CartDataCollection $data,
        array $allCodes,
        SalesChannelContext $context
    ): CartPromotionsDataDefinition {
        $keyCacheList = 'parent-promotions-code';

        if (!$data->has($keyCacheList)) {
            $data->set($keyCacheList, new CartPromotionsDataDefinition());
        }

        /** @var CartPromotionsDataDefinition $promotionsList */
        $promotionsList = $data->get($keyCacheList);

        foreach ($promotionsList->getAllCodes() as $code) {
            if (!\in_array($code, $allCodes, true)) {
                $promotionsList->removeCode((string) $code);
            }
        }

        $codesToFetch = [];
        foreach ($allCodes as $code) {
            if ($promotionsList->hasCode($code)) {
                continue;
            }

            $codesToFetch[] = $code;
            $promotionsList->addCodePromotions($code, []);
        }

        if (\count($codesToFetch) > 0) {
            $salesChannelId = $context->getSalesChannel()->getId();

            foreach ($codesToFetch as $currentCode) {
                $globalCriteria = (new Criteria())->addFilter(new PermittedGlobalCodePromotions([$currentCode], $salesChannelId));

                foreach (self::DEFAULT_ASSOCIATIONS as $association) {
                    $globalCriteria->addAssociation($association);
                }

                /** @var PromotionCollection $foundPromotions */
                $foundPromotions = $this->promotionGateway->get($globalCriteria, $context);

                if (\count($foundPromotions->getElements()) <= 0) {
                    $individualCriteria = (new Criteria())->addFilter(new PermittedIndividualCodePromotions([$currentCode], $salesChannelId));

                    foreach (self::DEFAULT_ASSOCIATIONS as $association) {
                        $individualCriteria->addAssociation($association);
                    }

                    /** @var PromotionCollection $foundPromotions */
                    $foundPromotions = $this->promotionGateway->get($individualCriteria, $context);
                }

                if (\count($foundPromotions->getElements()) > 0) {
                    $promotionsList->addCodePromotions($currentCode, $foundPromotions->getElements());
                }
            }
        }

        $data->set($keyCacheList, $promotionsList);

        return $promotionsList;
    }
}
