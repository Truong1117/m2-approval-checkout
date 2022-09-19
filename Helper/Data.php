<?php
namespace Commercers\CheckoutApproval\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'checkout_approval/general/config';
    const XML_PATH_CUSTOMER_ALLOW_CHECKOUT = 'checkout_approval/general/customer_allow_checkout';
    const XML_PATH_MESSAGE_ERROR = 'checkout_approval/general/message_error';
    const XML_PATH_URL_REDIRECT = 'checkout_approval/general/url_redirect';
    protected $_storeManager;
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }
    public function getConfigValue($path,$storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    public function getEnableCheckoutApproval(){
        return $this->getConfigValue(self::XML_PATH_ENABLED);
    }
    public function getGroupCustomerAllowCheckout(){
        return $this->getConfigValue(self::XML_PATH_CUSTOMER_ALLOW_CHECKOUT);
    }
    public function getMessageError(){
        return $this->getConfigValue(self::XML_PATH_MESSAGE_ERROR);
    }
    public function getPathUrlRedirect(){
        return $this->getConfigValue(self::XML_PATH_URL_REDIRECT);
    }
}

