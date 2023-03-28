<?php
namespace Tonder\Payment\Model\Config\Backend;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ThreeDS extends \Magento\Framework\App\Config\Value
{

    /**
     * @var Config
     */
    protected $_resourceConfig;

    /**
     * ThreeDS constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param Config $resourceConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        Config $resourceConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_resourceConfig = $resourceConfig;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return ThreeDS
     */
    public function afterSave()
    {
        if ($this->getFieldsetDataValue('multi_currency') == '1') {
            $this->_resourceConfig->saveConfig(
                'payment/moneris/three_d_secure',
                0,
                $this->getData('scope'),
                $this->getData('scope_id')
            );
        }

        return parent::afterSave();
    }
}
