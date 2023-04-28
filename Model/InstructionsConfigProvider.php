<?php

namespace Tonder\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use Tonder\Payment\Model\Method\Adapter;

class InstructionsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        Adapter::METHOD_CODE,
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Asset service
     *
     * @var Repository
     */
    protected $assetRepo;

    protected $icons = [];

    /**
     * @var CcConfig
     */
    protected $ccConfig;

    /**
     * @var Source
     */
    protected $assetSource;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @param Repository $assetRepo
     * @throws LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Repository $assetRepo,
        CcConfig $ccConfig,
        Source $assetSource
    ) {
        $this->escaper = $escaper;
        $this->assetRepo = $assetRepo;
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $instructions = $this->getInstructions($code);
                $policyLink = $this->assetRepo->getUrl('Tonder_Payment::pdf/policy.pdf');
                $instructions = preg_replace("/\{\{policy_link\}\}(.*?)\{\{\/policy_link\}\}/", '<a href="'. $policyLink .'" download="'. __("Policy") .'">$1</a>
', $instructions);
                $termLink = $this->assetRepo->getUrl('Tonder_Payment::pdf/term-and-condition.pdf');
                $instructions = preg_replace("/\{\{term_link\}\}(.*?)\{\{\/term_link\}\}/", '<a href="'. $termLink .'" download="'. __("Term-and-Condition") .'">$1</a>
', $instructions);
                $config['payment']['instructions'][$code] = $instructions;
                $config['payment']['tonder_ccform']['icons'] = $this->getIcons();
                $config['payment']['tonder_ccform']['form_configuration'] = $this->methods[$code]->getFormConfiguration();
            }
        }
        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->methods[$code]->getInstructions()));
    }

    public function getIcons()
    {
        if (!empty($this->icons)) {
            return $this->icons;
        }

        $types = [
            'AE' => "American Express",
            'VI' => "Visa",
            'MC' => "MasterCard"
        ];
        foreach ($types as $code => $label) {
            if (!array_key_exists($code, $this->icons)) {
                $asset = $this->ccConfig->createAsset('Tonder_Payment::images/cc/' . strtolower($code) . '.png');
                $placeholder = $this->assetSource->findSource($asset);
                if ($placeholder) {
                    list($width, $height) = getimagesize($asset->getSourceFile());
                    $this->icons[$code] = [
                        'url' => $asset->getUrl(),
                        'width' => $width,
                        'height' => $height,
                        'title' => __($label),
                    ];
                }
            }
        }

        return $this->icons;
    }
}