<?php

declare(strict_types=1);

namespace Packiro\Checkout\Reader\Cart;

use Doctrine\DBAL\Connection;
use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\CartCollection;
use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\SplitCartExtension;
use Shopware\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopware\Core\Framework\Uuid\Uuid;

class CartReader implements CartReaderInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function findSplitCarts(string $parentCartToken): CartCollection
    {
        $content = $this->connection->fetchAllAssociative(
            'SELECT
                    `cart`.`payload`,
                    `cart`.`token`,
                    `cart`.`compressed`
                FROM cart
                JOIN pc_cart ON `pc_cart`.`cart_token` = `cart`.`token`
                WHERE `pc_cart`.`parent_token` = :token;',
            [
                'token' => $parentCartToken,
            ]
        );

        if (!is_array($content)) {
            return new CartCollection();
        }

        $carts = array_map(static function (array $element) {
            return $element['compressed'] ? CacheValueCompressor::uncompress($element['payload']) : unserialize((string) $element['payload']);
        }, $content);

        return new CartCollection($carts);
    }

    public function findPcCart(string $parentCartToken, string $productVariantId, string $accessoryOptionId): ?SplitCartExtension
    {
        return $this->findAndHydratePcCart(
            'SELECT `pc_cart`.`cart_token`, `pc_cart`.`parent_token`, `pc_cart`.`product_variant_id`, `pc_cart`.`accessory_option_id` 
             FROM pc_cart 
             WHERE `parent_token` = :parentToken
             AND `product_variant_id` = :productVariantId     
             AND `accessory_option_id` = :accessoryOptionId',
            [
                'parentToken' => $parentCartToken,
                'productVariantId' => Uuid::fromHexToBytes($productVariantId),
                'accessoryOptionId' => Uuid::fromHexToBytes($accessoryOptionId),
            ]
        );
    }

    public function findPcCartByToken(string $cartToken): ?SplitCartExtension
    {
        return $this->findAndHydratePcCart(
            'SELECT `pc_cart`.`cart_token`, `pc_cart`.`parent_token`, `pc_cart`.`product_variant_id`, `pc_cart`.`accessory_option_id` 
             FROM pc_cart 
             WHERE `cart_token` = :cartToken',
            [
                'cartToken' => $cartToken,
            ]
        );
    }

    private function findAndHydratePcCart(string $sqlQuery, array $parameters): ?SplitCartExtension
    {
        $result = $this->connection->fetchAssociative($sqlQuery, $parameters);
        if (is_array($result)) {
            return new SplitCartExtension(
                $result['cart_token'],
                $result['parent_token'],
                Uuid::fromBytesToHex($result['product_variant_id']),
                Uuid::fromBytesToHex($result['accessory_option_id']),
            );
        }

        return null;
    }
}
