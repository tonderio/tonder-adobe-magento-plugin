<?php

namespace Tonder\Payment\Controller\Key;

use Tonder\Payment\Model\Ui\Direct\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;

class GetKeyData extends Action
{
    /**
     * @var CollectionFactory
     */
    protected $paymentTokenCollectionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * GetKeyData constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param ConfigProvider $configProvider
     * @param CommandPoolInterface $commandPool
     * @param CollectionFactory $paymentTokenCollectionFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ConfigProvider $configProvider,
        CommandPoolInterface $commandPool,
        CollectionFactory $paymentTokenCollectionFactory
    ) {
        $this->configProvider = $configProvider;
        $this->customerSession = $customerSession;
        $this->commandPool = $commandPool;
        $this->paymentTokenCollectionFactory = $paymentTokenCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $controllerResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $postParams = $this->getRequest()->getPostValue();
        if ($this->checkData($postParams)) {
            $data = $this->prepareData($postParams);
            if ($data) {
                try {
                    $this->commandPool->get('get_key')->execute($data);
                    $result = true;
                } catch (\Exception $e) {
                    $result = false;
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        $postParams = [];
        return $controllerResult->setData($result);
    }

    /**
     * @param $postParams
     * @return array|false
     */
    public function prepareData($postParams)
    {
        $data = [];
        $storeInfo = $this->configProvider->getStoreInfo();
        if ($storeInfo[0]) {
            $data['store_id'] = $storeInfo[0];
        } else {
            return false;
        }

        if ($storeInfo[1]) {
            $data['api_token'] = $storeInfo[1];
        } else {
            return false;
        }
        $customer = $this->customerSession->getCustomer();
        if ($customer->getEntityId()) {
            $data['cust_id'] = (string)$customer->getEntityId();
            $data['email'] = $customer->getEmail();
        } else {
            return false;
        }

        $data['pan'] = $postParams['card_data']['additional_data']['cc_number'];
        $data['year'] = $postParams['card_data']['additional_data']['cc_exp_year'];
        $data['month'] = $postParams['card_data']['additional_data']['cc_exp_month'];
        $data['type'] = $postParams['card_data']['additional_data']['cc_type'];

        $year = substr($postParams['card_data']['additional_data']['cc_exp_year'], 2, 3);
        $month = sprintf("%02d", $postParams['card_data']['additional_data']['cc_exp_month']);
        $data['expdate'] = $year . $month;
        $data['isUs'] = $postParams['isUs'];
        $data['cvd_value'] = $postParams['card_data']['additional_data']['cc_cid'];
        $data['street'] = $postParams['address'];
        return $data;
    }

    /**
     * @param $postParams
     * @return bool
     */
    protected function checkData($postParams)
    {
        $collection = $this->paymentTokenCollectionFactory->create();

        //Set customer_id condition
        $fields = 'customer_id';
        $customer = $this->customerSession->getCustomer();
        if ($customer->getEntityId()) {
            $condition = ['like' => $customer->getEntityId()];
        } else {
            return false;
        }
        $collection->addFieldToFilter($fields, $condition);

        //Set details condition
        $fields = 'details';
        $month = $postParams['card_data']['additional_data']['cc_exp_month'];
        $year = $postParams['card_data']['additional_data']['cc_exp_year'];

        $details = $this->convertDetailsToJSON([
            'type' => $postParams['card_data']['additional_data']['cc_type'],
            'maskedCC' => substr($postParams['card_data']['additional_data']['cc_number'], -4),
            'expirationDate' => $month . '/' . $year
        ]);
        $condition = ['like' => $details];
        $collection->addFieldToFilter($fields, $condition);
        if ($collection->getSize() > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $details
     * @return string
     */
    private function convertDetailsToJSON($details)
    {
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
    }
}
