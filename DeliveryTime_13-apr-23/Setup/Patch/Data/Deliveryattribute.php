<?php
namespace Keyexpress\DeliveryTime\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class Deliveryattribute implements DataPatchInterface
{
   private $_moduleDataSetup;

   private $_eavSetupFactory;

   public function __construct(
       ModuleDataSetupInterface $moduleDataSetup,
       EavSetupFactory $eavSetupFactory
   ) {
       $this->_moduleDataSetup = $moduleDataSetup;
       $this->_eavSetupFactory = $eavSetupFactory;
   }

   public function apply()
   {
       /** @var EavSetup $eavSetup */
       $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->_moduleDataSetup]);

       $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'delivery_time', [
           'type' => 'int',
           'backend' => '',
           'frontend' => '',
           'label' => 'Delivery Time',
           'input' => 'select',
           'class' => 'delivery_time',
           'source' => \Keyexpress\DeliveryTime\Model\Config\Source\DeliveryOptions::class,
           'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
           'visible' => true,
           'required' => true,
           'user_defined' => false,
           'default' => 1,
           'searchable' => false,
           'filterable' => false,
           'comparable' => false,
           'visible_on_front' => false,
           'used_in_product_listing' => true,
           'unique' => false,
            'apply_to' => 'simple,grouped,configurable,downloadable,virtual,bundle'
       ]);
       // $eavSetup->removeAttribute($entityTypeId, 'legal');
   }

   public static function getDependencies()
   {
       return [];
   }

   public function getAliases()
   {
       return [];
   }

   public static function getVersion()
   {
      return '1.0.0';
   }
}