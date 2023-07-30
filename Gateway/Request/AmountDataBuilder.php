<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class AmountDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class AmountDataBuilder extends AbstractDataBuilder
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
