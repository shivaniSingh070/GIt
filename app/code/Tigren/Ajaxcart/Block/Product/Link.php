<?php

namespace Tigren\Ajaxcart\Block\Product;

use Magento\Framework\Json\EncoderInterface;

class Link extends \Magento\Downloadable\Block\Catalog\Product\Links
{
    public function __construct(\Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Pricing\Helper\Data $pricingHelper, EncoderInterface $encoder, array $data = [])
    {
        parent::__construct($context, $pricingHelper, $encoder, $data);
    }

}