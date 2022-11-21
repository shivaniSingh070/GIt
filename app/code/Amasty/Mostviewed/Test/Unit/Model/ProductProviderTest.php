<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Model;

use Amasty\Mostviewed\Model\OptionSource\Sortby;
use Amasty\Mostviewed\Model\ProductProvider;
use Amasty\Mostviewed\Model\ResourceModel\Product\Collection;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProductProviderTest
 *
 * @see ProductProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var ProductProvider
     */
    private $model;

    /**
     * @var Product|MockObject
     */
    private $product;

    protected function setUp()
    {
        $this->product = $this->createMock(Product::class);

        $type = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->setMethods(['getAssociatedProductIds', 'getUsedProductIds', 'getOptionsIds', 'getSelectionsCollection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $type->expects($this->any())->method('getAssociatedProductIds')->willReturn([1]);
        $type->expects($this->any())->method('getUsedProductIds')->willReturn([2]);
        $type->expects($this->any())->method('getOptionsIds')->willReturn([5]);
        $type->expects($this->any())->method('getSelectionsCollection')->willReturn([]);
        $this->product->expects($this->any())->method('getTypeInstance')->willReturn($type);
        $this->product->expects($this->any())->method('getId')->willReturn(10);

        $this->model = $this->getObjectManager()->getObject(ProductProvider::class);
    }

    /**
     * @covers ProductProvider::applySorting
     * @dataProvider applySortingDataProvider
     */
    public function testApplySorting($sorting)
    {
        $collection = $this->createMock(Collection::class);
        $select = $this->createMock(Select::class);

        $matcherSelect = $sorting == 'test' ? $this->once() : $this->never();
        $collection->expects($matcherSelect)->method('getSelect')->willReturn($select);
        $matcherOrder = $sorting == 'test' ? $this->never() : $this->once();
        $collection->expects($matcherOrder)->method('setOrder')->willReturn($collection);

        $this->invokeMethod($this->model, 'applySorting', [$sorting, $collection]);
    }

    /**
     * Data provider for applySorting test
     * @return array
     */
    public function applySortingDataProvider()
    {
        return [
            [Sortby::NAME],
            [Sortby::PRICE_ASC],
            [Sortby::PRICE_DESC],
            [Sortby::NEWEST],
            ['test'],
        ];
    }

    /**
     * @covers ProductProvider::getProductIdsByType
     * @dataProvider getProductIdsByTypeDataProvider
     */
    public function testGetProductIdsByType($typeId, $result)
    {
        $this->product->expects($this->any())->method('getTypeId')->willReturnCallback(
            function () use ($typeId) {
                return $typeId;
            }
        );

        $this->assertEquals($result, $this->invokeMethod($this->model, 'getProductIdsByType', [$this->product]));
    }

    /**
     * Data provider for getProductIdsByType test
     * @return array
     */
    public function getProductIdsByTypeDataProvider()
    {
        return [
            ['grouped', [1]],
            ['configurable', [2]],
            ['bundle', []],
            ['test', [10]],
        ];
    }
}
