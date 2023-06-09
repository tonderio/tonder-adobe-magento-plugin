<?php
namespace Tonder\Payment\Model\Adminhtml\Source;

use Magento\Payment\Model\Source\Cctype as PaymentCctype;

/**
 * Class Cctype provides source for backend cctypes selector
 */
class Cctype extends PaymentCctype
{
    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return ['AE','VI','MC','DI','JCB','DN'];
    }

    /**
     * Geting credit cards types
     *
     * @return array
     */
    public function getCcTypes()
    {
        return $this->_paymentConfig->getCcTypes();
    }
}
