<?php

use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;

require __DIR__ . '/giftcard_account.php';
require __DIR__ . '/quote_with_address_and_product.php';

/** @var GiftCardCartProcessor $cartProcessor */
$cartProcessor = $objectManager->create(GiftCardCartProcessor::class);
$cartProcessor->applyToCart($account, $quote);
