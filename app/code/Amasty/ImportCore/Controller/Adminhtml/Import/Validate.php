<?php

namespace Amasty\ImportCore\Controller\Adminhtml\Import;

use Amasty\ImportCore\Import\Config\ProfileConfigFactory;
use Amasty\ImportCore\Import\FormProvider;
use Amasty\ImportCore\Model\ConfigProvider;
use Amasty\ImportCore\Processing\JobManager;
use Amasty\ImportCore\Ui\DataProvider\Import\CompositeFormType;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Validate extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_ImportCore::import';

    /**
     * @var ProfileConfigFactory
     */
    private $profileConfigFactory;

    /**
     * @var JobManager
     */
    private $jobManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var FormProvider
     */
    private $formProvider;

    public function __construct(
        Action\Context $context,
        ProfileConfigFactory $profileConfigFactory,
        ConfigProvider $configProvider,
        FormProvider $formProvider,
        JobManager $jobManager
    ) {
        parent::__construct($context);
        $this->profileConfigFactory = $profileConfigFactory;
        $this->jobManager = $jobManager;
        $this->configProvider = $configProvider;
        $this->formProvider = $formProvider;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParam('encodedData');
        if (!empty($data)) {
            $params = $this->getRequest()->getParams();
            unset($params['encodedData']);
            $postData = \json_decode($data, true);
            $this->getRequest()->setParams(array_merge_recursive($params, $postData));
        }
        /** @var \Amasty\ImportCore\Import\Config\ProfileConfig $profileConfig */
        $profileConfig = $this->profileConfigFactory->create();
        $profileConfig->setStrategy('validate_and_save');
        $profileConfig->setEntityCode($this->getRequest()->getParam('entity_code'));
        $profileConfig->setIsUseMultiProcess($this->configProvider->useMultiProcess());
        $profileConfig->setMaxJobs($this->configProvider->getMaxProcessCount());
        $this->formProvider->get(CompositeFormType::TYPE)->prepareConfig($profileConfig, $this->getRequest());
        $profileConfig->initialize();

        try {
            $result = ['type' => 'success'];
            $this->jobManager->requestJob($profileConfig, $this->getRequest()->getParam('processIdentity'));
        } catch (\Exception $e) {
            $result = ['type' => 'error', 'message' => $e->getMessage()];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
