<?php

namespace Tonder\Payment\Gateway\Request;

use Tonder\Payment\Gateway\Helper\MappingCurrency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class RefundAmountDataBuilder extends AmountDataBuilder
{
    /**
     * @param array $buildSubject
     * @return array|array[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(array $buildSubject)
    {
        $amount = SubjectReader::readAmount($buildSubject);
        return [
            self::AMOUNT => sprintf('%.2F', $amount)
        ];
    }
}
