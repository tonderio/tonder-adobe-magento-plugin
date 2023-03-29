<?php
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class CavvDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const CAVV = 'cavv';

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
                ]
            ];
        }
        return [];
    }
}
