<?php


namespace Tonder\Payment\Gateway\Request;


use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;

class DataBuilder
{
    /**
     * @var CollectionFactory
     */
    protected $paymentTokenCollectionFactory;

    /**
     * VaultDetailsDataBuilder constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->paymentTokenCollectionFactory = $collectionFactory;
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function buildData(array $buildSubject)
    {
        /** @var PaymentDataObjectInterface $paymentObject */
        $paymentObject = SubjectReader::readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentObject->getPayment();
        $order = $payment->getOrder();

        if (!$order->getCustomerId()) {
            throw new LocalizedException(__('Could not find customer ID'));
        }
        /** @var PaymentToken $token */
        $token = $this->paymentTokenCollectionFactory->create()
            ->addFieldToFilter('customer_id', $order->getCustomerId())
            ->addFieldToFilter('public_hash', $payment->getAdditionalInformation('public_hash'))
            ->getFirstItem();
        if (!$token->getGatewayToken()) {
            throw new LocalizedException(__('Could not find token for this card. Please use a new one.'));
        }
        return [$token, $order];
    }
}
