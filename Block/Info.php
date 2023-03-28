<?php
namespace Tonder\Payment\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

/**
 * Class Info
 */
class Info extends ConfigurableInfo
{
    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getLabel($field)
    {
        switch ($field) {
            case 'cc_type':
                return __('Card Type');
            case 'reference_num':
                return __('Reference Num');
            case 'transaction_type':
                return __('Transaction Type');
            case 'transaction_id':
                return __('Transaction ID');
            case 'kount_transaction_id':
                return __('Kount Transaction ID');
            case 'card_number':
                return __('Card number');
            case 'card_expiry_date':
                return __('Expiration Date');
            case 'response_code':
                return __('Response Code');
            case 'approve_messages':
                return __('Approve Message');
            case 'kount_response_code':
                return __('Kount');
            case 'avs_response_code':
                return __('AVS');
            case 'cvd_response_code':
                return __('CVD');
            case 'auth_code':
                return __('Authentication Code');
            case 'mcp_purchase':
                return __('MCP Purchase');
            case 'threed_secure_response_code':
                return __('3D Secure');
            default:
                return parent::getLabel($field);
        }
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getValueView($field, $value)
    {
        switch ($field) {
            case 'transaction_type':
                return $this->formatMessages([$value]);
            case 'kount_response_code':
                return $this->formatKountMessage($value);
            case 'avs_response_code':
                return $this->formatAvsMessage($value);
            case 'cvd_response_code':
                return $this->formatCvdMessage($value);
            case 'threed_secure_response_code':
                return $this->formatThreeDSecure($value);
            default:
                return parent::getValueView($field, $value);
        }
    }

    /**
     * Formatting messages
     *
     * @param array $messagesCodes
     * @return string
     */
    private function formatMessages(array $messagesCodes)
    {
        $result = [];
        $messages = $this->getMessageCode();
        foreach ($messagesCodes as $code) {
            if (isset($messages[$code])) {
                $result[] = sprintf('%s - %s', $code, $messages[$code]);
            } elseif (is_string($code)) {
                $result[] = strtoupper($code);
            }
        }

        return implode(', ', $result);
    }

    /**
     * @param $code
     * @return string
     */
    private function formatKountMessage($code)
    {
        $kountMessageMapping = [
            'A' => 'Approve',
            'D' => 'Decline',
            'R' => 'Review',
        ];
        return isset($kountMessageMapping[$code]) ? $kountMessageMapping[$code] : 'Can not resolve response code';
    }

    /**
     * @param $code
     * @return string
     */
    private function formatAvsMessage($code)
    {
        $avsMessageMapping = [
            'A' => 'Address matches, ZIP does not. Acquirer rights not implied.',
            'B' => 'Street addresses match. Postal code not verified due to incompatible formats. (Acquirer sent both street address and postal code.)',
            'C' => 'Street addresses not verified due to incompatible formats. (Acquirer sent both street address and postal code.)',
            'D' => 'Street addresses and postal codes match.',
            'E' => 'Customer name incorrect, billing address and postal code match',
            'F' => 'Street address and postal code match. Applies to U.K. only',
            'G' => 'Address information not verified for international transaction. Issuer is not an AVS participant, or AVS data was present in the request but issuer did not return an AVS result, or Visa performs AVS on behalf of the issuer and there was no address record on file for this account.',
            'I' => 'Address information not verified.',
            'K' => 'Customer name matches',
            'L' => 'Customer name and postal code match.',
            'M' => 'Street address and postal code match.',
            'N' => 'Neither address nor postal code matches.',
            'O' => 'Customer name and billing address match',
            'P' => 'Postal code match. Acquirer sent both postal code and street address but street address not verified due to incompatible formats.',
            'R' => 'Retry: system unavailable or timed out. Issuer ordinarily performs AVS but was unavailable. The code R is used by Visa when issuers are unavailable. Issuers should refrain from using this code.',
            'S' => 'AVS currently not supported.',
            'T' => 'Nine-digit zip code matches, address does not match.',
            'U' => 'Address not verified for domestic transaction. Issuer is not an AVS participant, or AVS data was present in the request but issuer did not return an AVS result, or Visa performs AVS on behalf of the issuer and there was no address record on file for this account.',
            'W' => 'Not applicable. If present, replaced with ‘Z’ by Visa. Available for U.S. issuers only.',
            'X' => 'For U.S. addresses, nine-digit postal code and addresses matches; for addresses outside the U.S., postal code and address match.',
            'Y' => 'Street address and postal code match.',
            'Z' => 'Postal/Zip matches; street address does not match or street address not included in request.',
            'null' => 'AVS could not be verified'
        ];
        return isset($avsMessageMapping[$code]) ? $avsMessageMapping[$code] : 'Can not resolve response code';
    }

    /**
     * @param $code
     * @return string
     */
    private function formatCvdMessage($code)
    {
        $cvdMessageMapping = [
            '1M' => 'Match',
            '1N' => 'No Match',
            '1P' => 'Not Processed',
            '1S' => 'CVD should be on the card, but Merchant has indicated that CVD is not present.',
            '1U' => 'Issuer is not a CVD participant',
            '1Y' => 'Match for AmEx/JCB only',
            '1D' => 'Invalid security code for AmEx/JCB',
            'Other' => 'Invalid response code',
            'null' => 'AVS could not be verified'
        ];
        return isset($cvdMessageMapping[$code]) ? $cvdMessageMapping[$code] : 'Can not resolve response code';
    }

    /**
     * @param $code
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function formatThreeDSecure($code)
    {
        if ($code == 'none') {
            return 'non-authenticated';
        }
        $card_type = $this->getInfo()->getAdditionalInformation('cc_type');
        $visaMapping = [
            '0' => 'CAVV authentication results invalid',
            '1' => 'CAVV failed validation (authentication)',
            '2' => 'CAVV passed validation (authentication)',
            '3' => 'CAVV passed validation (attempt)',
            '8' => 'CAVV passed validation (attempt)',
            'A' => 'CAVV passed validation (attempt)',
            '4' => 'CAVV failed validation (attempt)',
            '7' => 'CAVV failed validation (attempt)',
            '9' => 'CAVV failed validation (attempt)',
            '6' => 'CAVV not validated - Issuer not participating',
            'B' => 'CAVV passed validation; info only',
            'C' => 'CAVV was not validated (attempt)',
            'D' => 'CAVV was not validated (authentication)',
        ];
        $masterMapping =  [
            '0' => 'Authentication failed',
            '1' => 'Authentication attempted',
            '2' => 'Authentication successful',
        ];
        $ameExMapping = [
            '1' => 'AEVV Failed - Authentication, Issuer Key',
            '2' => 'AEVV Passed - Authentication, Issuer Key',
            '3' => 'AEVV Passed - Attempt, Issuer Key',
            '4' => 'AEVV Failed - Attempt, Issuer Key',
            '7' => 'AEVV Failed - Attempt, Issuer not participating, Network Key',
            '8' => 'AEVV Passed - Attempt, Issuer not participating, Network Key',
            '9' => 'AEVV Failed - Attempt, Participating, Access Control Server (ACS) not available, Network Key',
            'A' => 'AEVV Passed - Attempt, Participating, Access Control Server (ACS) not available, Network Key',
            'U' => 'AEVV Unchecked',
        ];

        switch ($card_type) {
            case 'V':
                return $visaMapping[$code] ?? 'Can not resolve response code';
            case 'M':
                return $masterMapping[$code] ??'Can not resolve response code';
            case "AX":
                return $ameExMapping[$code] ?? 'Can not resolve response code';
            default:
                return 'non-authenticated';
        }
    }
    /**
     * Getting codes of messages and the text representation
     *
     * @return array
     */
    private function getMessageCode()
    {
        return [
            '00' => 'Purchase',
            '01' => 'Pre-Authorization',
            '02' => 'Pre-Authorization Completion',
            '04' => 'Refund',
            '11' => 'Purchase Correction',
            '50' => 'Decline',
            '51' => 'Expired Card',
            '52' => 'PIN retries exceeded',
            '53' => 'No sharing',
            '54' => 'No security module',
            '55' => 'Invalid transaction',
            '56' => 'No Support',
            '57' => 'Lost or stolen card',
            '58' => 'Invalid status',
            '59' => 'Restricted Card',
            '60' => 'No Chequing account',
            '61' => 'No PBF',
            '62' => 'PBF update error',
            '63' => 'Invalid authorization type',
            '64' => 'Bad Track 2',
            '65' => 'Adjustment not allowed',
            '66' => 'Invalid credit card advance increment',
            '67' => 'Invalid transaction date',
            '68' => 'PTLF error',
            '69' => 'Bad message error',
            '70' => 'No IDF',
            '71' => 'Invalid route authorization',
            '72' => 'Card on National NEG file ',
            '73' => 'Invalid route service (destination)',
            '74' => 'Unable to authorize',
            '75' => 'Invalid PAN length',
            '76' => 'Low funds',
            '77' => 'Pre-auth full',
            '78' => 'Duplicate transaction',
            '79' => 'Maximum online refund reached',
            '80' => 'Maximum offline refund reached',
            '81' => 'Maximum credit per refund reached',
            '82' => 'Number of times used exceeded',
            '83' => 'Maximum refund credit reached',
            '84' => 'Duplicate transaction - authorization number has already been corrected by host. ',
            '85' => 'Inquiry not allowed',
            '86' => 'Over floor limit ',
            '87' => 'Maximum number of refund credit by retailer',
            '88' => 'Place call',
            '89' => 'CAF status inactive or closed',
            '90' => 'Referral file full',
            '91' => 'NEG file problem',
            '92' => 'Advance less than minimum',
            '93' => 'Delinquent',
            '94' => 'Over table limit',
            '95' => 'Amount over maximum',
            '96' => 'PIN required',
            '97' => 'Mod 10 check failure',
            '98' => 'Force Post',
            '99' => 'Bad PBF',
            '100' => 'Unable to process transaction',
            '101' => 'Place call',
            '102' => 'Place call',
            '103' => 'NEG file problem',
            '104' => 'CAF problem',
            '105' => 'Card not supported',
            '106' => 'Amount over maximum',
            '107' => 'Over daily limit',
            '108' => 'CAF Problem',
            '109' => 'Advance less than minimum',
            '110' => 'Number of times used exceeded',
            '111' => 'Delinquent',
            '112' => 'Over table limit',
            '113' => 'Timeout',
            '115' => 'PTLF error',
            '121' => 'Administration file problem',
            '122' => 'Unable to validate PIN: security module down',
            '150' => 'Merchant not on file',
            '200' => 'Invalid account',
            '201' => 'Incorrect PIN',
            '202' => 'Advance less than minimum',
            '203' => 'Administrative card needed',
            '204' => 'Amount over maximum ',
            '205' => 'Invalid Advance amount',
            '206' => 'CAF not found',
            '207' => 'Invalid transaction date',
            '208' => 'Invalid expiration date',
            '209' => 'Invalid transaction code',
            '210' => 'PIN key sync error',
            '212' => 'Destination not available',
            '251' => 'Error on cash amount',
            '252' => 'Debit not supported',
            '426' => 'AMEX - Denial 12',
            '427' => 'AMEX - Invalid merchant',
            '429' => 'AMEX - Account error',
            '430' => 'AMEX - Expired card',
            '431' => 'AMEX - Call Amex',
            '434' => 'AMEX - Call 03',
            '435' => 'AMEX - System down',
            '436' => 'AMEX - Call 05',
            '437' => 'AMEX - Declined',
            '438' => 'AMEX - Declined',
            '439' => 'AMEX - Service error',
            '440' => 'AMEX - Call Amex',
            '441' => 'AMEX - Amount error',
            '475' => 'CREDIT CARD - Invalid expiration date',
            '476' => 'CREDIT CARD - Invalid transaction, rejected',
            '477' => 'CREDIT CARD - Refer Call',
            '478' => 'CREDIT CARD - Decline, Pick up card, Call',
            '479' => 'CREDIT CARD - Decline, Pick up card',
            '480' => 'CREDIT CARD - Decline, Pick up card',
            '481' => 'CREDIT CARD - Decline',
            '482' => 'CREDIT CARD - Expired Card',
            '483' => 'CREDIT CARD - Refer',
            '484' => 'CREDIT CARD - Expired card - refer',
            '485' => 'CREDIT CARD - Not authorized',
            '486' => 'CREDIT CARD - CVV Cryptographic error',
            '487' => 'CREDIT CARD - Invalid CVV',
            '489' => 'CREDIT CARD - Invalid CVV',
            '490' => 'CREDIT CARD - Invalid CVV',
            '800' => 'Bad format',
            '801' => 'Bad data',
            '802' => 'Invalid Clerk ID',
            '809' => 'Bad close ',
            '810' => 'System timeout',
            '811' => 'System error',
            '821' => 'Bad response length',
            '877' => 'Invalid PIN block',
            '878' => 'PIN length error',
            '880' => 'Final packet of a multi-packet transaction',
            '881' => 'Intermediate packet of a multi-packet transaction',
            '889' => 'MAC key sync error',
            '898' => 'Bad MAC value',
            '899' => 'Bad sequence number - resend transaction',
            '900' => 'Capture - PIN Tries Exceeded',
            '901' => 'Capture - Expired Card',
            '902' => 'Capture - NEG Capture',
            '903' => 'Capture - CAF Status 3',
            '904' => 'Capture - Advance < Minimum',
            '905' => 'Capture - Num Times Used',
            '906' => 'Capture - Delinquent',
            '907' => 'Capture - Over Limit Table',
            '908' => 'Capture - Amount Over Maximum',
            '909' => 'Capture - Capture',
            '960' => 'Initialization failure - merchant number mismatch',
            '961' => 'Initialization failure - pinpad  mismatch',
            '963' => 'No match on Poll code',
            '964' => 'No match on Concentrator ID',
            '965' => 'Invalid software version',
            '966' => 'Duplicate terminal name',
            '973' => 'Unable to locate merchant kount details',
            '984' => 'Data error',
            '987' => 'Invalid transaction'
        ];
    }
}
