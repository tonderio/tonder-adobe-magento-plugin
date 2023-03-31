<?php
namespace Tonder\Payment\Model\Config;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;

class Config extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var ConfigInterface
     */
    protected $_config;

    /**
     * @param Context $context
     * @param ConfigInterface $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        array $data = []
    ) {
        $this->_config=$config;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _renderValue(AbstractElement $element)
    {
        $field_config = $element->getData('field_config');
        if (!($id = $field_config['id'])) {
            throw new NoSuchEntityException(__('Element Id not found!'));
        }
        $approved_url = $this->_urlBuilder->getBaseUrl() . 'tonder/payment/complete';
        $declined_url = $this->_urlBuilder->getBaseUrl() . 'tonder/order/cancel';
        switch ($id) {
            case 'cancel_url':
            case 'declined_url':
                $element->addData([
                    'value' => $declined_url
                ]);
                break;
            default:
                $element->addData([
                    'value' => $approved_url
                ]);
                break;
        }
        return parent::_renderValue($element);
    }
}
