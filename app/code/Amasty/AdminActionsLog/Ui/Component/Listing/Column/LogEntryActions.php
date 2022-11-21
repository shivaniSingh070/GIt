<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Column;

use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class LogEntryActions extends Column
{
    /**
     * @var LogEntryTypes
     */
    private $logEntryTypes;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LogEntryTypes $logEntryTypes,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->logEntryTypes = $logEntryTypes;
    }

    public function prepareDataSource(array $dataSource)
    {
        $previewTypes = array_filter(array_keys($this->logEntryTypes->toArray()), function ($type) {
            return in_array($type, [LogEntryTypes::TYPE_EDIT, LogEntryTypes::TYPE_NEW, LogEntryTypes::TYPE_RESTORE]);
        });
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')]['details'] = [
                'href' => $this->context->getUrl(
                    'amaudit/actionslog/edit',
                    ['id' => $item['id']]
                ),
                'label' => __('View Details'),
                'hidden' => false,
            ];

            if (in_array($item['type'], $previewTypes)) {
                $item[$this->getData('name')]['preview'] = [
                    'callback' => [
                        'provider' => 'index = preview-modal',
                        'target' => 'getPreviewData',
                        'id' => $item['id'],
                    ],
                    'label' => __('Preview Details'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
