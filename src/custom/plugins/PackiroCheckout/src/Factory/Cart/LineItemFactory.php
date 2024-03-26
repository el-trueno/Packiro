<?php

declare(strict_types=1);

namespace Packiro\Checkout\Factory\Cart;

use Packiro\Checkout\Struct\Accessory\AccessoryStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use Shopware\Core\Checkout\Promotion\Cart\PromotionItemBuilder;
use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\Content\Rule\RuleCollection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class LineItemFactory implements LineItemFactoryInterface
{
    public const CODE_PREFIX = 'split_promotion.';

    public function __construct(
        private PromotionItemBuilder $promotionItemBuilder,
    ) {
    }

    public function createDiscountLineItems(
        string $originalCode,
        PromotionEntity $promotionEntity,
        Cart $cart,
        SalesChannelContext $salesChannelContext
    ): array {
        if (!$promotionEntity->getDiscounts()) {
            return [];
        }

        $promotionEntity = $this->createSplitPromotionEntity($promotionEntity);
        $code = self::CODE_PREFIX . $originalCode;

        $lineItems = [];
        foreach ($promotionEntity->getDiscounts() as $discount) {
            $splitCartDiscount = $this->createSplitDiscountEntity($discount);

            $factor = 1.0;
            if (!$salesChannelContext->getCurrency()->getIsSystemDefault()) {
                $factor = $salesChannelContext->getCurrency()->getFactor();
            }

            $discountItem = $this->promotionItemBuilder->buildDiscountLineItem(
                $code,
                $promotionEntity,
                $discount,
                $salesChannelContext->getCurrency()->getId(),
                $factor
            );

            $originalCodeItem = $cart->getLineItems()->filter(function (LineItem $item) use ($code) {
                if ($item->getReferencedId() === $code) {
                    return $item;
                }

                return null;
            })->first();

            if ($originalCodeItem && \count($originalCodeItem->getExtensions()) > 0) {
                $discountItem->setExtensions($originalCodeItem->getExtensions());
            }

            $lineItems[] = $discountItem;
        }

        return $lineItems;
    }

    public function createAccessoryLineItem(AccessoryStruct $accessoryStruct): LineItem
    {
        return (new LineItem(
            Uuid::randomHex(),
            $accessoryStruct->getType(),
            $accessoryStruct->getId(),
            (int)max(1, (int)$accessoryStruct->getQuantity()),
        ))->setStackable(true);
    }

    private function createSplitPromotionEntity(PromotionEntity $originalPromotionEntity): PromotionEntity
    {
        $promotionEntity = clone $originalPromotionEntity;
        $promotionEntity->setCartRules(new RuleCollection());
        $promotionEntity->setPersonaRules(new RuleCollection());
        $promotionEntity->setOrderRules(new RuleCollection());

        return $promotionEntity;
    }

    private function createSplitDiscountEntity(PromotionDiscountEntity $originalDiscountEntity): PromotionDiscountEntity
    {
        $splitCartDiscount = clone $originalDiscountEntity;
        $splitCartDiscount->setDiscountRules(new RuleCollection());
        $splitCartDiscount->setId(Uuid::randomHex());

        return $splitCartDiscount;
    }
}
