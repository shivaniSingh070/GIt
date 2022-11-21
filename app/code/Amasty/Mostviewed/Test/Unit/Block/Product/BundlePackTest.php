<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Block\Product;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Block\Product\BundlePack;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class BundlePackTest
 *
 * @see BundlePack
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundlePackTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var BundlePack|MockObject
     */
    private $block;

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface|MockObject
     */
    private $packRepository;

    protected function setUp()
    {
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->packRepository = $this->createMock(\Amasty\Mostviewed\Api\PackRepositoryInterface::class);
        $store = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $sessionFactory = $this->createPartialMock(
            \Magento\Customer\Model\SessionFactory::class,
            ['create', 'getCustomerGroupId']
        );

        $sessionFactory->expects($this->any())->method('create')->willReturn($sessionFactory);
        $sessionFactory->expects($this->any())->method('getCustomerGroupId')->willReturn(1);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $store->expects($this->any())->method('getId')->willReturn(1);
        $priceCurrency->expects($this->any())->method('format')->willReturnArgument(0);
        $priceCurrency->expects($this->any())->method('round')->willReturnArgument(0);

        $this->block = $this->getObjectManager()->getObject(
            BundlePack::class,
            [
                '_storeManager' => $storeManager,
                'packRepository' => $this->packRepository,
                'sessionFactory' => $sessionFactory,
                'priceCurrency' => $priceCurrency,
            ]
        );
    }

    /**
     * @covers BundlePack::toHtml
     */
    public function testToHtml()
    {
        $this->block = $this->createPartialMock(
            BundlePack::class,
            ['isBundlePacksExists', 'getParentHtml', 'getProduct']
        );
        $config = $this->createMock(\Amasty\Mostviewed\Helper\Config::class);
        $config->expects($this->any())->method('getBlockPosition')->willReturn('es');

        $this->setProperty($this->block, 'config', $config);
        $this->block->expects($this->any())->method('isBundlePacksExists')->willReturn(true);
        $this->block->expects($this->any())->method('getParentHtml')->willReturn('test');

        $this->setProperty($this->block, '_nameInLayout', 'false');
        $this->assertEquals('', $this->block->toHtml());
        $this->setProperty($this->block, '_nameInLayout', 'test');
        $this->assertEquals('test', $this->block->toHtml());
    }

    /**
     * @covers BundlePack::isBundlePacksExists
     */
    public function testIsBundlePacksExists()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);

        $pack = $this->createMock(Pack::class);
        $pack->expects($this->any())->method('getCustomerGroupIds')->willReturn(1);

        $product->expects($this->any())->method('isSaleable')->willReturnOnConsecutiveCalls(false, true, true);
        $this->packRepository->expects($this->any())->method('getPacksByParentProductsAndStore')
            ->willReturnOnConsecutiveCalls(false, [$pack]);

        $this->block->setProduct($product);

        $this->assertFalse($this->block->isBundlePacksExists());
        $this->assertFalse($this->block->isBundlePacksExists());
        $this->assertTrue($this->block->isBundlePacksExists());
    }

    /**
     * @covers BundlePack::getProductDiscount
     * @dataProvider getProductDiscountDataProvider
     */
    public function testGetProductDiscount($discountType, $applyForParents, $result)
    {
        $pack = $this->createMock(PackInterface::class);

        $pack->expects($this->any())->method('getDiscountAmount')->willReturn(5);
        $pack->expects($this->any())->method('getDiscountType')->willReturn($discountType);
        $pack->expects($this->any())->method('getApplyForParent')->willReturn($applyForParents);

        $this->assertEquals($result, $this->block->getProductDiscount($pack, true));
    }

    /**
     * Data provider for getProductIdsByType test
     * @return array
     */
    public function getProductDiscountDataProvider()
    {
        return [
            [1, true, '5%'],
            [2, true, '-5'],
            [1, false, ''],
        ];
    }

    /**
     * @covers BundlePack::getDiscountResult
     * @dataProvider getDiscountResultDataProvider
     */
    public function testGetDiscountResult($data, $result)
    {
        $this->assertEquals($result, $this->block->getDiscountResult($data));
    }

    /**
     * Data provider for getProductIdsByType test
     * @return array
     */
    public function getDiscountResultDataProvider()
    {
        return [
            [
                [
                    'parent_price' => 10,
                    'products' => [1, 2, 3],
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 5,
                    'apply_for_parent' => true,
                ],
                [
                    'final_price' => 5,
                    'discount' => 11,
                ]
            ],
            [
                [
                    'parent_price' => 10,
                    'products' => [4, 2, 3],
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 5,
                    'apply_for_parent' => false,
                ],
                [
                    'final_price' => 10,
                    'discount' => 9
                ]
            ],
            [
                [
                    'parent_price' => 10,
                    'products' => [],
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 5,
                    'apply_for_parent' => false,
                ],
                [
                    'final_price' => 10,
                    'discount' => 0,
                ]
            ],
        ];
    }

    /**
     * @covers BundlePack::applyDiscount
     * @dataProvider applyDiscountDataProvider
     */
    public function testApplyDiscount($price, $config, $result)
    {
        $this->assertEquals($result, $this->invokeMethod($this->block, 'applyDiscount', [$price, $config]));
    }

    /**
     * Data provider for applyDiscount test
     * @return array
     */
    public function applyDiscountDataProvider()
    {
        return [
            [
                10,
                [
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 5,
                ],
                5
            ],
            [
                5,
                [
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 10,
                ],
                0
            ],
            [
                20,
                [
                    'discount_type' => 1,
                    'discount_amount' => 10,
                ],
                18
            ],
        ];
    }
}
