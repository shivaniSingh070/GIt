<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\CartAction\Response;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterfaceFactory;
use Amasty\GiftCardAccount\Model\GiftCardAccount\CartAction\Response\Builder\BuilderInterface;

class Builder
{
    const ADD_TO_CART = 'add_to_cart';

    /**
     * @var BuilderInterface[]
     */
    private $builders;

    /**
     * @var GiftCardAccountResponseInterfaceFactory
     */
    private $giftCardAccountResponseFactory;

    /**
     * @param BuilderInterface[] $builders
     * @param GiftCardAccountResponseInterfaceFactory $giftCardAccountResponseFactory
     */
    public function __construct(
        GiftCardAccountResponseInterfaceFactory $giftCardAccountResponseFactory,
        array $builders
    ) {
        $this->checkBuilderInstance($builders);
        $this->builders = $builders;
        $this->giftCardAccountResponseFactory = $giftCardAccountResponseFactory;
    }

    public function build($account, $type)
    {
        /** @var GiftCardAccountResponseInterface $response */
        $response = $this->giftCardAccountResponseFactory->create();
        $response->setAccount($account);

        foreach ($this->builders[$type] as $builder) {
            $builder->build($account, $response);
        }

        return $response;
    }

    /**
     * @param array $builders
     * @throws \InvalidArgumentException
     * @return void
     */
    private function checkBuilderInstance(array $builders): void
    {
        foreach ($builders as $builderGroups) {
            foreach ($builderGroups as $builderKey => $builder) {
                if (!$builder instanceof BuilderInterface) {
                    throw new \InvalidArgumentException(
                        'The processor instance "' . $builderKey . '" must implement ' . BuilderInterface::class
                    );
                }
            }
        }
    }
}
