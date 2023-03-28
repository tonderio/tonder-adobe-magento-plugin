<?php
namespace Tonder\Payment\Plugin\System\Config;

use Magento\Config\Block\System\Config\Form\Field as ConfigFormField;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class FormFieldPlugin
 * @package Tonder\Payment\Plugin\System\Config
 */
class FormFieldPlugin
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * FormFieldPlugin constructor.
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param ConfigFormField $subject
     * @param AbstractElement $element
     */
    public function beforeRender(ConfigFormField $subject, AbstractElement $element)
    {
        $sectionName = $this->request->getParam('section');
        if ($sectionName == 'payment') {
            $originalData = $element->getOriginalData();
            $htmlAttributes = $element->getHtmlAttributes();
            foreach ($htmlAttributes as $attribute) {
                if (isset($originalData[$attribute])) {
                    $element->addCustomAttribute($attribute, $originalData[$attribute]);
                }
            }
        }
    }
}
