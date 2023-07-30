<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Tonder\Payment\Model\Adminhtml\Source\Environment;
use Tonder\Payment\Helper\Data;

/**
 * Class AbstractDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
abstract class AbstractDataBuilder implements BuilderInterface
{
    /**@#+
     * List Transaction Type
     * @const
     */

    const PRE_AUTH_CAPTURE = 'completion';

    const MCP_PRE_AUTH_CAPTURE = 'mcp_completion';

    /**
     * Capture
     */
    const PURCHASE = 'purchase';

    const MCP_PURCHASE = 'mcp_purchase';

    /**
     * Transaction type: Void
     */
    const VOID = 'purchasecorrection';

    /**
    /**
     * Transaction type: Authorize
     */
    const AUTHORIZE = 'preauth';

    const MCP_AUTHORIZE = 'mcp_preauth';


    /**
     * Transaction Type: Refund
     */
    const REFUND = 'refund';

    const MCP_REFUND = 'mcp_refund';

    /**
     * Need Replace it in TransferFactory
     */
    const REPLACE_KEY = 'replace_key';

    /**
     * Amount
     */
    const AMOUNT = "amount";

    /**
     * Cardholder Amount
     */


    /**
     * Vault Capture
     */
    const VAULT_CAPTURE = 'res_purchase_cc';

    const MCP_VAULT_CAPTURE = 'mcp_res_purchase_cc';

    /**
     * Vault Authorize
     */
    const VAULT_AUTHORIZE = 'res_preauth_cc';

    const MCP_VAULT_AUTHORIZE = 'mcp_res_preauth_cc';

}
