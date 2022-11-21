<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Layout\Customer;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccountFormatter;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;

class Cards implements LayoutProcessorInterface
{
    /**
     * @var GiftCardAccountFormatter
     */
    private $accountFormatter;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Repository
     */
    private $accountRepository;

    public function __construct(
        GiftCardAccountFormatter $accountFormatter,
        Session $session,
        UrlInterface $url,
        Repository $accountRepository
    ) {
        $this->accountFormatter = $accountFormatter;
        $this->session = $session;
        $this->url = $url;
        $this->accountRepository = $accountRepository;
    }

    public function process(array $jsLayout): array
    {
        $jsLayout['components']['amcard-account-render']['deleteCardUrl'] =
            $this->url->getUrl('amgcard/account/remove');
        $jsLayout['components']['amcard-giftcards']['addCardUrl'] = $this->url->getUrl('amgcard/account/addCard');
        $jsLayout['components']['amcard-giftcards']['cards'] = $this->getCardsFront();

        return $jsLayout;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCardsFront(): array
    {
        $cards = $this->accountRepository->getAccountsByCustomerId((int)$this->session->getCustomerId());
        $preparedCards = [];

        foreach ($cards as $card) {
            $preparedCards[] = $this->accountFormatter->getFormattedData($card);
        }

        return $preparedCards;
    }
}
