<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Block\Widget;

use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Block\Widget\PackList;
use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Model\ResourceModel\Product\Collection as ProductCollection;
use Amasty\Mostviewed\Model\ResourceModel\Product\CollectionFactory;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\BlockInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class PackListTest
 *
 * @see PackList
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackListTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var PackList
     */
    private $block;

    protected function setUp()
    {
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $collectionFactory = $this->createMock(CollectionFactory::class);
        $collection = $this->createMock(ProductCollection::class);
        $store = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $select = $this->createMock(Select::class);
        $packRepository = $this->getMockBuilder(PackRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $block = $this->createPartialMock(BlockInterface::class, ['setBundles', 'setProduct', 'toHtml']);
        $searchCriteriaBuilder = $this->createPartialMock(SearchCriteriaBuilder::class, ['create', 'getItems']);
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $sessionFactory = $this->createPartialMock(
            \Magento\Customer\Model\SessionFactory::class,
            ['create', 'getCustomerGroupId']
        );
        $pack1 = $this->getObjectManager()->getObject(Pack::class)->setCustomerGroupIds(1)->setPackId(1);
        $pack2 = $this->getObjectManager()->getObject(Pack::class)->setCustomerGroupIds(2)->setPackId(2);

        $layout->expects($this->any())->method('getBlock')->willReturn(null);
        $layout->expects($this->any())->method('createBlock')->willReturn($block);
        $block->expects($this->any())->method('setBundles')->willReturn($block);
        $block->expects($this->any())->method('setProduct')->willReturn($block);
        $block->expects($this->any())->method('toHtml')->willReturn('test');
        $searchCriteriaBuilder->expects($this->any())->method('create')->willReturn($searchCriteria);
        $packRepository->expects($this->any())->method('getList')->willReturn($searchCriteriaBuilder);
        $searchCriteriaBuilder->expects($this->any())->method('getItems')->willReturn([$pack1, $pack2]);
        $sessionFactory->expects($this->any())->method('create')->willReturn($sessionFactory);
        $sessionFactory->expects($this->any())->method('getCustomerGroupId')->willReturn(1);
        $collectionFactory->expects($this->any())->method('create')->willReturn($collection);
        $collection->expects($this->any())->method('applyBundleFilter')->willReturn($collection);
        $collection->expects($this->any())->method('addAttributeToSelect')->willReturn($collection);
        $collection->expects($this->any())->method('addStoreFilter')->willReturn($collection);
        $collection->expects($this->any())->method('addMinimalPrice')->willReturn($collection);
        $collection->expects($this->any())->method('addFinalPrice')->willReturn($collection);
        $collection->expects($this->any())->method('addTaxPercents')->willReturn($collection);
        $collection->expects($this->any())->method('addAttributeToSelect')->willReturn($collection);
        $collection->expects($this->any())->method('addUrlRewrite')->willReturn($collection);
        $collection->expects($this->any())->method('getSelect')->willReturn($select);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->block = $this->getObjectManager()->getObject(
            PackList::class,
            [
                'packRepository' => $packRepository,
                'searchCriteriaBuilder' => $searchCriteriaBuilder,
                'sessionFactory' => $sessionFactory,
                'collectionFactory' => $collectionFactory,
                '_storeManager' => $storeManager,
            ]
        );
        $this->setProperty($this->block, '_layout', $layout);
    }

    /**
     * @covers PackList::renderBundle
     */
    public function testRenderBundle()
    {
        $product = $this->createPartialMock(Product::class, ['getBundlePackId']);
        $this->assertEquals('', $this->block->renderBundle($product));

        $product->expects($this->any())->method('getBundlePackId')->willReturn(1);
        $this->setProperty($this->block, 'bundles', [1 => 10]);
        $this->assertEquals('test', $this->block->renderBundle($product));
    }

    /**
     * @covers PackList::getIdentities
     */
    public function testGetIdentities()
    {
        $pack = $this->createMock(Pack::class);
        $pack->expects($this->any())->method('getPackId')->willReturn(1);
        $pack->expects($this->any())->method('getProductIds')->willReturn('1,2');

        $this->setProperty($this->block, 'bundles', []);
        $this->assertEquals([], $this->block->getIdentities());

        $this->setProperty($this->block, 'bundles', [$pack]);
        $this->assertEquals(
            [Pack::CACHE_TAG . '_1', Product::CACHE_TAG .'_1', Product::CACHE_TAG .'_2'],
            $this->block->getIdentities()
        );
    }

    /**
     * @covers PackList::getBundles
     */
    public function testGetBundles()
    {
        $this->assertArrayHasKey(1, $this->block->getBundles());
        $this->setProperty($this->block, 'bundles', []);
        $this->assertEquals([], $this->block->getBundles());
    }
}
