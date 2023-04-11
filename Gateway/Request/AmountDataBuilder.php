<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Tonder\Payment\Gateway\Helper\MappingCurrency;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class AmountDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class AmountDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            self::AMOUNT => (float)sprintf('%.2F', SubjectReader::readAmount($buildSubject))
        ];
    }
}
