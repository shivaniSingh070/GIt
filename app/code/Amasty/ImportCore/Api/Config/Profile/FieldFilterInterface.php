<?php

namespace Amasty\ImportCore\Api\Config\Profile;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;

interface FieldFilterInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string|null
     */
    public function getField(): ?string;

    /**
     * @param string|null $field
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface
     */
    public function setField(?string $field): FieldFilterInterface;

    /**
     * @return string|null
     */
    public function getCondition(): ?string;

    /**
     * @param string|null $condition
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface
     */
    public function setCondition(?string $condition): FieldFilterInterface;

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @param string|null $filterType
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface
     */
    public function setType(?string $filterType): FieldFilterInterface;

    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface|null
     */
    public function getFilterClass(): ?ConfigClassInterface;

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface|null $filterType
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface
     */
    public function setFilterClass(?ConfigClassInterface $filterClass): FieldFilterInterface;

    /**
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldFilterExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ImportCore\Api\Config\Profile\FieldFilterExtensionInterface;

    /**
     * @param \Amasty\ImportCore\Api\Config\Profile\FieldFilterExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface
     */
    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\Config\Profile\FieldFilterExtensionInterface $extensionAttributes
    ): FieldFilterInterface;
}
