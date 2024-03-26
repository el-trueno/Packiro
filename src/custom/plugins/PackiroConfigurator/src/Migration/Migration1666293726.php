<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1666293726 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1666293726;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_accessory_group` (
    `id` BINARY(16) NOT NULL,
    `media_id` BINARY(16),
    `multiple_selection` TINYINT(1),
    `scaling_stack` TINYINT(1),
    `active_pro_lib` TINYINT(1),
    `active` TINYINT(1),
    `disabled` TINYINT(1),
    `type` VARCHAR(255),
    `priority` INT(11),
    `technical_name` VARCHAR(255),
    `internal_note` TEXT,
    
    `custom_fields` JSON,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`id`),

    CONSTRAINT `fk.pc_accessory_group.media_id` 
        FOREIGN KEY (`media_id`)
        REFERENCES `media` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_accessory_group_translation` (
    `pc_accessory_group_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` VARCHAR(255),
    `short_description` TEXT,
    `help_text` TEXT,
    
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`pc_accessory_group_id`, `language_id`),

    CONSTRAINT `fk.pc_accessory_group_translation.language_id` 
        FOREIGN KEY (`language_id`)
        REFERENCES `language` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_accessory_group_translation.pc_accessory_group_id` 
        FOREIGN KEY (`pc_accessory_group_id`)
        REFERENCES `pc_accessory_group` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_accessory_group_tag` (
    `pc_accessory_group_id` BINARY(16) NOT NULL,
    `tag_id` BINARY(16) NOT NULL,
    
    PRIMARY KEY (`pc_accessory_group_id`, `tag_id`),
    
    CONSTRAINT `fk.pc_accessory_group_tag.pc_accessory_group_id` 
        FOREIGN KEY (`pc_accessory_group_id`)
        REFERENCES `pc_accessory_group` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_accessory_group_tag.tag_id` 
        FOREIGN KEY (`tag_id`)
        REFERENCES `tag` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_accessory_option` (
    `id` BINARY(16) NOT NULL,
    `pc_accessory_group_id` BINARY(16) NOT NULL,
    `media_id` BINARY(16),
    `tax_id` BINARY(16),
    `pc_accessory_provided` JSON,
    `price` JSON,
    `min_quantity` INT(11),
    `max_quantity` INT(11),
    `delivery_days` INT(11),
    `scaling_stack` TINYINT(1),
    `pre_selected` TINYINT(1),
    `active` TINYINT(1),
    `disabled` TINYINT(1),
    `type` VARCHAR(255),
    `priority` INT(11),
    `technical_name` VARCHAR(255),
    `internal_note` TEXT,
    
    `custom_fields` JSON,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`id`),

    CONSTRAINT `fk.pc_accessory_option.media_id` 
        FOREIGN KEY (`media_id`)
        REFERENCES `media` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_accessory_option.tax_id` 
        FOREIGN KEY (`tax_id`)
        REFERENCES `tax` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_accessory_option.pc_accessory_group_id` 
        FOREIGN KEY (`pc_accessory_group_id`)
        REFERENCES `pc_accessory_group` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_accessory_option_translation` (
    `pc_accessory_option_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` VARCHAR(255),
    `short_description` TEXT,
    `help_text` TEXT,
    
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`pc_accessory_option_id`, `language_id`),

    CONSTRAINT `fk.pc_accessory_option_translation.language_id` 
        FOREIGN KEY (`language_id`)
        REFERENCES `language` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_accessory_option_translation.pc_accessory_option_id` 
        FOREIGN KEY (`pc_accessory_option_id`)
        REFERENCES `pc_accessory_option` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_accessory_option_tag` (
    `pc_accessory_option_id` BINARY(16) NOT NULL,
    `tag_id` BINARY(16) NOT NULL,
    
    PRIMARY KEY (`pc_accessory_option_id`, `tag_id`),
    
    CONSTRAINT `fk.pc_accessory_option_tag.pc_accessory_option_id` 
        FOREIGN KEY (`pc_accessory_option_id`)
        REFERENCES `pc_accessory_option` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_accessory_option_tag.tag_id` 
        FOREIGN KEY (`tag_id`)
        REFERENCES `tag` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_product` (
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `type` VARCHAR(255),
    `technical_name` VARCHAR(255),
    `pc_accessory_not_available` JSON,
    `pc_accessory_not_combinable` JSON,
    
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`product_id`, `product_version_id`),

    CONSTRAINT `fk.pc_product.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_accessory_option_product` (
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `pc_accessory_option_id` BINARY(16) NOT NULL,

    PRIMARY KEY (`product_id`, `product_version_id`, `pc_accessory_option_id`),

    CONSTRAINT `fk.pc_accessory_option_product.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_accessory_option_product.pc_accessory_option_id` 
        FOREIGN KEY (`pc_accessory_option_id`)
        REFERENCES `pc_accessory_option` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_pro_lib_group` (
    `id` BINARY(16) NOT NULL,
    `customer_id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `customer_reference` VARCHAR(255),
    `version_count` INT(11),
    
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.pc_pro_lib_group.customer_id` 
        FOREIGN KEY (`customer_id`)
        REFERENCES `customer` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_pro_lib_item` (
    `id` BINARY(16) NOT NULL,
    `pc_pro_lib_group_id` BINARY(16),
    `product_id` BINARY(16),
    `product_version_id` BINARY(16),
    `artwork_id` VARCHAR(255),
    `artwork_state` VARCHAR(255),
    `artwork_access` JSON,
    `packshot_created` TINYINT(1),
    `packshot_purchased` TINYINT(1),
    `version` INT(11),
    `name` VARCHAR(255),
    `note` TEXT,
    `payload` JSON,
    
    `last_order_at` DATETIME(3),
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    
    `locked` TINYINT(1),

    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.pc_pro_lib_item.pc_pro_lib_group_id` 
        FOREIGN KEY (`pc_pro_lib_group_id`)
        REFERENCES `pc_pro_lib_group` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_pro_lib_item.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_pro_lib_item_accessory_option` (
    `pc_accessory_option_id` BINARY(16) NOT NULL,
    `pc_pro_lib_item_id` BINARY(16) NOT NULL,
    
    PRIMARY KEY (`pc_accessory_option_id`, `pc_pro_lib_item_id`),
    
    CONSTRAINT `fk.pc_pro_lib_item_accessory_option.pc_accessory_option_id` 
        FOREIGN KEY (`pc_accessory_option_id`)
        REFERENCES `pc_accessory_option` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_pro_lib_item_accessory_option.pc_pro_lib_item_id` 
        FOREIGN KEY (`pc_pro_lib_item_id`)
        REFERENCES `pc_pro_lib_item` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_pro_lib_item_order` (
    `id` BINARY(16) NOT NULL,
    `pc_pro_lib_item_id` BINARY(16),
    `order_id` BINARY(16) NOT NULL,
    `order_version_id` BINARY(16) NOT NULL,
    `order_line_item_id` BINARY(16) NOT NULL,
    `order_line_item_version_id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `quantity` INT(11),
    
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`id`),

    CONSTRAINT `fk.pc_pro_lib_item_order.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_pro_lib_item_order.pc_pro_lib_item_id` 
        FOREIGN KEY (`pc_pro_lib_item_id`)
        REFERENCES `pc_pro_lib_item` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_pro_lib_item_order.order_id` 
        FOREIGN KEY (`order_id`, `order_version_id`)
        REFERENCES `order` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_pro_lib_item_order.order_line_item_id` 
        FOREIGN KEY (`order_line_item_id`, `order_line_item_version_id`)
        REFERENCES `order_line_item` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pc_country_rule_tax_free` (
    `country_id` BINARY(16) NOT NULL,
    `rule_id` BINARY(16) NOT NULL,
    
    `created_at` DATETIME(3) NOT NULL,
         
    PRIMARY KEY (`country_id`, `rule_id`),
    
    CONSTRAINT `fk.pc_country_rule_tax_free.country_id` 
        FOREIGN KEY (`country_id`) 
        REFERENCES `country` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pc_country_rule_tax_free.rule_id` 
        FOREIGN KEY (`rule_id`) 
        REFERENCES `rule` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
