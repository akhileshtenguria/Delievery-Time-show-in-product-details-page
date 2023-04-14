<?php
declare(strict_types=1);
namespace Keyexpress\DeliveryTime\Block;

use Magento\Framework\View\Element\Template;

class TaxInvoice extends Template
{
    protected $dataHelper;

    public function __construct(
        Template\Context $context,
        \Keyexpress\DeliveryTime\Helper\Data $dataHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Helper\Data $taxhelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;    
        // $this->registry = $registry;
        $this->priceCurrency = $priceCurrency; 
        $this->product = $product;
        $this->taxHelper = $taxhelper;
        $this->_productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->taxCalculation = $taxCalculation;
        parent::__construct($context, $data);
        $this->setInitialFields();
    }

    public function setInitialFields()
    {
        if (!$this->getLabel()) {
            $this->setLabel(__('Order Packing Charges'));
        }
    }

    public function initTotals()
    {
        $taxamt=array();
        $productIdarr=array();
        $order = $this->getParentBlock()->getOrder();
        $taxRate=0;
        $taxpercent='';
        $currTaxAmount=0;
        foreach ($order->getAllVisibleItems() as $_item) { 
            $taxamount=0;
            $productIdarr[]= $_item->getProductId(); 
            $productId= $_item->getProductId(); 
            $qty = $_item->getQtyOrdered();
            $product= $this->_productRepository->getById($productId);
            $finalPrice=0;
            $productFinalPrice=0;
            $productFinalPrice2de=0;
            $withTaxFinalprice=0;

            // tax rate calc code 
            $productTaxClassId = $product->getTaxClassId();
            $defaultCustomerTaxClassId = $this->scopeConfig->getValue('tax/classes/default_customer_tax_class');
            $store      = $this->_storeManager->getStore(); 
            $request = $this->taxCalculation->getRateRequest(null, null, null, $store);
            // print_r(get_class_methods($request)); 
            $taxRate = $this->taxCalculation->getRate($request->setProductClassId($productTaxClassId),$store);
            $taxpercent='';
            if($taxRate!=0)
            {
                $taxpercent = " (".$taxRate."%".") ";
            }
            // $taxAmount= ($product->getFinalPrice() * $taxRate) / 100;
            if ($product->getTypeId() == 'bundle') {
                $finalPrice   = $product->getPriceInfo()->getPrice('final_price')->getValue();       
            }else{
                $finalPrice = $product->getFinalPrice();
            } 
            // $taxamount= ($finalPrice * $taxRate) / 100;
            $productFinalPrice = $this->currency($finalPrice, false,false);
            $withTaxFinalprice = $this->finalPrice($product,$finalPrice);
            $productFinalPrice2de = number_format($productFinalPrice, 2, '.', '');
            $taxamount =  abs($withTaxFinalprice - $productFinalPrice2de);
            $taxamt[] = $taxamount * $qty;                    
        }
            $totalTaxAmt = array_sum($taxamt);
            $totalTaxAmt = number_format($totalTaxAmt, 2, '.', '');
            // $currTaxAmount = $this->currency($totalTaxAmt,false,false);
            if($totalTaxAmt!=0){
                $this->getParentBlock()->addTotal(
                    new \Magento\Framework\DataObject(
                        [
                            'code' => 'tax_amount',
                            'strong' => false,
                            'value' => $totalTaxAmt, // extension attribute field
                            // 'base_value' => $currTaxAmount,
                            'label' => __('Incl. Tax').$taxpercent,
                        ]
                    ),
                    $this->getAfter()
                );
            }
        return $this;
    }

    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function currency($value, $format = true, $includeContainer = true)
    {
        return $format ? $this->priceCurrency->convertAndFormat($value, $includeContainer)
        : $this->priceCurrency->convert($value);
    }

    public function finalPrice($product, $finalPrice=null) {
         return $taxprice = $this->currency( $this->taxHelper->getTaxPrice($product, $finalPrice, true),false,true);
        // return number_format($taxprice, 2, '.', '');
    }    
}