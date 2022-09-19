<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Commercers\CheckoutApproval\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Commercers\OnepageCheckout\Helper\Data;

class Index extends \Magento\Checkout\Controller\Onepage implements HttpGetActionInterface
{
    /**
     * Checkout page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Checkout\Helper\Data $checkoutHelper */
        $checkoutHelper = $this->_objectManager->get(\Magento\Checkout\Helper\Data::class);
        if (!$checkoutHelper->canOnepageCheckout()) {
            $this->messageManager->addErrorMessage(__('One-page checkout is turned off.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->_customerSession->isLoggedIn() && !$checkoutHelper->isAllowedGuestCheckout($quote)) {
            $this->messageManager->addErrorMessage(__('Guest checkout is disabled.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        if($this->_customerSession->isLoggedIn()){
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
            $customer = $customer->load($this->_customerSession->getCustomerId());

            $customerGroupId=$customer->getGroupId();
            $checkoutApprovalHelperData = $this->_objectManager->create('Commercers\CheckoutApproval\Helper\Data');
            $enableCheckoutApproval = $checkoutApprovalHelperData->getEnableCheckoutApproval();
            $messageError = $checkoutApprovalHelperData->getMessageError();
            $pathUrlRedirect = $checkoutApprovalHelperData->getPathUrlRedirect();
            $groupCustomerAllowCheckout = explode(",", $checkoutApprovalHelperData->getGroupCustomerAllowCheckout());
            if($enableCheckoutApproval){
                if(in_array($customerGroupId, $groupCustomerAllowCheckout)){
                    $this->messageManager->addErrorMessage(__($messageError));
                    return $this->resultRedirectFactory->create()->setPath($pathUrlRedirect);
                    // return $this->resultRedirectFactory->create()->setUrl($pathUrlRedirect);
                }
            }
        }

        // generate session ID only if connection is unsecure according to issues in session_regenerate_id function.
        // @see http://php.net/manual/en/function.session-regenerate-id.php
        if (!$this->isSecureRequest()) {
            $this->_customerSession->regenerateId();
        }
        $this->_objectManager->get(\Magento\Checkout\Model\Session::class)->setCartWasUpdated(false);
        $this->getOnepage()->initCheckout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Checkout'));
        return $resultPage;
    }

    /**
     * Checks if current request uses SSL and referer also is secure.
     *
     * @return bool
     */
    private function isSecureRequest(): bool
    {
        $request = $this->getRequest();

        $referrer = $request->getHeader('referer');
        $secure = false;

        if ($referrer) {
            $scheme = parse_url($referrer, PHP_URL_SCHEME);
            $secure = $scheme === 'https';
        }

        return $secure && $request->isSecure();
    }
}
