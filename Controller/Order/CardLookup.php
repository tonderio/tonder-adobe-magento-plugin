<?php
namespace Tonder\Payment\Controller\Order;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as PaymentTokenCollectionFac;

class CardLookup extends Action
{
    const STORE_ID = 'store_id';
    const API_TOKEN = 'api_token';
    const RESPONSE_CODE = 'ResponseCode';

    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var CustomerSession
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_jsonFramework;

    /**
     * @var PaymentTokenCollectionFac
     */
    protected $paymentTokenCollectionFactory;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var \Tonder\Payment\Logger\Logger
     */
    protected $logger;

    /**
     * CardLookup constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonFramework
     * @param PaymentTokenCollectionFac $paymentTokenCollectionFactory
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param ConfigInterface $config
     * @param \Tonder\Payment\Logger\Logger $logger
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CustomerSession $customerSession,
        \Magento\Framework\Serialize\Serializer\Json $jsonFramework,
        PaymentTokenCollectionFac $paymentTokenCollectionFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        ConfigInterface $config,
        \Tonder\Payment\Logger\Logger $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_jsonFramework = $jsonFramework;
        $this->paymentTokenCollectionFactory = $paymentTokenCollectionFactory;
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($this->getRequest()->isAjax()) {
            $payload = $this->getRequest()->getParam('payload');
            $store_id  = $this->config->getValue(self::STORE_ID);
            $api_token = $this->config->getValue(self::API_TOKEN);
            $quote = $this->checkoutSession->getQuote();
            $merchantUrl = $this->_url->getUrl('moneris/order/postback');
            $cardData = $payload['cardData'] ?? null;
            $token = '';
            if (isset($payload['use_vault']) && $payload['use_vault']) {
                $token = $this->paymentTokenCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $quote->getCustomerId())
                    ->addFieldToFilter('public_hash', $payload['public_hash'])
                    ->getFirstItem();
                if (!$token->getGatewayToken()) {
                    throw new LocalizedException(__('Could not find token for this card. Please use a new one.'));
                }
                $cardData = $this->_jsonFramework->unserialize($token->getDetails());
            }
            if ($cardData == null) {
                throw new \Exception(__('Missing Card Information.'));
            }
            $pan = $cardData['accountNumber'] ?? '';
            //Card lookup request
            $result->setData([
                'can_use_3ds'   => false
            ]);
            $quote->getPayment()->setAdditionalInformation('can_use_3ds', false);
            $this->quoteRepository->save($quote);
        }

        return $result;
    }
}
