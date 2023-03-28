<?php
namespace Tonder\Payment\Gateway\Request\Vault;

use Tonder\Payment\Gateway\Request\AVSDataBuilder as Builder;

class AVSDataBuilder extends Builder
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        if (!$this->config->getValue('avs_enable')) {
            return [];
        }
        if (!isset($buildSubject['street']['street'])) {
            return [];
        }
        //Street number and name handler
        $streetNumber = '';
        $streetName = '';
        foreach (preg_split('/,/', $buildSubject['street']['street'][0]) as $result) {
            if (is_numeric($result) || is_numeric($result[0])) {
                $streetNumber .= empty($streetNumber) ? $result : '';
            }
            if (ctype_alpha($result)) {
                $streetName .= empty($streetName) ? $result : '';
            }
        }
        $streetNumber = empty($streetNumber) ? '0' : $streetNumber;
        $streetName = empty($streetName) ? 'Street' : $streetName;

        return [
            self::AVS_INFO => [
                self::AVS_STREET_NAME => $streetName,
                self::AVS_STREET_NUMBER => $streetNumber,
                self::AVS_ZIP_CODE => $buildSubject['street']['post_code']
            ]
        ];
    }
}
