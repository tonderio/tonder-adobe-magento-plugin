<?php

namespace Tonder\Payment\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\ResourceConnection;

class UpdateEnvironment implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * UpdateEnvironment constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return UpdateEnvironment|void
     */
    public function apply()
    {
        $table = $this->resourceConnection->getTableName('core_config_data');
        $connection = $this->resourceConnection->getConnection();
        $where = "path = 'payment/tonder/environment' AND value = 'US'";
        $usEnvironment = $connection->select()->from($table, 'value')->where($where);
        if ($connection->fetchCol($usEnvironment)) {
            $connection->update($table, ['value' => 'CA'], $where);
        }
    }
}
