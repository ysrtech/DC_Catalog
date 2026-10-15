<?php
/**
 * Dot Collective - DC_Catalog
 *
 * Store-view level visibility: a store row can now override the default page's
 * Status. use_default_is_active = 1 (the default) means "inherit from the default
 * page", so every existing row keeps behaving exactly as before.
 *
 * @category   DC
 * @package    DC_Catalog
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('catalog_attribute_page');

if (!$installer->getConnection()->tableColumnExists($table, 'use_default_is_active')) {
    $installer->run("
ALTER TABLE `{$table}`
    ADD COLUMN `use_default_is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `is_active`;
");
}

// Store rows created for translations copied the default page's Status at that moment.
// They now inherit it, so bring the stored copy back in line with the default page.
$installer->run("
UPDATE `{$table}` AS s
    INNER JOIN `{$table}` AS d
        ON d.attribute_code = s.attribute_code
       AND d.attribute_option_id = s.attribute_option_id
       AND d.attribute_value_store_id = 0
SET s.is_active = d.is_active
WHERE s.attribute_value_store_id > 0
  AND s.use_default_is_active = 1;
");

$installer->endSetup();
