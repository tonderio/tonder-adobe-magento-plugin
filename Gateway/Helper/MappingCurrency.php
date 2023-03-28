<?php

namespace Tonder\Payment\Gateway\Helper;

class MappingCurrency
{
    const CURRENCIES_ARRAY = [
        'AFN' => [
            'currency_code_number' => '971',
            'rate' => '0.016527'
        ],

        'EUR' => [
            'currency_code_number' => '978',
            'rate' => '1.5114'
        ],

        'ALL' => [
            'currency_code_number' => '008',
            'rate' => '0.012122'
        ],

        'DZD' => [
            'currency_code_number' => '012',
            'rate' => '0.009936'
        ],

        'USD' => [
            'currency_code_number' => '840',
            'rate' => '1.2764'
        ],

        'AOA' => [
            'currency_code_number' => '973',
            'rate' => '0.002192'
        ],

        'XCD' => [
            'currency_code_number' => '951',
            'rate' => '0.4692'
        ],

        'ARS' => [
            'currency_code_number' => '032',
            'rate' => '0.017367'
        ],

        'AMD' => [
            'currency_code_number' => '051',
            'rate' => ''
        ],

        'AWG' => [
            'currency_code_number' => '533',
            'rate' => '0.693709'
        ],

        'AUD' => [
            'currency_code_number' => '036',
            'rate' => '0.9155'
        ],

        'AZN' => [
            'currency_code_number' => '944',
            'rate' => '0.748636'
        ],

        'BSD' => [
            'currency_code_number' => '044',
            'rate' => ''
        ],

        'BHD' => [
            'currency_code_number' => '048',
            'rate' => '3.385653'
        ],

        'BDT' => [
            'currency_code_number' => '050',
            'rate' => '0.015052'
        ],

        'BBD' => [
            'currency_code_number' => '052',
            'rate' => '0.6256'
        ],

        'BYN' => [
            'currency_code_number' => '933',
            'rate' => '0.514853'
        ],

        'BZD' => [
            'currency_code_number' => '084',
            'rate' => '0.6277'
        ],

        'XOF' => [
            'currency_code_number' => '952',
            'rate' => '0.002307'
        ],

        'BMD' => [
            'currency_code_number' => '060',
            'rate' => '1.2764'
        ],

        'INR' => [
            'currency_code_number' => '356',
            'rate' => '0.017016'
        ],

        'BTN' => [
            'currency_code_number' => '064',
            'rate' => '0.017058'
        ],

        'BOB' => [
            'currency_code_number' => '068',
            'rate' => '0.182868'
        ],

        'BOV' => [
            'currency_code_number' => '984',
            'rate' => ''
        ],

        'BAM' => [
            'currency_code_number' => '977',
            'rate' => '0.761499'
        ],

        'BWP' => [
            'currency_code_number' => '072',
            'rate' => ''
        ],

        'NOK' => [
            'currency_code_number' => '578',
            'rate' => '0.1429'
        ],

        'BRL' => [
            'currency_code_number' => '986',
            'rate' => '0.2296'
        ],

        'BND' => [
            'currency_code_number' => '096',
            'rate' => '0.931493'
        ],

        'BGN' => [
            'currency_code_number' => '975',
            'rate' => '0.772701'
        ],

        'BIF' => [
            'currency_code_number' => '108',
            'rate' => '0.000654'
        ],

        'CVE' => [
            'currency_code_number' => '132',
            'rate' => '67.934188'
        ],

        'KHR' => [
            'currency_code_number' => '116',
            'rate' => ''
        ],

        'XAF' => [
            'currency_code_number' => '950',
            'rate' => ''
        ],

        'KYD' => [
            'currency_code_number' => '136',
            'rate' => '1.5286'
        ],

        'CLP' => [
            'currency_code_number' => '152',
            'rate' => '0.001626'
        ],

        'CLF' => [
            'currency_code_number' => '990',
            'rate' => ''
        ],

        'CNY' => [
            'currency_code_number' => '156',
            'rate' => '0.184326'
        ],

        'COP' => [
            'currency_code_number' => '170',
            'rate' => '0.000339'
        ],

        'COU' => [
            'currency_code_number' => '970',
            'rate' => ''
        ],

        'KMF' => [
            'currency_code_number' => '174',
            'rate' => '0.00303'
        ],

        'CDF' => [
            'currency_code_number' => '976',
            'rate' => ''
        ],

        'NZD' => [
            'currency_code_number' => '554',
            'rate' => '0.837'
        ],

        'CRC' => [
            'currency_code_number' => '188',
            'rate' => '0.002136'
        ],

        'HRK' => [
            'currency_code_number' => '191',
            'rate' => '0.200588'
        ],

        'CUP' => [
            'currency_code_number' => '192',
            'rate' => '1.276425'
        ],

        'CUC' => [
            'currency_code_number' => '931',
            'rate' => ''
        ],

        'ANG' => [
            'currency_code_number' => '532',
            'rate' => '0.701332'
        ],

        'CZK' => [
            'currency_code_number' => '203',
            'rate' => '0.05788'
        ],

        'DKK' => [
            'currency_code_number' => '208',
            'rate' => '0.203'
        ],

        'DJF' => [
            'currency_code_number' => '262',
            'rate' => '0.007148'
        ],

        'DOP' => [
            'currency_code_number' => '214',
            'rate' => '0.021808'
        ],

        'EGP' => [
            'currency_code_number' => '818',
            'rate' => '0.0798'
        ],

        'SVC' => [
            'currency_code_number' => '222',
            'rate' => '0.145718'
        ],

        'ERN' => [
            'currency_code_number' => '232',
            'rate' => ''
        ],

        'SZL' => [
            'currency_code_number' => '748',
            'rate' => '0.073875'
        ],

        'ETB' => [
            'currency_code_number' => '230',
            'rate' => ''
        ],

        'FKP' => [
            'currency_code_number' => '238',
            'rate' => ''
        ],

        'FJD' => [
            'currency_code_number' => '242',
            'rate' => '0.5876'
        ],

        'XPF' => [
            'currency_code_number' => '953',
            'rate' => '0.012636'
        ],

        'GMD' => [
            'currency_code_number' => '270',
            'rate' => '0.024083'
        ],

        'GEL' => [
            'currency_code_number' => '981',
            'rate' => '0.414693'
        ],

        'GHS' => [
            'currency_code_number' => '936',
            'rate' => ''
        ],

        'GIP' => [
            'currency_code_number' => '292',
            'rate' => '0.963485'
        ],

        'GTQ' => [
            'currency_code_number' => '320',
            'rate' => '0.165554'
        ],

        'GBP' => [
            'currency_code_number' => '826',
            'rate' => '1.6708'
        ],

        'GNF' => [
            'currency_code_number' => '324',
            'rate' => '0.000131'
        ],

        'GYD' => [
            'currency_code_number' => '328',
            'rate' => '0.006056'
        ],

        'HTG' => [
            'currency_code_number' => '332',
            'rate' => '0.011244'
        ],

        'HNL' => [
            'currency_code_number' => '340',
            'rate' => '0.051365'
        ],

        'HKD' => [
            'currency_code_number' => '344',
            'rate' => '0.1646'
        ],

        'HUF' => [
            'currency_code_number' => '348',
            'rate' => '0.004316'
        ],

        'ISK' => [
            'currency_code_number' => '352',
            'rate' => '0.009325'
        ],

        'IDR' => [
            'currency_code_number' => '360',
            'rate' => '0.000086'
        ],

        'XDR' => [
            'currency_code_number' => '960',
            'rate' => ''
        ],

        'IRR' => [
            'currency_code_number' => '364',
            'rate' => ''
        ],

        'IQD' => [
            'currency_code_number' => '368',
            'rate' => ''
        ],

        'ILS' => [
            'currency_code_number' => '376',
            'rate' => '0.3749'
        ],

        'JMD' => [
            'currency_code_number' => '388',
            'rate' => '0.008436'
        ],

        'JPY' => [
            'currency_code_number' => '392',
            'rate' => '0.012033'
        ],

        'JOD' => [
            'currency_code_number' => '400',
            'rate' => '1.797781'
        ],

        'KZT' => [
            'currency_code_number' => '398',
            'rate' => '0.003047'
        ],

        'KES' => [
            'currency_code_number' => '404',
            'rate' => '0.011742'
        ],

        'KPW' => [
            'currency_code_number' => '408',
            'rate' => ''
        ],

        'KRW' => [
            'currency_code_number' => '410',
            'rate' => '0.001074'
        ],

        'KWD' => [
            'currency_code_number' => '414',
            'rate' => '4.174052'
        ],

        'KGS' => [
            'currency_code_number' => '417',
            'rate' => ''
        ],

        'LAK' => [
            'currency_code_number' => '418',
            'rate' => '0.00014'
        ],

        'LBP' => [
            'currency_code_number' => '422',
            'rate' => ''
        ],

        'LSL' => [
            'currency_code_number' => '426',
            'rate' => '0.073845'
        ],

        'ZAR' => [
            'currency_code_number' => '710',
            'rate' => '0.0738'
        ],

        'LRD' => [
            'currency_code_number' => '430',
            'rate' => '0.006317'
        ],

        'LYD' => [
            'currency_code_number' => '434',
            'rate' => ''
        ],

        'CHF' => [
            'currency_code_number' => '756',
            'rate' => '1.3948'
        ],

        'MOP' => [
            'currency_code_number' => '446',
            'rate' => '0.159752'
        ],

        'MKD' => [
            'currency_code_number' => '807',
            'rate' => '0.02457'
        ],

        'MGA' => [
            'currency_code_number' => '969',
            'rate' => '0.000327'
        ],

        'MWK' => [
            'currency_code_number' => '454',
            'rate' => '0.001687'
        ],

        'MYR' => [
            'currency_code_number' => '458',
            'rate' => '0.30573'
        ],

        'MVR' => [
            'currency_code_number' => '462',
            'rate' => '0.08235'
        ],

        'MRU' => [
            'currency_code_number' => '929',
            'rate' => '0.032728'
        ],

        'MUR' => [
            'currency_code_number' => '480',
            'rate' => '0.03199'
        ],

        'XUA' => [
            'currency_code_number' => '965',
            'rate' => ''
        ],

        'MXN' => [
            'currency_code_number' => '484',
            'rate' => '0.05754'
        ],

        'MXV' => [
            'currency_code_number' => '979',
            'rate' => ''
        ],

        'MDL' => [
            'currency_code_number' => '498',
            'rate' => '0.0764'
        ],

        'MNT' => [
            'currency_code_number' => '496',
            'rate' => ''
        ],

        'MAD' => [
            'currency_code_number' => '504',
            'rate' => '0.138445'
        ],

        'MZN' => [
            'currency_code_number' => '943',
            'rate' => '0.01775'
        ],

        'MMK' => [
            'currency_code_number' => '104',
            'rate' => ''
        ],

        'NAD' => [
            'currency_code_number' => '516',
            'rate' => '0.07388'
        ],

        'NPR' => [
            'currency_code_number' => '524',
            'rate' => '0.010635'
        ],

        'NIO' => [
            'currency_code_number' => '558',
            'rate' => '0.036231'
        ],

        'NGN' => [
            'currency_code_number' => '566',
            'rate' => '0.003303'
        ],

        'OMR' => [
            'currency_code_number' => '512',
            'rate' => '3.315045'
        ],

        'PKR' => [
            'currency_code_number' => '586',
            'rate' => '0.00755'
        ],

        'PAB' => [
            'currency_code_number' => '590',
            'rate' => ''
        ],

        'PGK' => [
            'currency_code_number' => '598',
            'rate' => '0.35676'
        ],

        'PYG' => [
            'currency_code_number' => '600',
            'rate' => '0.000183'
        ],

        'PEN' => [
            'currency_code_number' => '604',
            'rate' => '0.358103'
        ],

        'PHP' => [
            'currency_code_number' => '608',
            'rate' => '0.026234'
        ],

        'PLN' => [
            'currency_code_number' => '985',
            'rate' => '0.343861'
        ],

        'QAR' => [
            'currency_code_number' => '634',
            'rate' => '0.350611'
        ],

        'RON' => [
            'currency_code_number' => '946',
            'rate' => '0.312303'
        ],

        'RUB' => [
            'currency_code_number' => '643',
            'rate' => '0.017411'
        ],

        'RWF' => [
            'currency_code_number' => '646',
            'rate' => '0.001316'
        ],

        'WST' => [
            'currency_code_number' => '882',
            'rate' => '0.49291'
        ],

        'STN' => [
            'currency_code_number' => '930',
            'rate' => ''
        ],

        'SAR' => [
            'currency_code_number' => '682',
            'rate' => '0.340505'
        ],

        'RSD' => [
            'currency_code_number' => '941',
            'rate' => '0.012848'
        ],

        'SCR' => [
            'currency_code_number' => '690',
            'rate' => '0.071346'
        ],

        'SLL' => [
            'currency_code_number' => '694',
            'rate' => '0.00013'
        ],

        'SGD' => [
            'currency_code_number' => '702',
            'rate' => '0.93185'
        ],

        'XSU' => [
            'currency_code_number' => '994',
            'rate' => ''
        ],

        'SBD' => [
            'currency_code_number' => '090',
            'rate' => '0.153252'
        ],

        'SOS' => [
            'currency_code_number' => '706',
            'rate' => ''
        ],

        'SSP' => [
            'currency_code_number' => '728',
            'rate' => ''
        ],

        'LKR' => [
            'currency_code_number' => '144',
            'rate' => '0.006858'
        ],

        'SDG' => [
            'currency_code_number' => '938',
            'rate' => ''
        ],

        'SRD' => [
            'currency_code_number' => '968',
            'rate' => '0.169827'
        ],

        'SEK' => [
            'currency_code_number' => '752',
            'rate' => '0.146638'
        ],

        'CHE' => [
            'currency_code_number' => '947',
            'rate' => ''
        ],

        'CHW' => [
            'currency_code_number' => '948',
            'rate' => ''
        ],

        'SYP' => [
            'currency_code_number' => '760',
            'rate' => ''
        ],

        'TWD' => [
            'currency_code_number' => '901',
            'rate' => '0.043418'
        ],

        'TJS' => [
            'currency_code_number' => '972',
            'rate' => '0.12375'
        ],

        'TZS' => [
            'currency_code_number' => '834',
            'rate' => '0.000549'
        ],

        'THB' => [
            'currency_code_number' => '764',
            'rate' => '0.040736'
        ],

        'TOP' => [
            'currency_code_number' => '776',
            'rate' => ''
        ],

        'TTD' => [
            'currency_code_number' => '780',
            'rate' => '0.187093'
        ],

        'TND' => [
            'currency_code_number' => '788',
            'rate' => '0.467682'
        ],

        'TRY' => [
            'currency_code_number' => '949',
            'rate' => '0.174603'
        ],

        'TMT' => [
            'currency_code_number' => '934',
            'rate' => '0.364886'
        ],

        'UGX' => [
            'currency_code_number' => '800',
            'rate' => '0.000346'
        ],

        'UAH' => [
            'currency_code_number' => '980',
            'rate' => ''
        ],

        'AED' => [
            'currency_code_number' => '784',
            'rate' => '0.347652'
        ],

        'USN' => [
            'currency_code_number' => '997',
            'rate' => ''
        ],

        'UYU' => [
            'currency_code_number' => '858',
            'rate' => '0.02979'
        ],

        'UYI' => [
            'currency_code_number' => '940',
            'rate' => ''
        ],

        'UYW' => [
            'currency_code_number' => '927',
            'rate' => ''
        ],

        'UZS' => [
            'currency_code_number' => '860',
            'rate' => '0.000124'
        ],

        'VUV' => [
            'currency_code_number' => '548',
            'rate' => '0.011199'
        ],

        'VES' => [
            'currency_code_number' => '928',
            'rate' => ''
        ],

        'VND' => [
            'currency_code_number' => '704',
            'rate' => '0.000055'
        ],

        'YER' => [
            'currency_code_number' => '886',
            'rate' => ''
        ],

        'ZMW' => [
            'currency_code_number' => '967',
            'rate' => '0.067571'
        ],

        'ZWL' => [
            'currency_code_number' => '932',
            'rate' => ''
        ],

        'XBA' => [
            'currency_code_number' => '955',
            'rate' => ''
        ],

        'XBB' => [
            'currency_code_number' => '956',
            'rate' => ''
        ],

        'XBC' => [
            'currency_code_number' => '957',
            'rate' => ''
        ],

        'XBD' => [
            'currency_code_number' => '958',
            'rate' => ''
        ],

        'XTS' => [
            'currency_code_number' => '963',
            'rate' => ''
        ],

        'XXX' => [
            'currency_code_number' => '999',
            'rate' => ''
        ],

        'XAU' => [
            'currency_code_number' => '959',
            'rate' => ''
        ],

        'XPD' => [
            'currency_code_number' => '964',
            'rate' => ''
        ],

        'XPT' => [
            'currency_code_number' => '962',
            'rate' => ''
        ],

        'XAG' => [
            'currency_code_number' => '961',
            'rate' => ''
        ],

        'CAD' => [
            'currency_code_number' => '124',
            'rate' => ''
        ]
    ];

    /**
     * @param $code
     * @return array|string[]
     */
    public function getCurrencyData($code)
    {
        $currencyData = [];
        if (array_key_exists($code, self::CURRENCIES_ARRAY)) {
            $currencyData = self::CURRENCIES_ARRAY[$code];
        }
        return $currencyData;
    }
}
