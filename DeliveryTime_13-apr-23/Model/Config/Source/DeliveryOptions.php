<?php
namespace Keyexpress\DeliveryTime\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class DeliveryOptions extends AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options=[
                                ['label' => __('Select'), 'value' => 0],
                                ['label' => __('Instant Download'), 'value' => 1],
                                ['label' => __('Delivery in 1-3 days'), 'value' => 2],
                                ['label' => __('Delivery in 3-5 days'), 'value' => 3]
                            ];
        }
        return $this->_options;
    }
}