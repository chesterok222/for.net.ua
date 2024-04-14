<?php

namespace Ecomitize\BuyNow\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 *
 */
class SendEmail extends AbstractHelper
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var
     */
    protected $senderResolvAer;

    /**
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $state
     * @param Config $config
     * @param SenderResolverInterface $senderResolver
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        Config $config,
        SenderResolverInterface $senderResolver
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->config = $config;
        $this->senderResolver = $senderResolver;
        parent::__construct($context);
    }

    /**
     * @param $templateVars
     * @return bool
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute($templateVars)
    {
        $templateId = $this->config->getEmailTemplate();
        $from = $this->senderResolver->resolve($this->config->getIdentity());
        $toEmail = $this->config->getSendTo();

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $this->inlineTranslation->suspend();

            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
            return false;
        }

        return true;
    }
}
