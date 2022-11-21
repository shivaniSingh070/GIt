<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Api;

interface ImporterInterface
{
    /**
     * Constants for keys of data
     */
    const KEY_PRODUCTS_ORDERED     = 'products_ordered';
    const KEY_PRODUCT_OPTIONS      = 'options';
    const KEY_PRODUCT_BUNDLE_ITEMS = 'bundle_items';
    
    /**
     * Import csv file
     *
     * @param array $data
     */
    public function import(array $data);
}
