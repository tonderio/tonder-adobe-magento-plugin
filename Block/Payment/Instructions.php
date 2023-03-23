<?php

namespace Tonder\Payment\Block\Payment;

use Magento\Payment\Block\Info;

class Instructions extends Info
{
    /**
     * @var string
     */
    protected $_instructions;

    /**
     * @var string
     */
    protected $_template = 'Tonder_Payment::payment/instructions.phtml';

    /**
     * Get payment instructions
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInstructions()
    {
        if ($this->_instructions === null) {
            $this->_instructions = $this->getInfo()->getAdditionalInformation('instructions');
        }
        return $this->_instructions;
    }
}
