<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Command\Redeem;

use Magento\Framework\Exception\NotFoundException;

class ProcessorPool
{
    const AM_STORECREDIT = 'am_storecredit';

    /**
     * @var ProcessorInterface
     */
    private $processors;

    public function __construct($processors = [])
    {
        $this->checkProcessorInstance($processors);
        $this->processors = $processors;
    }

    /**
     * @param string $processor
     * @throws NotFoundException
     * @return ProcessorInterface
     */
    public function get(string $processor): ProcessorInterface
    {
        if (!isset($this->processors[$processor])) {
            throw new NotFoundException(
                __('The "%1" processor executor isn\'t defined. Verify the executor and try again.', $processor)
            );
        }

        return $this->processors[$processor];
    }

    /**
     * @param array $processors
     * @throws \InvalidArgumentException
     * @return void
     */
    private function checkProcessorInstance(array $processors): void
    {
        foreach ($processors as $processorKey => $processor) {
            if (!$processor instanceof ProcessorInterface) {
                throw new \InvalidArgumentException(
                    'The processor instance "' . $processorKey . '" must implement '
                    . \Amasty\GiftCardAccount\Model\GiftCardAccount\Command\Redeem\ProcessorInterface::class
                );
            }
        }
    }
}
