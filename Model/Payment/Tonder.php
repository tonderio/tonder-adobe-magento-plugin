<?php
declare(strict_types=1);

namespace Tonder\Payment\Model\Payment;

class Tonder extends \Magento\Payment\Model\Method\AbstractMethod
{
    public const METHOD_CODE = 'tonder';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $_isOffline = true;

    public function getInstructions()
    {
        $instructions = $this->getConfigData('instructions');
        return $instructions !== null ? trim($instructions) : '';
    }
}

