<?php
class NPQP_Country_Code{
    
    public function getCountryCode($phone){
       $country_code = $this->getCountryCodeApi($phone) ? $this->getCountryCodeApi($phone) : $this->getCountryCodeArr($phone);
       return $country_code;
    }
    
    public function getCountryCodeApi($phone){
        $response = wp_safe_remote_get( 'http://ec2-54-67-86-85.us-west-1.compute.amazonaws.com/api/api.php?ANI=' . urlencode($phone) );
        if ( ! is_wp_error($response) ) {
            $response = json_decode( $response['body'] );
            $country_code = $response->countryPrefix;
            return [
                'country_code' => $country_code,
                'number' => $this->getNumber($phone, $country_code),
            ];
        } else {
            $country_code = false;
        }
        return false;
    }
    
    public function getCountryCodeArr($phone){
        $country_codes = $this->countryCodes();
        $phone = ltrim($phone, '+');
        foreach($country_codes as $code){
            if( $code == substr( $phone, 0, strlen($code) ) ) {
                $country_code = $code;
                break;
            }
        }
        if ( $country_code ) {
            return [
                'country_code' => $country_code,
                'number' => $this->getNumber($phone, $country_code),
            ];
        }
        return false;
    }
    
    public function getNumber($phone, $prefix){
        $number = str_replace( ['+', '-', ' '], '', $phone);
        $number = substr_replace( $number, '', 0, strlen($prefix) ); 
        return $number;
    }
    
    public function getCountryNameByCode($code){
        $country_names = array_flip( $this->countryCodes() );
        return $country_names[$code] ? $country_names[$code] : false;
    }
    
    public function getCountryCodeByName($name){
        $country_codes = $this->countryCodes();
        return $country_codes[$name] ? $country_codes[$name] : false;
    }
    
    public function countryCodes(){
        return [
            'Afganistan' => '93',
            'Afrika Selatan' => '27',
            'Afrika Tengah' =>  '236',
            'Albania' =>  '355',
            'Algeria' =>  '213',
            'Amerika Serikat' =>  '1',
            'Andorra' =>  '376',
            'Angola' =>  '244',
            'Antigua & Barbuda' =>  '1-268',
            'Arab Saudi' =>  '966',
            'Argentina' =>  '54',
            'Armenia' =>  '374',
            'Australia' =>  '61',
            'Austria' =>  '43',
            'Azerbaijan' =>  '994',
            'Bahama' =>  '1-242',
            'Bahrain' =>  '973',
            'Bangladesh' =>  '880',
            'Barbados  1-246',
            'Belanda' =>  '31',
            'Belarus' =>  '375',
            'Belgia' =>  '32',
            'Belize' =>  '501',
            'Benin' =>  '229',
            'Bhutan' =>  '975',
            'Bolivia' =>  '591',
            'Bosnia & Herzegovina' =>  '387',
            'swana' =>  '267',
            'Brasil' =>  '55',
            'Britania Raya (Inggris)' =>  '44',
            'nei Darussalam' =>  '673',
            'Bulgaria' =>  '359',
            'Burkina Faso' =>  '226',
            'Burundi' =>  '257',
            'Ceko' =>  '420',
            'Chad' =>  '235',
            'Chili' =>  '56',
            'China' =>  '86',
            'Denmark' =>  '45',
            'bouti' =>  '253',
            'Domikia' =>  '1-767',
            'Ekuador' =>  '593',
            'Salvador' =>  '503',
            'Eritrea' =>  '291',
            'Estonia' =>  '372',
            'Ethiopia' =>  '251',
            'Fiji' =>  '679',
            'Filipina' =>  '63',
            'Finlandia' =>  '358',
            'Gabon' =>  '241',
            'Gambia' =>  '220',
            'Georgia' =>  '995',
            'Ghana' =>  '233',
            'Grenada' =>  '1-473',
            'Guatemala' =>  '502',
            'Guinea' =>  '224',
            'Guinea Bissau' =>  '245',
            'Guinea Khatulistiwa' =>  '240',
            'Guyana' =>  '592',
            'Haiti' =>  '509',
            'Honduras' =>  '504',
            'Hongaria' =>  '36',
            'Hongkong' =>  '852',
            'India' =>  '91',
            'Indonesia' =>  '62',
            'Irak' =>  '964',
            'Iran' =>  '98',
            'Irlandia' =>  '353',
            'Islandia' =>  '354',
            'Israel' =>  '972',
            'Italia' =>  '39',
            'Jamaika' =>  '1-876',
            'Jepang' =>  '81',
            'Jerman' =>  '49',
            'Jordan' =>  '962',
            'Kamboja' =>  '855',
            'Kamerun' =>  '237',
            'Kanada' =>  '1',
            'Kazakhstan' =>  '7',
            'Kenya' =>  '254',
            'Kirgizstan' =>  '996',
            'Kiribati' =>  '686',
            'Kolombia' =>  '57',
            'Komoro' =>  '269',
            'Republik Kongo' =>  '243',
            'Korea Selatan' =>  '82',
            'Korea Utara' =>  '850',
            'Kosta Rika' =>  '506',
            'Kroasia' =>  '385',
            'Kuba' =>  '53',
            'Kuwait' =>  '965',
            'Laos' =>  '856',
            'Latvia' =>  '371',
            'Lebanon' =>  '961',
            'Lesotho' =>  '266',
            'Liberia' =>  '231',
            'Libya' =>  '218',
            'Liechtenstein' =>  '423',
            'Lituania' =>  '370',
            'Luksemburg' =>  '352',
            'Madagaskar' =>  '261',
            'Makao' =>  '853',
            'Makedonia' =>  '389',
            'Maladewa' =>  '960',
            'Malawi' =>  '265',
            'Malaysia' =>  '60',
            'Mali' =>  '223',
            'Malta' =>  '356',
            'Maroko' =>  '212',
            'Marshall (Kep.)' =>  '692',
            'Mauritania' =>  '222',
            'Mauritius' =>  '230',
            'Meksiko' =>  '52',
            'Mesir' =>  '20',
            'Mikronesia (Kep.)' =>  '691',
            'Moldova' =>  '373',
            'Monako' =>  '377',
            'Mongolia' =>  '976',
            'Montenegro' =>  '382',
            'Mozambik' =>  '258',
            'Myanmar' =>  '95',
            'Namibia' =>  '264',
            'Nauru' =>  '674',
            'Nepal' =>  '977',
            'Niger' =>  '227',
            'Nigeria' =>  '234',
            'Nikaragua' =>  '505',
            'Norwegia' =>  '47',
            'Oman' =>  '968',
            'Pakistan' =>  '92',
            'Palau' =>  '680',
            'Panama' =>  '507',
            'Pantai Gading' =>  '225',
            'Papua Nugini' =>  '675',
            'Paraguay' =>  '595',
            'Perancis' =>  '33',
            'Peru' =>  '51',
            'Polandia' =>  '48',
            'Portugal' =>  '351',
            'Qatar' =>  '974',
            'Rep. Dem. Kongo' =>  '242',
            'Republik Dominika' =>  '1-809',
            'Rumania' =>  '40',
            'Rusia' =>  '7',
            'Rwanda' =>  '250',
            'Saint Kitts and Nevis' =>  '1-869',
            'Saint Lucia' =>  '1-758',
            'Saint Vincent & the Grenadines' =>  '1-784',
            'Samoa' =>  '685',
            'San Marino' =>  '378',
            'Sao Tome & Principe' =>  '239',
            'Selandia Baru' =>  '64',
            'Senegal' =>  '221',
            'Serbia' =>  '381',
            'Seychelles' =>  '248',
            'Sierra Leone' =>  '232',
            'Singapura' =>  '65',
            'Siprus' =>  '357',
            'Slovenia' =>  '386',
            'Slowakia' =>  '421',
            'Solomon (Kep.)' =>  '677',
            'Somalia' =>  '252',
            'Spanyol' =>  '34',
            'Sri Lanka' =>  '94',
            'Sudan' =>  '249',
            'Sudan Selatan' =>  '211',
            'Suriah' =>  '963',
            'Suriname' =>  '597',
            'Swaziland' =>  '268',
            'Swedia' =>  '46',
            'Swiss' =>  '41',
            'Tajikistan' =>  '992',
            'Tanjung Verde' =>  '238',
            'Tanzania' =>  '255',
            'Taiwan' =>  '886',
            'Thailand' =>  '66',
            'Timor Leste' =>  '670',
            'Togo' =>  '228',
            'Tonga' =>  '676',
            'Trinidad & Tobago' =>  '1-868',
            'Tunisia' =>  '216',
            'Turki' =>  '90',
            'Turkmenistan' =>  '993',
            'Tuvalu' =>  '688',
            'Uganda' =>  '256',
            'Ukraina' =>  '380',
            'Uni Emirat Arab' =>  '971',
            'Uruguay' =>  '598',
            'Uzbekistan' =>  '998',
            'Vanuatu' =>  '678',
            'Venezuela' =>  '58',
            'Vietnam' =>  '84',
            'Yaman' =>  '967',
            'Yunani' =>  '30',
            'Zambia' =>  '260',
            'Zimbabwe' =>  '263',
        ];
    }
}