<?php

namespace Tonder\Payment\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Module\ResourceInterface;

/**
 * Version Class used to display version code of this module in admin panel
 */
class Version extends Field
{
    /**
     * @var ResourceInterface
     */
    protected $moduleResource;

    public function __construct(
        ResourceInterface $moduleResource,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->moduleResource = $moduleResource;

        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->moduleResource->getDbVersion('Tonder_Payment');
    }
}
