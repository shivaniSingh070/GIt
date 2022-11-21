<?php

namespace Amasty\ExportCore\Export\Filter\Type\Store;

use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ExportCore\Api\Filter\FilterMetaInterface;
use Magento\Cms\Ui\Component\Listing\Column\Cms\Options;

class Meta implements FilterMetaInterface
{
    /**
     * @var ConfigInterfaceFactory
     */
    private $configFactory;

    /**
     * @var Options
     */
    private $options;

    public function __construct(
        ConfigInterfaceFactory $configFactory,
        Options $options
    ) {
        $this->configFactory = $configFactory;
        $this->options = $options;
    }

    public function getJsConfig(FieldInterface $field): array
    {
        $options = $this->options->toOptionArray();
        if (empty($options)) {
            return [];
        }

        foreach ($options as &$option) {
            $option['value'] = !is_array($option['value']) ? (string)$option['value'] : $option['value'];
        }

        return [
            'component' => 'Magento_Ui/js/form/element/multiselect',
            'template' => 'ui/form/element/multiselect',
            'options' => $options
        ];
    }

    public function getConditions(FieldInterface $field): array
    {
        return [
            ['label' => __('is'), 'value' => 'in'],
            ['label' => __('is not'), 'value' => 'nin'],
            ['label' => __('is null'), 'value' => 'null'],
            ['label' => __('is not null'), 'value' => 'notnull'],
        ];
    }

    public function prepareConfig(FieldFilterInterface $filter, $value): FilterMetaInterface
    {
        $config = $this->configFactory->create();
        $config->setValue($value);
        $filter->getExtensionAttributes()->setStoreFilter($config);

        return $this;
    }

    public function getValue(FieldFilterInterface $filter)
    {
        if ($filter->getExtensionAttributes()->getStoreFilter()) {
            return $filter->getExtensionAttributes()->getStoreFilter()->getValue();
        }

        return null;
    }
}
