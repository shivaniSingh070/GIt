<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOrderNumber
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOrderNumber\Plugin\Model;

class Sequence
{
    /**
     * @var \Bss\CustomOrderNumber\Helper\Data
     */
    private $helper;

    /**
     * Sequence constructor.
     * @param \Bss\CustomOrderNumber\Helper\Data $helper
     */
    public function __construct(
        \Bss\CustomOrderNumber\Helper\Data $helper
    ) {

        $this->helper = $helper;
    }

    /**
     * Retrieve next value
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetNextValue($subject, callable $proceed)
    {
        return $proceed();
    }
}
