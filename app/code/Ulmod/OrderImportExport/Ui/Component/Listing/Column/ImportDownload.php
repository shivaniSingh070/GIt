<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class ImportDownload extends Column
{
    protected $escaper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $downloadUrl = $this->urlBuilder->getUrl(
                    'adminhtml/orderimportexport/importdownload',
                    ['id' => $item['id']]
                );

               if ($item['type'] == 2 || $item['type'] == 3){
                    $html = __('Download from the imported direcoty');  
               } else {
                    $html = '<a  href="' . $downloadUrl . '" target="_self">' . __('Download') . '</a>';  
               }

                $item[$fieldName] = $html;
            }
        }
        return $dataSource;
    }
}
