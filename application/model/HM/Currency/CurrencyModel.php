<?php
class HM_Currency_CurrencyModel
{
    private static  $_currencyList = array( 'AED' => 'UAE Dirham',
                                            'AFN' => 'Afghani',
                                            'ALL' => 'Lek',
                                            'AMD' => 'Armenian Dram',
                                            'ANG' => 'Netherlands Antillean Guilder',
                                            'AOA' => 'Kwanza',
                                            'ARS' => 'Argentine Peso',
                                            'AUD' => 'Australian Dollar',
                                            'AWG' => 'Aruban Florin',
                                            'AZN' => 'Azerbaijanian Manat',
                                            'BAM' => 'Convertible Mark',
                                            'BBD' => 'Barbados Dollar',
                                            'BDT' => 'Taka',
                                            'BGN' => 'Bulgarian Lev',
                                            'BHD' => 'Bahraini Dinar',
                                            'BIF' => 'Burundi Franc',
                                            'BMD' => 'Bermudian Dollar',
                                            'BND' => 'Brunei Dollar',
                                            'BOB' => 'Boliviano',
                                            'BOV' => 'Mvdol',
                                            'BRL' => 'Brazilian Real',
                                            'BSD' => 'Bahamian Dollar',
                                            'BTN' => 'Ngultrum',
                                            'BWP' => 'Pula',
                                            'BYR' => 'Belarussian Ruble',
                                            'BZD' => 'Belize Dollar',
                                            'CAD' => 'Canadian Dollar',
                                            'CDF' => 'Congolese Franc',
                                            'CHE' => 'WIR Euro',
                                            'CHF' => 'Swiss Franc',
                                            'CHW' => 'WIR Franc',
                                            'CLF' => 'Unidades de fomento',
                                            'CLP' => 'Chilean Peso',
                                            'CNY' => 'Yuan Renminbi',
                                            'COP' => 'Colombian Peso',
                                            'COU' => 'Unidad de Valor Real',
                                            'CRC' => 'Costa Rican Colon',
                                            'CUC' => 'Peso Convertible',
                                            'CUP' => 'Cuban Peso',
                                            'CVE' => 'Cape Verde Escudo',
                                            'CZK' => 'Czech Koruna',
                                            'DJF' => 'Djibouti Franc',
                                            'DKK' => 'Danish Krone',
                                            'DOP' => 'Dominican Peso',
                                            'DZD' => 'Algerian Dinar',
                                            'EGP' => 'Egyptian Pound',
                                            'ERN' => 'Nakfa',
                                            'ETB' => 'Ethiopian Birr',
                                            'EUR' => 'Euro',
                                            'FJD' => 'Fiji Dollar',
                                            'FKP' => 'Falkland Islands Pound',
                                            'GBP' => 'Pound Sterling',
                                            'GEL' => 'Lari',
                                            'GHS' => 'Cedi',
                                            'GIP' => 'Gibraltar Pound',
                                            'GMD' => 'Dalasi',
                                            'GNF' => 'Guinea Franc',
                                            'GTQ' => 'Quetzal',
                                            'GYD' => 'Guyana Dollar',
                                            'HKD' => 'Hong Kong Dollar',
                                            'HNL' => 'Lempira',
                                            'HRK' => 'Croatian Kuna',
                                            'HTG' => 'Gourde',
                                            'HUF' => 'Forint',
                                            'IDR' => 'Rupiah',
                                            'ILS' => 'New Israeli Sheqel',
                                            'INR' => 'Indian Rupee',
                                            'IQD' => 'Iraqi Dinar',
                                            'IRR' => 'Iranian Rial',
                                            'ISK' => 'Iceland Krona',
                                            'JMD' => 'Jamaican Dollar',
                                            'JPY' => 'Yen',
                                            'KES' => 'Kenyan Shilling',
                                            'KGS' => 'Som',
                                            'KHR' => 'Riel',
                                            'KMF' => 'Comoro Franc',
                                            'KPW' => 'North Korean Won',
                                            'KRW' => 'Won',
                                            'KWD' => 'Kuwaiti Dinar',
                                            'KYD' => 'Cayman Islands Dollar',
                                            'KZT' => 'Tenge',
                                            'LAK' => 'Kip',
                                            'LBP' => 'Lebanese Pound',
                                            'LKR' => 'Sri Lanka Rupee',
                                            'LRD' => 'Liberian Dollar',
                                            'LSL' => 'Loti',
                                            'LTL' => 'Lithuanian Litas',
                                            'LVL' => 'Latvian Lats',
                                            'LYD' => 'Libyan Dinar',
                                            'MAD' => 'Moroccan Dirham',
                                            'MDL' => 'Moldovan Leu',
                                            'MGA' => 'Malagasy Ariary',
                                            'MKD' => 'Denar',
                                            'MMK' => 'Kyat',
                                            'MNT' => 'Tugrik',
                                            'MOP' => 'Pataca',
                                            'MRO' => 'Ouguiya',
                                            'MUR' => 'Mauritius Rupee',
                                            'MVR' => 'Rufiyaa',
                                            'MWK' => 'Kwacha',
                                            'MXN' => 'Mexican Peso',
                                            'MXV' => 'Mexican Unidad de Inversion (UDI)',
                                            'MYR' => 'Malaysian Ringgit',
                                            'MZN' => 'Metical',
                                            'NAD' => 'Namibia Dollar',
                                            'NGN' => 'Naira',
                                            'NIO' => 'Cordoba Oro',
                                            'NOK' => 'Norwegian Krone',
                                            'NPR' => 'Nepalese Rupee',
                                            'NZD' => 'New Zealand Dollar',
                                            'OMR' => 'Rial Omani',
                                            'PAB' => 'Balboa',
                                            'PEN' => 'Nuevo Sol',
                                            'PGK' => 'Kina',
                                            'PHP' => 'Philippine Peso',
                                            'PKR' => 'Pakistan Rupee',
                                            'PLN' => 'Zloty',
                                            'PYG' => 'Guarani',
                                            'QAR' => 'Qatari Rial',
                                            'RON' => 'Leu',
                                            'RSD' => 'Serbian Dinar',
                                            'RUB' => 'Russian Ruble',
                                            'RWF' => 'Rwanda Franc',
                                            'SAR' => 'Saudi Riyal',
                                            'SBD' => 'Solomon Islands Dollar',
                                            'SCR' => 'Seychelles Rupee',
                                            'SDG' => 'Sudanese Pound',
                                            'SEK' => 'Swedish Krona',
                                            'SGD' => 'Singapore Dollar',
                                            'SHP' => 'Saint Helena Pound',
                                            'SLL' => 'Leone',
                                            'SOS' => 'Somali Shilling',
                                            'SRD' => 'Surinam Dollar',
                                            'SSP' => 'South Sudanese Pound',
                                            'STD' => 'Dobra',
                                            'SVC' => 'El Salvador Colon',
                                            'SYP' => 'Syrian Pound',
                                            'SZL' => 'Lilangeni',
                                            'THB' => 'Baht',
                                            'TJS' => 'Somoni',
                                            'TMT' => 'New Manat',
                                            'TND' => 'Tunisian Dinar',
                                            'TOP' => 'Pa’anga',
                                            'TRY' => 'Turkish Lira',
                                            'TTD' => 'Trinidad and Tobago Dollar',
                                            'TWD' => 'New Taiwan Dollar',
                                            'TZS' => 'Tanzanian Shilling',
                                            'UAH' => 'Hryvnia',
                                            'UGX' => 'Uganda Shilling',
                                            'USD' => 'US Dollar',
                                            'UYI' => 'Uruguay Peso en Unidades Indexadas (URUIURUI)',
                                            'UYU' => 'Peso Uruguayo',
                                            'UZS' => 'Uzbekistan Sum',
                                            'VEF' => 'Bolivar Fuerte',
                                            'VND' => 'Dong',
                                            'VUV' => 'Vatu',
                                            'WST' => 'Tala',
                                            'XAF' => 'CFA Franc BEAC',
                                            'XCD' => 'East Caribbean Dollar',
                                            'XDR' => 'SDR (Special Drawing Right)',
                                            'XOF' => 'CFA Franc BCEAO',
                                            'XPF' => 'CFP Franc',
                                            'XSU' => 'Sucre',
                                            'XUA' => 'ADB Unit of Account',
                                            'YER' => 'Yemeni Rial',
                                            'ZAR' => 'Rand',
                                            'ZMK' => 'Zambian Kwacha',
                                            'ZWL' => 'Zimbabwe Dollar');
    
    /**
     * Возвращает массив валют
     * @return multitype 
     */
    public static function getList()
    {
        return self::$_currencyList;
    }
    
    /**
     * Возвращает массив валют, 
     * где в наименовании каждой валюты присутствует ее код
     * @return multitype
     */
    public static function getFullNameList()
    {
        $result = array();
        foreach (self::$_currencyList as $key=>$name) {
            $result[$key] = $key . "\t" . $name;
        }
        return $result;
    }
    
    /**
     * Функция возвращает название валюты по ее коду
     * @param string $shotName код валюты
     * @return boolean|string 
     */
    public static function getName( $shotName )
    {
        if( !self::isCurrency($shotName) ) return FALSE;
        return self::$_currencyList[strtoupper($shotName)];
    }
    
    /**
     * Проверяет есть ли код валюты в списке
     * @param string $shotName код валюты
     * @return boolean
     */
    public static function isCurrency( $shotName ) 
    {
        return array_key_exists(strtoupper($shotName), self::$_currencyList);
    }
    
    /**
     * Возвращает код валюты, установленной как валюта по умолчанию
     */
    public static function getDefaultCurrency()
    {
        return Zend_Registry::get('serviceContainer')->getService('Option')->getDefaultCurrency();        
    }
}
?>