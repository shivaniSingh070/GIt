<?php

namespace Amasty\ImportCore\Test\Unit\FieldModifier;

use Amasty\ImportCore\Import\DataHandling\FieldModifier\Uppercase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\Uppercase
 */
class UppercaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'simple_test' => ["test", "TEST"],
            'mixed'       => ["hElLo", "HELLO"],
            'unicode'     => ["ТьмАФФки", "ТЬМАФФКИ"],
        ];
    }

    /**
     * @param $value
     * @param $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform(string $value, string $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(Uppercase::class);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
