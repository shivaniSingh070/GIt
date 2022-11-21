<?php

namespace Amasty\Feed\Block\Adminhtml\Field\Edit\Button;

/**
 * Class Save
 *
 * @package Amasty\Feed
 */
class Save extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'on_click' => ''
        ];
    }
}
