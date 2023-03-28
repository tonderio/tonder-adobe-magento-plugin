<?php
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class CavvDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const CAVV = 'cavv';
    const THREE_D_VERSION = 'threeds_version';
    const ThreeDSServerTransId = 'threeds_server_trans_id';

    /**
     * @param array $buildSubject
     * @return array|array[]
     */
    public function build(array $buildSubject)
    {
        $cavv = '';
        if (isset($buildSubject['response']['Message']) && isset($buildSubject['response']['Cavv'])) {
            $cavv = $buildSubject['response']['Cavv'];
        }
        if ($cavv != '') {
            return [
                self::REPLACE_KEY => [
                    self::CAVV => $cavv,
                    self::THREE_D_VERSION => '2',
                    self::ThreeDSServerTransId => $buildSubject['response']['ThreeDSServerTransId']
                ]
            ];
        }
        return [];
    }
}
