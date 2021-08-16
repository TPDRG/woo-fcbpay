<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters( 'wc_ecpay_payment_settings',
	array(
		'enabled' => array(
			'title' 	=> '是否啟用',
			'type' 		=> 'checkbox',
			'label' 	=> '啟用',
			'default' 	=> 'no'
		),


        'payServer' => array(
            'title' 	=> '執行環境',
            'label'       	=> '執行環境',
            'type'        	=> 'select',
            'description' 	=> '執行環境為測試環境或是正式環境',
            'default'     	=> '演練環境(測試用)',
            'desc_tip'    	=> true,
            'options'     	=> array(
                'https://pay.firstbank.com.tw/PayServerOnline/OrderProcessOnline' =>'正式環境',
                'https://tpay.firstbank.com.tw/PayServerOnline/OrderProcessOnline'=>'演練環境(測試用)',
                'https://firstbank_api.tpdrg.com/OrderProcessOnline' =>'DEV',
            )
        ),
		'title' => array(
			'title' 	  => '標題',
			'type' 		  => 'text',
			'description' => '結帳時特店名稱',
			'default' 	  => '特店名稱',
			'desc_tip'    => true,
		),
		'description' => array(
			'title' 	  => '描述',
			'type' 		  => 'textarea',
			'description' => '結帳說明',
			'desc_tip'    => true,
		),
		'merchant_id' => array(
			'title' 	=>  '特店代號',
			'type' 		=> 'text',
			'default' 	=> '20031928123'
		),
		'hash_key' => array(
			'title' 	=> '驗證金鑰',
			'type' 		=> 'text',
            'description' => '交易驗證金鑰',
			'default' 	=> 'ABCD1234567889'
		),
        'payment_methods' => array(
            'type' 		=> 'ecpay_payment_methods',
        ),
	)
);
