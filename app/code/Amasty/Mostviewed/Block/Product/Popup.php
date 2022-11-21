<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Block\Product;

use Magento\Framework\View\Element\Template;

/**
 * Class Popup
 * @package Amasty\Mostviewed\Block\Product
 */
class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    private $products = [];

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
    }

    public function _construct()
    {
        $this->setTemplate('Amasty_Mostviewed::bundle/popup.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param array $products
     *
     * @return $this
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->jsonEncoder->encode(
            [
                'url' => $this->getUrl('ammostviewed/cart/add')
            ]
        );
    }
}
