<?php 
/*
 * Created a extension for order Attribute
 * Register extension
 * Updated by AA 18.11.2019
 * Trello: https://trello.com/c/1FNqLT6A/47-export-order-only-when-payment-is-done-order-status-processing#comment-5dd244680f55406f9c884c70
*/
?>
<?php
\Magento\Framework\Component\ComponentRegistrar::register(
	\Magento\Framework\Component\ComponentRegistrar::MODULE,
	'Pixelmechanics_OrderAttribute',
	__DIR__
);

