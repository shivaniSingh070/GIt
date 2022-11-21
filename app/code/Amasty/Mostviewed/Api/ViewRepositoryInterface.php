<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Api;

/**
 * @api
 */
interface ViewRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Mostviewed\Api\Data\ViewInterface $view
     * @return \Amasty\Mostviewed\Api\Data\ViewInterface
     */
    public function save(\Amasty\Mostviewed\Api\Data\ViewInterface $view);

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\Mostviewed\Api\Data\ViewInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\Mostviewed\Api\Data\ViewInterface $view
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Mostviewed\Api\Data\ViewInterface $view);

    /**
     * Delete by id
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);
}
