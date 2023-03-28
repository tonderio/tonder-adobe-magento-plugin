<?php

namespace Tonder\Payment\Block\Message;

use Magento\Framework\View\Element\Template;

class Notice extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * Notice constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Session\SessionManagerInterface $session,
        array $data = []
    ) {
        $this->session = $session;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getNoticeMessage()
    {
        $this->session->start();
        return $this->session->getMessage();
    }

    /**
     * @return mixed
     */
    public function unSetNoticeMessage()
    {
        $this->session->start();
        return $this->session->unsMessage();
    }
}
