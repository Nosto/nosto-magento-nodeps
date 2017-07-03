<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Nosto
 * @package   Nosto_Tagging
 * @author    Nosto Solutions Ltd <magento@nosto.com>
 * @copyright Copyright (c) 2013-2017 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about a product.
 * This is used during the order confirmation API request and the product
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Variation extends Nosto_Object_Product_Variation
{
    /**
     * Loads the Variation info from a Magento product model.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Customer_Model_Group $group the customer group
     * @param string $productAvailability
     * @param string $currencyCode
     * @param Mage_Core_Model_Store|null $store the store to get the product data for.
     * @throws Nosto_NostoException
     */
    public function loadData(
        Mage_Catalog_Model_Product $product,
        Mage_Customer_Model_Group $group,
        $productAvailability,
        $currencyCode,
        Mage_Core_Model_Store $store
    )
    {
        if ($store === null) {
            $store = Mage::app()->getStore();
        }

        //It has to be a new instance of the Product. Because magento product takes customer group Id once only
        /** @var Mage_Catalog_Model_Product $tmpProduct */
        $tmpProduct = Mage::getModel('catalog/product')->load($product->getId());
        $tmpProduct->setCustomerGroupId($group->getCustomerGroupId());

        $this->setId($group->getCode());
        $this->setAvailable($productAvailability);
        $this->setPriceCurrencyCode($currencyCode);

        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');
        $this->setListPrice($priceHelper->getProductTaggingPrice($tmpProduct, $store, false));
        $this->setPrice($priceHelper->getProductTaggingPrice($tmpProduct, $store, true));
    }

    /**
     * Build price variations. It must be called after the currency has been set.
     * Because this method set varation currency to the product tagging currency.
     * And have to be called before building the product price. Becuase it changes the product's customer group id.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $productAvailability
     * @param string $currencyCode
     * @param Mage_Core_Model_Store $store
     * @return Nosto_Object_Product_VariationCollection
     */
    public static function buildVariations(
        Mage_Catalog_Model_Product $product,
        $productAvailability,
        $currencyCode,
        Mage_Core_Model_Store $store)
    {
        /** @var $customerHelper Mage_Customer_Helper_Data */
        $customerHelper = Mage::helper('customer');
        $defaultGroupId = $customerHelper->getDefaultCustomerGroupId($store);

        $defaultGroupCode = null;
        /** @var Mage_Customer_Model_Group $group */
        $defaultGroup = Mage::getModel('customer/group')->load($defaultGroupId);
        if ($defaultGroup != null) {
            $defaultGroupCode = $defaultGroup->getCode();
        }

        $variations = new Nosto_Object_Product_VariationCollection();

        $groups = Mage::getModel('customer/group')->getCollection();
        /** @var Mage_Customer_Model_Group $group */
        foreach ($groups as $group) {
            //skip the default customer group
            if ($group->getCode() == $defaultGroupCode) {
                continue;
            }

            /** @var Nosto_Tagging_Model_Meta_Variation $variation */
            $variation = Mage::getModel('nosto_tagging/meta_variation');
            $variation->loadData($product, $group, $productAvailability, $currencyCode, $store);
            $variations->append($variation);
        }

        return $variations;
    }

    /**
     * Get default variation
     *
     * @param Mage_Core_Model_Store $store
     * @return null|string
     */
    public static function buildDefaultVariationId(Mage_Core_Model_Store $store)
    {
        /** @var $customerHelper Mage_Customer_Helper_Data */
        $customerHelper = Mage::helper('customer');
        $defaultGroupId = $customerHelper->getDefaultCustomerGroupId($store);

        /** @var Mage_Customer_Model_Group $group */
        $defaultGroup = Mage::getModel('customer/group')->load($defaultGroupId);
        if ($defaultGroup instanceof Mage_Customer_Model_Group) {
            return $defaultGroup->getCode();
        }

        return null;
    }
}
