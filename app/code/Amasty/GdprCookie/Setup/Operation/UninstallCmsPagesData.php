<?php

declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

class UninstallCmsPagesData
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PageRepositoryInterface $pageRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->pageRepository = $pageRepository;
    }

    public function execute()
    {
        $this->searchCriteriaBuilder->addFilter(
            PageInterface::IDENTIFIER,
            ['cookie-settings', 'cookie-policy'],
            'in'
        );
        $searchCriteria = $this->searchCriteriaBuilder->create();

        try {
            array_map(function ($cmsPage) {
                $this->pageRepository->delete($cmsPage);
            }, $this->pageRepository->getList($searchCriteria)->getItems());
        } catch (LocalizedException $e) {
            null;
        }
    }
}
