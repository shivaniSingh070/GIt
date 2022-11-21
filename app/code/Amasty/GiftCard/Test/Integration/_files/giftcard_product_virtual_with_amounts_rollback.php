<?php

require 'giftcard_image_rollback.php';
require 'giftcard_codepool_with_code_rollback.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\ProductRepository $productRepository */
$productRepository = $objectManager->get(\Magento\Catalog\Model\ProductRepository::class);

try {
    $productRepository->deleteById('am_giftcard_with_amounts');
} catch (\Exception $e) {
    null;
}
