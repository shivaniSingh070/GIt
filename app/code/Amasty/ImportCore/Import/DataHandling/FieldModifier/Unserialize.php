<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\Base\Model\Serializer;
use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Import\DataHandling\AbstractModifier;
use Amasty\ImportCore\Import\DataHandling\ModifierProvider;

class Unserialize extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        $config,
        Serializer $serializer
    ) {
        parent::__construct($config);
        $this->serializer = $serializer;
    }

    public function transform($value)
    {
        return $this->serializer->unserialize($value) ?: $value;
    }

    public function getGroup(): string
    {
        return ModifierProvider::TEXT_GROUP;
    }

    public function getLabel(): string
    {
        return __('Unserialize')->getText();
    }
}
