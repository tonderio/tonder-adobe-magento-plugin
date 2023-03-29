<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Tonder\Payment\Model\Adminhtml\Source\Environment;
use Magento\Payment\Model\Method\AbstractMethod;

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
    const KOUNT_INQUIRY = 'kount_inquiry';

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

    const CARD_VERIFICATION = 'card_verification';

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
    const AMOUNT = 'amount';

    /**
     * Cardholder Amount
     */

    const CARDHOLDER_AMOUNT = 'cardholder_amount';

    /**
     * Comp Amount
     */
    const COMP_AMOUNT = 'comp_amount';

    /**
     * Tonder CC Vault
     */
    const CC_VAULT_CODE = 'tonder_cc_vault';

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

    const CARD_LOOKUP = 'card_lookup';
    const CAVV_PURCHASE = 'cavv_purchase';
    const CAVV_PREAUTH = 'cavv_preauth';
    const CAVV_VAULT_PURCHASE = 'res_cavv_purchase_cc';
    const CAVV_VAULT_PREAUTH = 'res_cavv_preauth_cc';
    /**
     * Get Key
     */
    const GET_KEY = 'get_key';

    /**
     * MCP Version
     */
    const MCP_VERSION = 'mcp_version';

    /**
     * Cardholder Currency Code
     */
    const CARDHOLDER_CURRENCY_CODE = 'cardholder_currency_code';

    /**
     * Card Verification with Vault
     */
    const CARD_VERIFICATION_VAULT = 'res_card_verification_cc';


}
