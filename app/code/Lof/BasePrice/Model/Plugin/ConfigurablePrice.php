<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * @category   Lof
 * @package    Lof_BasePrice
 * @subpackage Model
 * @copyright  Copyright (c) 2020 Landofcoder (https://landofcoder.com)
 * @link       https://landofcoder.com
 * @author     Landofcoder <landofcoder@gmail.com>
 */
namespace Lof\BasePrice\Model\Plugin;

/**
 * Class ConfigurablePrice
 * @package Lof\BasePrice\Model\Plugin
 */
class ConfigurablePrice
{
    /**
     * @var \Lof\BasePrice\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * Constructor
     *
     * @param \Lof\BasePrice\Helper\Data $helper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        \Lof\BasePrice\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ){
        $this->_helper = $helper;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsonDecoder = $jsonDecoder;
    }

    /**
     * Plugin for configurable price rendering. Iterates over configurable's simples and adds the base price
     * to price configuration.
     *
     * @param \Magento\Framework\Pricing\Render $subject
     * @param $json string
     * @return string
     */
    public function afterGetJsonConfig(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject, $json)
    {
        $config = $this->_jsonDecoder->decode($json);

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($subject->getAllowProducts() as $product) {
            $basePriceText = $this->_helper->getBasePriceText($product);

            if (empty($basePriceText)) {
                // if simple has no configured base price, us at least the base price of configurable
                $basePriceText = $this->_helper->getBasePriceText($subject->getProduct());
            }

            $config['optionPrices'][$product->getId()]['lof_baseprice_text'] = $basePriceText;
        }

        return $this->_jsonEncoder->encode($config);
    }
}
