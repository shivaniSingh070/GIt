<?php
/**
 *overide by anoop on 10-07-19 to resolve error message when redirect from one store to other
 *see trello - https://trello.com/c/gMJPp8v5/162-seo-duplicate-content-vermeidung-durch-non-html-und-www-non-www-weiterleitung
*/
declare(strict_types=1);

namespace Pixelmechanics\Engelsrufer\Controller\Store;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreResolver;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Session\Generic as Session;

/**
 * Builds correct url to target store and performs redirect.
 */

class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var SidResolverInterface
     */
    private $sidResolver;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Context $context
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreResolverInterface $storeResolver
     * @param Session $session
     * @param SidResolverInterface $sidResolver
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $storeRepository,
        StoreResolverInterface $storeResolver,
        Session $session,
        SidResolverInterface $sidResolver
    ) {
        parent::__construct($context);
        $this->storeRepository = $storeRepository;
        $this->storeResolver = $storeResolver;
        $this->session = $session;
        $this->sidResolver = $sidResolver;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Store\Model\Store $currentStore */
        $currentStore = $this->storeRepository->getById($this->storeResolver->getCurrentStoreId());
        $targetStoreCode = $this->_request->getParam(StoreResolver::PARAM_NAME);
        $this->session->setCustomTargetStore($targetStoreCode);
        // $_SESSION['custom_target_store'] = $targetStoreCode;
        $fromStoreCode = $this->_request->getParam('___from_store');
        $this->session->setCustomFromStore($fromStoreCode);
        // $_SESSION['custom_from_store'] = $fromStoreCode;
        $error = null;

        if ($targetStoreCode === null) {
            return $this->_redirect($currentStore->getBaseUrl());
        }

        try {
            /** @var \Magento\Store\Model\Store $targetStore */
            $fromStore = $this->storeRepository->get($fromStoreCode);
        } catch (NoSuchEntityException $e) {
            $error = __('Requested store is not found');
        }

        if ($error !== null) {
            $this->messageManager->addErrorMessage($error);
            $this->_redirect->redirect($this->_response, $currentStore->getBaseUrl());
        } else {
            $encodedUrl = $this->_request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED);

            $query = [
                '___from_store' => $fromStore->getCode(),
                StoreResolverInterface::PARAM_NAME => $targetStoreCode,
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $encodedUrl,
            ];
            /**
            * Removed the session ID query string from  the URl as it is deprecated in Magento v 2.3.3 or above
            * PM JM, 25.03.21, @link - https://trello.com/c/zHx4nbni/129-2020-06-1-update-magento-version-234
            */
            // if ($this->sidResolver->getUseSessionInUrl()) {
            //     // allow customers to stay logged in during store switching
            //     $sidName = $this->sidResolver->getSessionIdQueryParam($this->session);
            //     $query[$sidName] = $this->session->getSessionId();
            // }
            $arguments = [
                '_nosid' => true,
                '_query' => $query
            ];
            $this->_redirect->redirect($this->_response, 'stores/store/switch', $arguments);
        }
    }
}
