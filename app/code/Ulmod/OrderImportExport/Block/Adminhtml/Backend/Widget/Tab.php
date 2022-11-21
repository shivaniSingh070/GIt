<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Block\Adminhtml\Backend\Widget;

class Tab extends \Magento\Backend\Block\Widget\Tab
{
    /**
     * @var string|array|null
     */
    private $resources;

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $canShow = parent::canShowTab();
        if (is_string($this->resources) && $canShow) {
            $canShow = $this->_authorization->isAllowed(
                $this->resources
            );
        }

        if (is_array($this->resources) && $canShow) {
            foreach ($this->resources as $resource) {
                $isAllowed = $this->_authorization->isAllowed(
                    $resource
                );
                if (!$isAllowed) {
                    return false;
                }
            }
        }

        return $canShow;
    }

    /**
     * Set ACL resources list.
     *
     * @param array $resources
     * @return $this
     */
    public function setAclResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }
}
