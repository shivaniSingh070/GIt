<?php
/**
 * Public alias for the application entry point
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Bootstrap;

/**
 * The PixelMechanics Default-File for all PHP-Systems to define the Environment and include helper-files
 * @link https://trello.com/c/pk8egBYL/9-api-09import-product-qty-from-navision-to-magento#comment-5d440e6cc25aea41bd62adcb
 * @author: AA, 05.08.2019
 **/
    if (file_exists(__DIR__.'/../defines.php')) {
        require __DIR__.'/../defines.php';
    }
    
try {
    require __DIR__ . '/../app/bootstrap.php';

} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}

// $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
// * @var \Magento\Framework\App\Http $app
// $app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
// $bootstrap->run($app);

// $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
// $objectManager = $bootstrap->getObjectManager();
// $pr = $objectManager->get('Pixelmechanics\LowestPrice\Cron\Lowest');
// // echo"<pre>";
// print_r($pr->execute());

$params = $_SERVER;
switch($_SERVER['HTTP_HOST']) {

        case 'engelsrufer.de':
        case 'www.engelsrufer.de':
             $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'de';
                         $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'store';
        break;


        case 'engelsrufer.world':
        case 'www.engelsrufer.world':
             $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'en';
                         $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'store';
        break;

        case 'engelsrufer.it':
        case 'www.engelsrufer.it':
             $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'it';
                         $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'store';
        break;

        case 'engelsrufer.fr':
        case 'www.engelsrufer.fr':
             $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'fr';
                         $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'store';
        break;

        case 'engelsrufer.es':
        case 'www.engelsrufer.es':
             $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'es';
                         $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'store';
        break;
    }

/** @var \Magento\Framework\App\Http $app */
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
$app = $bootstrap->createApplication('Magento\Framework\App\Http');
$bootstrap->run($app);

