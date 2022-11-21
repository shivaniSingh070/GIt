<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Pack;

use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Model\Pack\DataProvider;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Framework\App\Request\DataPersistorInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DataProviderTest
 *
 * @see DataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var DataProvider
     */
    private $model;

    protected function setUp()
    {
        $collection = $this->createMock(\Amasty\Mostviewed\Model\ResourceModel\Pack\Collection::class);
        $coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $pack = $this->createMock(Pack::class);
        $dataPersistor = $this->createMock(DataPersistorInterface::class);
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
            ->setMethods(['create', 'addIdFilter', 'addAttributeToSelect', 'getItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $coreRegistry->expects($this->any())->method('registry')->willReturnOnConsecutiveCalls(null, $pack, $pack);
        $pack->expects($this->any())->method('getPackId')->willReturn(1);
        $dataPersistor->expects($this->any())->method('get')->willReturnOnConsecutiveCalls(null, $pack);
        $productCollection->expects($this->any())->method('create')->willReturn($productCollection);
        $productCollection->expects($this->any())->method('addIdFilter')->willReturn($productCollection);
        $productCollection->expects($this->any())->method('addAttributeToSelect')->willReturn($productCollection);
        $productCollection->expects($this->any())->method('getItems')->willReturn([]);

        $this->model = $this->getObjectManager()->getObject(
            DataProvider::class,
            [
                'collection' => $collection,
                'coreRegistry' => $coreRegistry,
                'productCollectionFactory' => $productCollection,
            ]
        );
    }

    /**
     * @covers DataProvider::getData
     */
    public function testGetData()
    {
        $this->assertNull($this->model->getData());
        $this->assertEquals([1 => null], $this->model->getData());
        $this->assertEquals([1 => null], $this->model->getData());
    }
    /**
     * @covers DataProvider::convertProductsData
     */
    public function testConvertProductsData()
    {
        $this->assertEquals([], $this->invokeMethod($this->model, 'convertProductsData', [[]]));
        $this->assertEquals(
            ['product_ids' =>['child_products_container' => []]],
            $this->invokeMethod($this->model, 'convertProductsData', [['product_ids' => 'test']])
        );
        $this->assertEquals(
            ['parent_ids' =>['parent_products_container' => []]],
            $this->invokeMethod($this->model, 'convertProductsData', [['parent_ids' => 'test']])
        );
    }
}
