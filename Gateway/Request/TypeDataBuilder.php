<?php
/**
 * Copyright © Tonder JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 11/05/2020
 * Time: 14:48
 */

namespace Tonder\Payment\Gateway\Request;


use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class TypeDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            self::REPLACE_KEY => [
                'type' => 'card_verification',
            ]
        ];
    }
}
