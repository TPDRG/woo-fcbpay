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

		'InvoiceFlag' => array(
			'title' 	=> '是否啟用電子發票',
			'type' 		=> 'checkbox',
			'label' 	=> '啟用',
			'default' 	=> 'no'
		),
		'Amount_TaxRate' => array(
			'title' 	=> '電子發票稅率',
			'type' 		=> 'text',
			'description' => '5%時本欄位為 0.05',
			'default' 	=> '0.05',
			'desc_tip'    => true
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
			'default' 	=> 'http://wp.tpdrg.com:8888/checkout/order-received'
		),
		'InAccountNo' => array(
			'title' 	=> '銷帳編號',
			'type' 		=> 'text',
            'description' => 'ATM/EATM/活期帳戶 銷帳編號 5碼或7碼',
			'default' 	=> ''
		),
		'checkType' => array(
			'title' 	=> '檢核公式',
            'label'       	=> '銷帳編號檢核公式',
            'type'        	=> 'select',
            'description' 	=> '8',
            'default'     	=> '編號及應繳金額納入運算',
            'desc_tip'    	=> true,
            'options'     	=> array(
                '5' =>'編號及應繳金額納入運算',
                '7' =>'虛擬帳號+金額',
                'A' =>'虛擬帳號+金額+繳費期限',
				'8' =>'不檢核'
            )
		),
		'Apply' => array(
			'title' 	=> '是否啟用線上檢核',
			'type' 		=> 'checkbox',
			'label' 	=> '啟用',
			'default' 	=> 'no'
		),
		'CSInAccountNo1' => array(
			'title' 	=> '四大超商銷帳編號 (二萬(含)以下)',
			'type' 		=> 'text',
            'description' => '四大超商銷帳編號(二萬(含)以下)  5碼或7碼',
			'default' 	=> ''
		),
		'CSInAccountNo2' => array(
			'title' 	=> '四大超商銷帳編號 (二萬到四萬(含)以下)',
			'type' 		=> 'text',
            'description' => '銷帳編號(二萬到四萬(含)以下)  5碼或7碼',
			'default' 	=> ''
		),
		'CSInAccountNo3' => array(
			'title' 	=> '四大超商銷帳編號 (四萬到六萬(含)以下)',
			'type' 		=> 'text',
            'description' => '銷帳編號(四萬到六萬(含)以下)  5碼或7碼',
			'default' 	=> ''
		),
		'Terminal' => array(
			'title' 	=> '微信終端編號',
			'type' 		=> 'text',
            'description' => '微信終端編號',
			'default' 	=> ''
		),
		'BonusActionCode' => array(
			'title' 	=> '信用卡紅利折抵活動代碼',
			'type' 		=> 'text',
            'description' => '信用卡紅利折抵活動代碼',
			'default' 	=> ''
		),
		'CustomResultPage' => array(
			'title' 	=> '信用卡結果顯示頁模式',
            'label'       	=> '信用卡結果顯示頁模式',
            'type'        	=> 'select',
            'description' 	=> '0',
            'options'     	=> array(
                '0' =>'以財金模板顯示付款結果(無法導回頁面)',
                '2' =>'以電商收款通模板顯示付款結果(可透過導回頁面)'
            )
		),
		'CustomResURL' => array(
			'title' 	=> '電商收款通模板導回網址',
			'type' 		=> 'text',
            'description' => '於電商收款通模板頁面點擊回到商城導回網址',
			'default' 	=> ''
		),
        'payment_methods' => array(
            'type' 		=> 'pay_payment_methods',
        )
	)
);
