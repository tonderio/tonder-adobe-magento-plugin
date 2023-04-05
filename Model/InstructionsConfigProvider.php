<?php

namespace Tonder\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Helper\Data as PaymentHelper;
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

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @param Repository $assetRepo
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Repository $assetRepo
    ) {
        $this->escaper = $escaper;
        $this->assetRepo = $assetRepo;
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
}