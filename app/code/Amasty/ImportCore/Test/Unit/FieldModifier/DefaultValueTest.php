<?php

namespace Amasty\ImportCore\Test\Unit\FieldModifier;

use Amasty\ImportCore\Import\DataHandling\FieldModifier\DefaultValue;
use Amasty\ImportCore\Import\Utils\Config\ArgumentConverter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\DefaultValue
 */
class DefaultValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'basic' => [
                ['value' => '5', 'force' => false],
                '',
                "5"
            ],
            'null' => [
                ['value' => '6', 'force' => false],
                null,
                '6'
            ],
            'force' => [
                ['value' => '7', 'force' => true],
                'test',
                '7'
            ],
            'notforce' => [
                ['value' => '6', 'force' => false],
                'test',
                'test'
            ],
            'empty_array' => [
                ['value' => '5', 'force' => true],
                [],
                '5'
            ]
        ];
    }

    /**
     * @param array $config
     * @param $value
     * @param $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform(array $config, $value, $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(DefaultValue::class, ['config' => $config]);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }

    /**
     * @param $config
     * @param $value
     * @param $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransformException(array $config, $value, $expectedResult)
    {
        $argumentConverterMock = $this->createMock(ArgumentConverter::class);
        $this->expectException(\LogicException::class);
        new DefaultValue([], $argumentConverterMock);
    }
}
