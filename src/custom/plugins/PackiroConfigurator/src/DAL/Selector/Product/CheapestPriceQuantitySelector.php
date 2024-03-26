<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\DAL\Selector\Product;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Core\Content\Product\DataAbstractionLayer\AbstractCheapestPriceQuantitySelector;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CheapestPriceQuantitySelector extends AbstractCheapestPriceQuantitySelector
{
    public const CONFIG_CHEAPEST_PRICE_MAX_QTY_CALCULATION = 'PackiroConfigurator.config.maxQtyForCheapestPriceCalculation';

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public function getDecorated(): AbstractCheapestPriceQuantitySelector
    {
        throw new DecorationPatternException(self::class);
    }

    public function add(QueryBuilder $query): void
    {
        $query->addSelect([
            'price.quantity_start != 1 as is_ranged',
            'price.quantity_end',
        ]);

        $maxQtyCalculation = $this->systemConfigService->get(self::CONFIG_CHEAPEST_PRICE_MAX_QTY_CALCULATION);
        $qtyLimitQuery = '';
        if ($maxQtyCalculation > 0) {
            $qtyLimitQuery = 'AND quantity_start < :maxQtyCalculation';
            $query->setParameter('maxQtyCalculation', $maxQtyCalculation);
        }

        $fromQuery = <<<SQL
            (
                SELECT product_id, MAX(quantity_end) as quantity_end 
                FROM product_price 
                WHERE 1=1
                $qtyLimitQuery
                GROUP BY product_id
                UNION
                SELECT product_id, quantity_end 
                FROM product_price 
                WHERE quantity_end IS NULL
                $qtyLimitQuery
           )
        SQL;
        $onQuery = <<<SQL
            max_quantity_price.product_id = price.product_id
            AND ((max_quantity_price.quantity_end = price.quantity_end)
                OR (max_quantity_price.quantity_end IS NULL AND price.quantity_end IS NULL))
        SQL;

        $query->innerJoin('product', $fromQuery, 'max_quantity_price', $onQuery);
    }
}
