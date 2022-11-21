<?php

namespace Amasty\ImportCore\Test\Unit\FieldModifier;

use Amasty\ImportCore\Import\DataHandling\FieldModifier\Trim;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\Trim
 */
class TrimTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $value
     * @param $expectedResult
     * @testWith ["test", "test"]
     *           [" test      ", "test"]
     *           ["   ", ""]
     */
    public function testTransform(string $value, string $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(Trim::class);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
