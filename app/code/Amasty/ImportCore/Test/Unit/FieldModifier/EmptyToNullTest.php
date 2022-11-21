<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Test\Unit\FieldModifier;

use Amasty\ImportCore\Import\DataHandling\FieldModifier\EmptyToNull;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\EmptyToNull
 */
class EmptyToNullTest extends \PHPUnit\Framework\TestCase
{
    public function transformDataProvider(): array
    {
        return [
            'empty' => ['', null],
            'not_empty' => ['test', 'test'],
            'space' => [' ', null],
            'null' => [null, null],
            'numerical' => [24, 24]
        ];
    }
    /**
     * @param $value
     * @param $expectedResult
     * @dataProvider transformDataProvider
     */
    public function testTransform($value, $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(EmptyToNull::class);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
