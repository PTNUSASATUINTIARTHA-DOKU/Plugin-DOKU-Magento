<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 5/13/19
 * Time: 12:39 AM
 */

namespace Doku\Hosted\Plugin\Block\Adminhtml\Order;


use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Doku\Core\Api\TransactionRepositoryInterface;
use Magento\Setup\Exception;

class View
{

    protected $_urlBuilder;
    protected $order;
    protected $transactionRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Order $order,
        UrlInterface $urlBuilder,
        TransactionRepositoryInterface $transactionRepository
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->order = $order;
        $this->transactionRepository = $transactionRepository;
    }

    public function afterSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {

        $message ='Are you sure you want to do this?';
        $url = '/dokucore/adminhtml_authorization/capture/id/' . $view->getOrderId();

        $order = $this->order->load($view->getOrderId());

        if(!$order->hasInvoices()) {
            try {
                $_dokuTrans = $this->transactionRepository->getByOrderId($view->getOrderId());
                if($_dokuTrans && $_dokuTrans->getPaymentType() == 'AUTHORIZATION' && $_dokuTrans->getAuthorizationStatus() == 'authorization') {
                    $url = $this->urlBuilder->getUrl('dokucore/authorization/capture', ['order_id' => $view->getOrderId()]);
                    $view->addButton(
                        'doku_order_capture',
                        [
                            'label' => __('Capture'),
                            'class' => 'myclass',
                            'onclick' => "confirmSetLocation('{$message}', '{$url}')"
                        ]
                    );

                    $voidUrl = $this->urlBuilder->getUrl('dokucore/authorization/voidpayment', ['order_id' => $view->getOrderId()]);
                    $view->addButton(
                        'doku_order_void',
                        [
                            'label' => __('Void'),
                            'class' => 'myclass',
                            'onclick' => "confirmSetLocation('{$message}', '{$voidUrl}')"
                        ]
                    );
                }


            } catch (\Exception $e) {
            }

        }


    }


}