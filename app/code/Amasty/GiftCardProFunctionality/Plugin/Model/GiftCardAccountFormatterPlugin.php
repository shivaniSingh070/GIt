<?php
declare(strict_types=1);

namespace Amasty\GiftCardProFunctionality\Plugin\Model;

use Amasty\GiftCard\Model\Config\Source\Usage;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardProFunctionality\Model\GiftCardAccount\Usage\Checker;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class GiftCardAccountFormatterPlugin
{
    /**
     * @var Usage
     */
    private $usage;

    /**
     * @var Checker
     */
    private $usageChecker;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Usage $usage,
        Checker $usageChecker,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager
    ) {
        $this->usage = $usage;
        $this->usageChecker = $usageChecker;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
    }

    public function afterGetFormattedData($subject, $result, GiftCardAccountInterface $account)
    {
        $result['usage'] = $this->usage->getValueByKey($account->getUsage());
        if ($this->usageChecker->isSingleUsed($account)) {
            $result['balance'] = $this->getCurrentBalance(0, $account->getWebsiteId());
        }

        return $result;
    }

    /**
     * @param float $price
     * @param int $websiteId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCurrentBalance(float $price, int $websiteId): string
    {
        $website = $this->storeManager->getWebsite($websiteId);

        return (string)$this->priceCurrency->format($price, true, 2, $website, $website->getBaseCurrencyCode());
    }
}
