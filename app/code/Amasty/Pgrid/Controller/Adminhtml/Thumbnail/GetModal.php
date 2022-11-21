<?php
declare(strict_types=1);

namespace Amasty\Pgrid\Controller\Adminhtml\Thumbnail;

use Magento\Backend\App\Action;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo;
use Psr\Log\LoggerInterface;

class GetModal extends Action
{
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        LayoutFactory $layoutFactory,
        ProductRepository $productRepository,
        Registry $registry,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->layoutFactory = $layoutFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        if ($productId = (int)$this->getRequest()->getParam('entity_id')) {
            try {
                $product = $this->productRepository->getById($productId, false, 0);
                $this->registry->register('current_product', $product, true);//for Gallery block
                $this->registry->register('product', $product, true);//for NewVideo block

                $layout = $this->layoutFactory->create();
                $galleryContent = $layout->createBlock(Content::class, 'content');
                $videoBlock = $layout->createBlock(NewVideo::class, 'new-video')
                    ->setTemplate('Magento_ProductVideo::product/edit/slideout/form.phtml');
                $gallery = $layout->createBlock(Gallery::class);

                $galleryContent->setChild('new-video', $videoBlock);
                $gallery->setChild('content', $galleryContent);

                return $result->setContents($gallery->toHtml());
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        return $result->setContents('');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }
}
