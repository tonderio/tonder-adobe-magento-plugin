<?php
namespace Tonder\Payment\Gateway\Request\Vault;

use Tonder\Payment\Gateway\Request\CVDDataBuilder as Builder;

/**
 * Class CVDDataBuilder
 * @package Tonder\Payment\Gateway\Request\Vault
 */
class CVDDataBuilder extends Builder
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        if (!$this->config->getValue('cvd_enable')) {
            return [];
        }
        if (!isset($buildSubject[self::CVD_VALUE])) {
            return [];
        }
        return [
            self::CVD_INFO => [
                self::CVD_INDICATOR => "1",
                self::CVD_VALUE => $buildSubject[self::CVD_VALUE]
            ]
        ];
    }
}
