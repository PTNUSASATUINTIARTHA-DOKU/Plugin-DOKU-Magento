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
namespace Doku\RefundRequest\Controller\Order;

use Doku\RefundRequest\Helper\Data;
use Doku\RefundRequest\Helper\Email;
use Doku\RefundRequest\Model\RequestFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Email
     */
    protected $emailSender;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var
     */
    protected $resultFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * Index constructor.
     * @param Email $emailSender
     * @param Data $helper
     * @param OrderInterface $orderInterface
     * @param RequestFactory $requestFactory
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Email $emailSender,
        Data $helper,
        OrderInterface $orderInterface,
        RequestFactory $requestFactory,
        Context $context,
        PageFactory $resultPageFactory,
        Validator $formKeyValidator,
        Filesystem $filesystem,
        UploaderFactory $fileUploader
    ) {
        $this->emailSender        = $emailSender;
        $this->helper             = $helper;
        $this->orderInterface     = $orderInterface;
        $this->requestFactory    = $requestFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->formKeyValidator = $formKeyValidator;

        $this->filesystem           = $filesystem;
        $this->fileUploader         = $fileUploader;

        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage("Invalid request!");
            return $resultRedirect->setPath('customer/account/');
        }
        $model          = $this->requestFactory->create();
        $data           = $this->getRequest()->getPostValue();

        $attachDir = 'doku_refund/';
        $fileInput = 'doku-attachment';

        if ($data) {
            if ($this->helper->getConfigEnableDropdown()) {
                $option = $data['doku-option'];
            } else {
                $option = '';
            }
            if ($this->helper->getConfigEnableDropdown()) {
                $radio = $data['doku-radio'];
            } else {
                $radio = '';
            }
            $reasonComment = $data['doku-refund-reason'];
            $incrementId   = $data['doku-refund-order-id'];
            $orderData     = $this->orderInterface->loadByIncrementId($incrementId);
            try {

                $file = $this->getRequest()->getFiles($fileInput);
                $fileName = ($file && array_key_exists('name', $file)) ? $file['name'] : null;

                if ($file && $fileName) {
                    $target = $this->mediaDirectory->getAbsolutePath($attachDir);

                    /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                    $uploader = $this->fileUploader->create(['fileId' => $fileInput]);

                    // set allowed file extensions
                    $uploader->setAllowedExtensions(['jpg','jpeg', 'png']);

                    // allow folder creation
                    $uploader->setAllowCreateFolders(true);

                    // rename file name if already exists
                    $uploader->setAllowRenameFiles(true);

                    // rename the file name into lowercase
                    // but this one is not working
                    // we can simply use strtolower() function to rename filename to lowercase
                    // $uploader->setFilenamesCaseSensitivity(true);

                    // enabling file dispersion will
                    // rename the file name into lowercase
                    // and create nested folders inside the upload directory based on the file name
                    // for example, if uploaded file name is IMG_123.jpg then file will be uploaded in
                    // pub/media/your-upload-directory/i/m/img_123.jpg
                    // $uploader->setFilesDispersion(true);

                    // upload file in the specified folder
                    $result = $uploader->save($target, $incrementId."_" . $fileName);

                    //echo '<pre>'; print_r($result); exit;

                    if ($result['file']) {
                        //$this->messageManager->addSuccess(__('File has been successfully uploaded.'));
                    }

                    $model->setDokuAttachment($uploader->getUploadedFileName());
                }

                $model->setOption($option);
                $model->setRadio($radio);
                $model->setOrderId($incrementId);
                $model->setReasonComment($reasonComment);
                $model->setCustomerName($orderData->getCustomerName());
                $model->setCustomerEmail($orderData->getCustomerEmail());
                $model->setDokuRefundType($data['doku-refund-type']);
                $model->save();
                try {
                    $this->sendEmail($orderData);
                    $this->messageManager
                        ->addSuccessMessage(__('Your refund request number #' . $incrementId . ' has been submited.'));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('customer/account/');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('customer/account/');
            }
        }
        return $resultRedirect->setPath('customer/account/');
    }

    /**
     * @param $orderData
     */
    protected function sendEmail($orderData)
    {
        $emailTemplate = $this->helper->getEmailTemplate();
        $adminEmail    = $this->helper->getAdminEmail();
        $adminEmails   = explode(",", $adminEmail);
        $countEmail    = count($adminEmails);
        if ($countEmail > 1) {
            foreach ($adminEmails as $value) {
                $value             = str_replace(' ', '', $value);
                $emailTemplateData = [
                    'adminEmail'   => $value,
                    'incrementId'  => $orderData->getIncrementId(),
                    'customerName' => $orderData->getCustomerName(),
                    'createdAt'    => $orderData->getCreatedAt(),
                ];
                $this->emailSender->sendEmail($value, $emailTemplate, $emailTemplateData);
            }
        } else {
            $emailTemplateData = [
                'adminEmail'   => $adminEmail,
                'incrementId'  => $orderData->getIncrementId(),
                'customerName' => $orderData->getCustomerName(),
                'createdAt'    => $orderData->getCreatedAt(),
            ];
            $this->emailSender->sendEmail($adminEmail, $emailTemplate, $emailTemplateData);
        }
    }
}
