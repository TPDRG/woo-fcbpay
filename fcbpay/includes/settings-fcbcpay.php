<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters( 'FCBpay_payment_settings',
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
		'ResURL' => array(
			'title' 	=> '回傳網址',
			'type' 		=> 'text',
            'description' => '資料回傳網址，可由電商收款通設定',
			'default' 	=> ''
		),
		'InAccountNo' => array(
			'title' 	=> '銷帳編號',
			'type' 		=> 'text',
            'description' => 'ATM/EATM/活期帳戶 銷帳編號',
			'default' 	=> ''
		),
		'checkType' => array(
			'title' 	=> '檢核公式',
			'type' 		=> 'text',
            'description' => 'ATM/EATM/活期帳戶 銷帳編號檢核公式',
			'default' 	=> ''
		),
		'InAccountNo2' => array(
			'title' 	=> '實體帳號',
			'type' 		=> 'text',
            'description' => 'ATM/EATM/活期帳戶 實體帳號',
			'default' 	=> ''
		),
		'CSInAccountNo1' => array(
			'title' 	=> '四大超商銷帳編號 (二萬(含)以下)',
			'type' 		=> 'text',
            'description' => '四大超商銷帳編號(二萬(含)以下)',
			'default' 	=> ''
		),
		'CSInAccountNo2' => array(
			'title' 	=> '四大超商銷帳編號 (二萬到四萬(含)以下)',
			'type' 		=> 'text',
            'description' => '銷帳編號(二萬到四萬(含)以下)',
			'default' 	=> ''
		),
		'CSInAccountNo3' => array(
			'title' 	=> '四大超商銷帳編號 (四萬到六萬(含)以下)',
			'type' 		=> 'text',
            'description' => '銷帳編號(四萬到六萬(含)以下)',
			'default' 	=> ''
		),
		'Terminal' => array(
			'title' 	=> '微信終端編號',
			'type' 		=> 'text',
            'description' => '微信終端編號',
			'default' 	=> ''
		),
        'payment_methods' => array(
            'type' 		=> 'pay_payment_methods',
        )
	)
);
