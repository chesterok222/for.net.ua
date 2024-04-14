<?php

namespace Ecomitize\BuyNow\Controller\Place;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;


/**
 *
 */
class Order extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Ecomitize\BuyNow\Helper\SendEmail
     */
    protected $sendEmail;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ecomitize\BuyNow\Helper\SendEmail $sendEmail
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sendEmail = $sendEmail;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $result = $this->sendEmail->execute($this->getRequest()->getParams());
        return $resultJson->setData(['success' => $result]);
    }
}
