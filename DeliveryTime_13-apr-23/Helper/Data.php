<?php

declare(strict_types=1);

namespace Keyexpress\DeliveryTime\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Directory\Model\RegionFactory;

class Data extends AbstractHelper
{

    public function __construct(
        Context $context,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Helper\Data $taxhelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        RegionFactory $regionFactory

    ) {
        $this->taxCalculation = $taxCalculation;
        $this->_storeManager = $storeManager;    
        $this->priceCurrency = $priceCurrency; 
        $this->registry = $registry;
        $this->product = $product;
        $this->taxHelper = $taxhelper;
         $this->scopeConfig = $scopeConfig;
          $this->regionFactory = $regionFactory;
        parent::__construct($context);
    }

   /* public function getTaxAmount()
    {       
        $currentproduct = $this->getCurrentProduct();
        $productId = $currentproduct->getId();
        $product = $this->product->load($productId);
        if ($product->getTypeId() == 'bundle') {
            $finalPrice   = $product->getPriceInfo()->getPrice('final_price')->getValue();      
        }
        $finalPrice= $product->getFinalPrice();
        // $this->taxHelper->getTaxPrice($product, $product->getFinalPrice(), true);
         // echo "wtam-".$this->currency($product->getFinalPrice(), true,false);
        // echo "tam-". $this->currency( $this->taxHelper->getTaxPrice($product, $finalPrice, true),true,false);
        $productFinalPrice = $this->currency($product->getFinalPrice(), false,true);
        $withTaxFinalprice = $this->finalPrice($product,$finalPrice);
        // $productTaxClassId = $product->getTaxClassId();
        // $productFinalPrice = number_format($productFinalPrice, 2, '.', '');
         $taxAmount= $withTaxFinalprice - $productFinalPrice;
        return  $taxAmount;
        // return  $this->getTaxPercentage($product); 
    }*/


    public function currency($value, $format = true, $includeContainer = true)
    {
        return $format ? $this->priceCurrency->convertAndFormat($value, $includeContainer)
        : $this->priceCurrency->convert($value);
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function finalPrice($product, $finalPrice=null) {
     return   $taxprice = $this->currency( $this->taxHelper->getTaxPrice($product, $finalPrice, true),false,true);
        // return number_format($taxprice, 2, '.', '');
    }

    public function getRegionId($stateCode, $countryId)
    {
        return $this->regionFactory->loadByCode($stateCode, $countryId)->getRegionId();
    }

    public function getTaxPercentage(){
        $currentproduct = $this->getCurrentProduct();
        $productId = $currentproduct->getId();
        $product = $this->product->load($productId);
        $storeId = $this->_storeManager->getStore()->getId();
       /* Get Current Store Code */
        $storeCode = $this->_storeManager->getStore()->getCode();
        $storeName = $this->_storeManager->getStore()->getName();

        $productTaxClassId = $product->getTaxClassId();
        if ($product->getTypeId() == 'bundle') {
                   
            $finalPrice   = $product->getPriceInfo()->getPrice('final_price')->getValue();       
        }else{
            $finalPrice = $product->getFinalPrice();
        } 
        // $productFinalPrice = $this->currency($finalPrice, false,false);
        $withTaxFinalprice = $this->taxHelper->getTaxPrice($product, $finalPrice, true);
        // $productFinalPrice2de = number_format($productFinalPrice, 2, '.', '');
        $taxAmount =  abs($withTaxFinalprice - $finalPrice);
        $taxAmount = number_format($taxAmount, 2, '.', '');

        $defaultCustomerTaxClassId = $this->scopeConfig->getValue('tax/classes/default_customer_tax_class');
        $store      = $this->_storeManager->getStore(); 
        $request = $this->taxCalculation->getRateRequest(null, null, null, $store);
        // print_r(get_class_methods($request)); 
        $taxRate = $this->taxCalculation->getRate($request->setProductClassId($productTaxClassId),$store);
        // $taxAmount= ($finalPrice * $taxRate) / 100;
        if($taxAmount!=0){
            if($taxRate!=''){
                return "(".$taxRate.'%'.")  ".$this->currency($taxAmount,true,true);
            }
        }
        return false;
        

    }



}
