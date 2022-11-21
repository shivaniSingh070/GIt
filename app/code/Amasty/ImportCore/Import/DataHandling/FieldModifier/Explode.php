<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Import\DataHandling\AbstractModifier;
use Amasty\ImportCore\Import\DataHandling\ModifierProvider;

class Explode extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var string
     */
    private $separator = ',';

    public function __construct($config = [])
    {
        parent::__construct($config);
        if (isset($config['separator'])) {
            $this->separator = $config['separator'];
        }
    }

    public function transform($value)
    {
        if (!is_array($value)) {
            return explode($this->separator, trim($value, $this->separator));
        }

        return $value;
    }

    public function getGroup(): string
    {
        return ModifierProvider::TEXT_GROUP;
    }

    public function getLabel(): string
    {
        return __('Explode')->getText();
    }
}
