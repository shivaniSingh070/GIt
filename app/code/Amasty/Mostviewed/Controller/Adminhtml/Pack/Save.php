<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Adminhtml\Pack;

use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;

/**
 * Class Save
 * @package Amasty\Mostviewed\Controller\Adminhtml\Pack
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Mostviewed::pack';

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $dataObject;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\Mostviewed\Model\Repository\PackRepository
     */
    private $packRepository;

    /**
     * @var \Amasty\Mostviewed\Model\PackFactory
     */
    private $packFactory;

    public function __construct(
        Action\Context $context,
        \Amasty\Mostviewed\Model\Repository\PackRepository $packRepository,
        \Amasty\Mostviewed\Model\PackFactory $packFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\DataObject $dataObject,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->dataObject = $dataObject;
        $this->logger = $logger;
        $this->packRepository = $packRepository;
        $this->packFactory = $packFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $packId = (int)$this->getRequest()->getParam('pack_id');
        if ($data) {
            /** @var Pack $model */
            $model = $this->packFactory->create();

            try {
                if ($packId) {
                    $model = $this->packRepository->getById($packId);
                }

                $this->prepareData($data);
                $model->setData($data);
                $this->packRepository->save($model);

                $this->messageManager->addSuccessMessage(__('The Bundle Pack was successfully saved.'));
                $this->dataPersistor->clear(Pack::PERSISTENT_NAME);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_mostviewed/*/edit', ['id' => $model->getPackId()]);

                    return;
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($packId) {
                    $this->_redirect('amasty_mostviewed/*/edit', ['id' => $packId]);
                } else {
                    $this->_redirect('amasty_mostviewed/*/newAction');
                }

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the pack data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->dataPersistor->set(Pack::PERSISTENT_NAME, $data);
                $this->_redirect('amasty_mostviewed/*/edit', ['id' => $packId]);

                return;
            }
        }
        $this->_redirect('amasty_mostviewed/*/');
    }

    /**
     * @param array $data
     *
     * @throws LocalizedException
     */
    private function prepareData(&$data)
    {
        if (isset($data['customer_group_ids'])) {
            $data['customer_group_ids'] = implode(',', $data['customer_group_ids']);
        }

        if (isset($data['product_ids']['child_products_container'])) {
            $childs = [];
            foreach ($data['product_ids']['child_products_container'] as $product) {
                $childs[(int)$product['position']] = $product['entity_id'];
            }
            ksort($childs);
            $data['product_ids'] = implode(',', $childs);
            unset($data['child_products_container']);
        } else {
            $data['product_ids'] = '';
        }

        if (!$data['pack_id']) {
            unset($data['pack_id']);
        }

        if (isset($data['parent_products_container'])) {
            $childs = [];
            foreach ($data['parent_products_container'] as $product) {
                $childs[] = $product['entity_id'];
            }
            $data['parent_product_ids'] = $childs;
            unset($data['parent_products_container']);
        }

        if (isset($data['discount_type'])
            && isset($data['discount_amount'])
            && $data['discount_type'] == DiscountType::PERCENTAGE
            && ((float)$data['discount_amount'] <= 0 || (float)$data['discount_amount'] > 100)
        ) {
            throw new LocalizedException(
                __('Invalid value provided for the Discount Amount field. Please enter a valid value between 0 and 100')
            );
        }
        if (isset($data['discount_amount'])) {
            $data['discount_amount'] = str_replace(',', '.', $data['discount_amount']);
            $data['discount_amount'] = (float)$data['discount_amount'];
        }
    }
}
