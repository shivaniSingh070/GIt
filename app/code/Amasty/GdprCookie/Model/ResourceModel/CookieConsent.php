<?php

namespace Amasty\GdprCookie\Model\ResourceModel;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Model\CookieConsent\CookieGroupProcessor;
use Amasty\GdprCookie\Setup\Operation\CreateCookieConsentStatusTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieConsentTable;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class CookieConsent extends AbstractDb
{
    /**
     * @var CookieGroupProcessor
     */
    private $cookieGroupProcessor;

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    public function __construct(
        Context $context,
        CookieGroupProcessor $cookieGroupProcessor,
        CookieManagementInterface $cookieManagement,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->cookieGroupProcessor = $cookieGroupProcessor;
        $this->cookieManagement = $cookieManagement;
    }
    public function _construct()
    {
        $this->_init(CreateCookieConsentTable::TABLE_NAME, 'id');
    }

    protected function _afterSave(AbstractModel $object)
    {
        parent::_afterSave($object);

        $consentStatusTable = $this->getTable(CreateCookieConsentStatusTable::TABLE_NAME);
        $this->getConnection()->delete(
            $consentStatusTable,
            ['cookie_consents_id = ?' => $object->getId()]
        );

        $dataToInsert = [];
        $groups = $this->cookieManagement->getAvailableGroups((int)$object->getWebsite());
        foreach ($groups as $group) {
            $dataToInsert[] = [
                'cookie_consents_id' => $object->getId(),
                'group_id' => $group->getId(),
                'status' => $this->cookieGroupProcessor->getConsentStatus(
                    $object->getAllowedGroupIds(),
                    $group
                )
            ];
        }

        $this->getConnection()->insertMultiple($consentStatusTable, $dataToInsert);

        return $this;
    }
}
