<?php

declare(strict_types=1);

namespace Packiro\Checkout\Writer\Cart;

use Doctrine\DBAL\Connection;
use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\SplitCartExtension;
use Packiro\Checkout\Reader\Cart\CartReaderInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class CartPersisterDecorator extends AbstractCartPersister
{
    public function __construct(
        private AbstractCartPersister $decorated,
        private Connection $connection,
        private CartReaderInterface $cartReader,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getDecorated(): AbstractCartPersister
    {
        return $this->decorated;
    }

    public function load(string $token, SalesChannelContext $context): Cart
    {
        return $this->decorated->load($token, $context);
    }

    public function save(Cart $cart, SalesChannelContext $context): void
    {
        $this->decorated->save($cart, $context);

        $this->savePcCart($cart);
    }

    public function delete(string $token, SalesChannelContext $context): void
    {
        $request = Request::createFromGlobals();
        $this->logger->info('Cart delete method', [
            'token' => $token,
            'salesChannelContext' => [
                'token' => $context->getToken(),
                'customerId' => $context->getCustomerId(),
            ],
            'requestUri' => $request->getRequestUri(),
            'headers' => [
                'sw-context-token' => $request->headers->get('sw-context-token'),
                'referer' => $request->headers->get('referer'),
            ],
        ]);

        $this->decorated->delete($token, $context);

        /** @var Cart $splitCart */
        foreach ($this->cartReader->findSplitCarts($token) as $splitCart) {
            $this->decorated->delete($splitCart->getToken(), $context);
            $this->deletePcCart($splitCart->getToken());

            $this->logger->info('Split Cart has been deleted', [
                'parentToken' => $token,
                'splitCartToken' => $splitCart->getToken(),
            ]);
        }

        $this->deletePcCart($token);
    }

    public function replace(string $oldToken, string $newToken, SalesChannelContext $context): void
    {
        $request = Request::createFromGlobals();
        $this->logger->info('Cart replace method', [
            'oldToken' => $oldToken,
            'newToken' => $newToken,
            'salesChannelContext' => [
                'token' => $context->getToken(),
                'customerId' => $context->getCustomerId(),
            ],
            'requestUri' => $request->getRequestUri(),
            'headers' => [
                'sw-context-token' => $request->headers->get('sw-context-token'),
                'referer' => $request->headers->get('referer'),
            ],
        ]);

        $this->decorated->replace($oldToken, $newToken, $context);

        $this->replacePcCart($oldToken, $newToken);
    }

    private function savePcCart(Cart $cart): void
    {
        if (!$cart->hasExtension(SplitCartExtension::EXTENSION_PROPERTY_NAME)) {
            return;
        }

        /** @var SplitCartExtension $splitCartExtension */
        $splitCartExtension = $cart->getExtension(SplitCartExtension::EXTENSION_PROPERTY_NAME);

        if (!$this->shouldBePersisted($cart->getToken())) {
            $this->deletePcCart($splitCartExtension->getCartToken());

            $this->logger->info('Cart is not persistent', [
                'splitCartToken' => $splitCartExtension->getCartToken(),
                'parentToken' => $splitCartExtension->getParentToken(),
            ]);

            return;
        }

        $pcCart = $this->cartReader->findPcCartByToken($cart->getToken());
        if ($pcCart !== null) {
            return;
        }

        $sql = <<<'SQL'
            INSERT INTO `pc_cart` (`cart_token`, `parent_token`, `product_variant_id`, `accessory_option_id`)
            VALUES (:cartToken, :parentToken, :productVariantId, :accessoryOptionId)
        SQL;

        $data = [
            'cartToken' => $splitCartExtension->getCartToken(),
            'parentToken' => $splitCartExtension->getParentToken(),
            'productVariantId' => Uuid::fromHexToBytes($splitCartExtension->getProductVariantId()),
            'accessoryOptionId' => Uuid::fromHexToBytes($splitCartExtension->getAccessoryOptionId()),
        ];

        $query = new RetryableQuery($this->connection, $this->connection->prepare($sql));
        $query->execute($data);
    }

    private function deletePcCart(string $token): void
    {
        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare('DELETE FROM `pc_cart` WHERE `cart_token` = :token')
        );
        $query->execute(['token' => $token]);
    }

    private function replacePcCart(string $oldToken, string $newToken): void
    {
        $sqlQuery = <<<'SQL'
            UPDATE `pc_cart` SET `cart_token` = :newToken WHERE `cart_token` = :oldToken;
            UPDATE `pc_cart` SET `parent_token` = :newToken WHERE `parent_token` = :oldToken;
        SQL;
        $queryParams = [
            'newToken' => $newToken,
            'oldToken' => $oldToken,
        ];

        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare($sqlQuery)
        );
        $query->execute($queryParams);
    }

    private function shouldBePersisted(string $cartToken): bool
    {
        $content = $this->connection->fetchOne(
            'SELECT `token` FROM `cart`
                WHERE `cart`.`token` = :cartToken;',
            [
                'cartToken' => $cartToken,
            ]
        );

        return is_string($content);
    }
}
