<?php
/**
 * Doku
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://dokucommerce.com/Doku-Commerce-License.txt
 *
 *
 * @package    Doku_RefundRequest
 * @author     Extension Team
 *
 *
 */
namespace Doku\RefundRequest\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Customer\Model\Session as CustomerSession;
use Doku\Core\Helper\Data as DokuHelper;

class Data extends AbstractHelper
{
    /**
     * Doku_CONFIG_ENABLE_MODULE
     */
    const Doku_CONFIG_ENABLE_MODULE = 'doku_refundrequest/doku_refundrequest_general/enable';

    /**
     * Doku_CONFIG_ORDER_REFUND
     */
    const Doku_CONFIG_ORDER_REFUND = 'doku_refundrequest/doku_refundrequest_general/canrefund';

    /**
     * Doku_CONFIG_TITLE_POPUP
     */
    const Doku_CONFIG_POPUP_TITLE = 'doku_refundrequest/doku_refundrequest_config/popup_title';

    /**
     * Doku_CONFIG_ENABLE_DROPDOWN
     */
    const Doku_CONFIG_ENABLE_DROPDOWN = 'doku_refundrequest/doku_refundrequest_config/enable_dropdown';

    /**
     * Doku_CONFIG_DROPDOWN_TITLE
     */
    const Doku_CONFIG_DROPDOWN_TITLE = 'doku_refundrequest/doku_refundrequest_config/dropdown_title';

    /**
     * Doku_CONFIG_ENABLE_OPTION
     */
    const Doku_CONFIG_ENABLE_OPTION = 'doku_refundrequest/doku_refundrequest_config/enable_option';

    /**
     * Doku_CONFIG_OPTION_TITLE
     */
    const Doku_CONFIG_OPTION_TITLE = 'doku_refundrequest/doku_refundrequest_config/option_title';

    /**
     * Doku_CONFIG_DETAIL_TITLE
     */
    const Doku_CONFIG_DETAIL_TITLE = 'doku_refundrequest/doku_refundrequest_config/detail_title';

    /**
     * Doku_CONFIG_TITLE
     */
    const Doku_CONFIG_TITLE = 'doku_refundrequest/doku_refundrequest_config/title';

    /**
     * Doku_CONFIG_ADMIN_EMAIL
     */
    const Doku_CONFIG_ADMIN_EMAIL = 'doku_refundrequest/doku_refundrequest_email_config/admin_email';

    /**
     * Doku_CONFIG_EMAIL_TEMPLATE
     */
    const Doku_CONFIG_EMAIL_TEMPLATE = 'doku_refundrequest/doku_refundrequest_email_config/email_template';

    /**
     * Doku_CONFIG_EMAIL_SENDER
     */
    const Doku_CONFIG_EMAIL_SENDER = 'doku_refundrequest/doku_refundrequest_email_config/email_sender';

    /**
     * Doku_CONFIG_ACCEPT_EMAIL
     */
    const Doku_CONFIG_ACCEPT_EMAIL = 'doku_refundrequest/doku_refundrequest_email_config/accept_email';

    /**
     * Doku_CONFIG_REJECT_EMAIL
     */
    const Doku_CONFIG_REJECT_EMAIL = 'doku_refundrequest/doku_refundrequest_email_config/reject_email';

    const DOKU_CONFIG_REFUND_URL = 'doku_refundrequest/doku_refundrequest_cron_config/refund_url';
    /**
     * ScopeConfigInterface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var OrderCollection
     */
    protected $orderCollectionFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param OrderCollection $orderCollectionFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        OrderCollection $orderCollectionFactory,
        DokuHelper $dokuHelper,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerSession = $customerSession;
        $this->dokuHelper = $dokuHelper;
    }

    //General config admin

    /**
     * Get Config Enable Module
     *
     * @return string
     */
    public function getConfigEnableModule()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_ENABLE_MODULE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getOrderRefund()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_ORDER_REFUND,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get Config Title Module
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get Config Title Module
     *
     * @return string
     */
    public function getPopupModuleTitle()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_POPUP_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get Config Enable Dropdown In Modal Popup
     *
     * @return string
     */
    public function getConfigEnableDropdown()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_ENABLE_DROPDOWN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get Config Title Dropdown Modal Popup
     *
     * @return string
     */
    public function getDropdownTitle()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_DROPDOWN_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get Config Enable Yes/No Option
     *
     * @return string
     */
    public function getConfigEnableOption()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_ENABLE_OPTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get Config Title Yes/No Option
     *
     * @return string
     */
    public function getOptionTitle()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_OPTION_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get Config
     *
     * @return string
     */
    public function getDetailTitle()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_DETAIL_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getAdminEmail()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_ADMIN_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function configSenderEmail()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_EMAIL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getRejectEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_REJECT_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getAcceptEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::Doku_CONFIG_ACCEPT_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getOrderByCustomerId()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $collection = $orders = [];

        if ($customerId) {
            $collection = $this->orderCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'customer_id',
                $customerId
            )->setOrder(
                'created_at',
                'desc'
            );
        }

        if (!empty($collection)) {
            foreach ($collection as $order) {
                $orders[] = [
                    "increment_id" => $order->getIncrementId(),
                    "status" => $order->getStatus()
                ];
            }
        }

        return $orders;
    }

    /**
     * Get Config Title Module
     *
     * @return string
     */
    public function getRefundUrl()
    {
        return $this->scopeConfig->getValue(
            self::DOKU_CONFIG_REFUND_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function doRefund($dataParam) {
        $url = $this->getRefundUrl();
        $ch = curl_init($url);

        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($dataParam) );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/x-www-form-urlencoded"
        ));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $this->dokuHelper->logger(get_class($this) . " ====== REFUND PARAM: " . json_encode($dataParam), 'DOKU_refund');
        $response = curl_exec( $ch );
        curl_close($ch);

        try {
            $xml = new \SimpleXMLElement($response);
            $result = json_decode(json_encode((array) $xml), TRUE);
            $this->dokuHelper->logger(get_class($this) . " ====== REFUND RESPONSE: " . json_encode($result), 'DOKU_refund');
            return $result;
        } catch (\Exception $e) {
            $this->dokuHelper->logger(get_class($this) . " ====== REFUND RESPONSE: " . $e->getMessage(), 'DOKU_refund');
            return false;
        }
    }

}
