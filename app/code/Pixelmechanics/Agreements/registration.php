<?php
/*
 * Created a extension for overriding Checkout Agreement
 * Register extension
 * Updated by AA 02.07.2019
*/
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Pixelmechanics_Agreements',
    __DIR__
);