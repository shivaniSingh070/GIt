<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Command;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;

class CommandFactory
{
    const REDEEM_COMMAND = 'redeem';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManger;

    /**
     * @var CommandInterface
     */
    private $commands;

    public function __construct(ObjectManagerInterface $objectManger, $commands = [])
    {
        $this->objectManger = $objectManger;
        $this->commands = $commands;
    }

    /**
     * @param string $command
     * @param array $arguments
     * @throws NotFoundException
     * @throws \InvalidArgumentException
     * @return CommandInterface
     */
    public function create(string $command, array $arguments = []): CommandInterface
    {
        if (!isset($this->commands[$command])) {
            throw new NotFoundException(
                __('The "%1" command executor isn\'t defined. Verify the executor and try again.', $command)
            );
        }
        $commandInstance = $this->objectManger->create($this->commands[$command], $arguments);
        if (!$commandInstance instanceof CommandInterface) {
            throw new \InvalidArgumentException(
                'The command instance "' . $command . '" must implement '
                . \Amasty\GiftCardAccount\Model\GiftCardAccount\Command\CommandInterface::class
            );
        }

        return $commandInstance;
    }
}
