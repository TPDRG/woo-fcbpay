<?php

/**
 * 付款方式。
 */
abstract class TPay_PaymentMethod {

    /**
     * ALL
     */
    const ALL = 'ALL';
    /**
     * 信用卡
     */
    const CREDIT = 'CREDIT';
    /**
     * 信用卡分期
     */
    const CREDIT_3 = 'CREDIT_3';
    const CREDIT_6 = 'CREDIT_6';
    const CREDIT_9 = 'CREDIT_9';
    const CREDIT_12 = 'CREDIT_12';
    const CREDIT_15 = 'CREDIT_15';
    const CREDIT_18 = 'CREDIT_18';
    const CREDIT_24 = 'CREDIT_24';
    const CREDIT_30 = 'CREDIT_30';

    /**
     * 紅利折抵
     */
    const CREDIT_REWARD = 'CREDIT_REWARD';

    /**
     * 銀聯卡。
     */
    const UNION = 'UNION';

    /**
     * 活期帳戶
     */
    const IDP = 'IDP';

    /**
     * eATM
     */
    const eATM = 'EATM';

    /**
     * ATM
     */
    const REG = 'REG';

    /**
     * 超商。
     */
    const CS = 'CS';

    /**
     * 微信
     */
    const WECHAT = 'WECHAT';

    /**
     * TWPAY。
     */
    const TWPAY = 'TWPAY';

    /**
     * JKOS。
     */
    const JKOS = 'JKOS';
}

class FCBPaySDK {

    /**
     * @ SDK版本
     */
    public $ServiceURL = 'ServiceURL';
    public $ServiceMethod = 'ServiceMethod';
    public $hashK = 'hashK';
    public $PaymentType = 'PaymentType';
    public $Send = 'Send';
    public $SendExtend = 'SendExtend';

    function __construct() {

        $this->PaymentType = 'aio';
        $this->Send = array(
			"PlatFormId"        => '',
			"PayType"     		=> 'ALL',
			'OrderId'			=> '',
			"Amount"       		=> '',
			"ShippingFee"		=> 0,
            "ProductName"       => 'WC訂單',
            "PayTitle"     		=> 'WCFCBPay',
            "ClientIP"    		=> '127.0.0.1',
            "TimeZone" 		    => '+0800',
            "CreateTime"        => '',
            "TransTime"         => '',
            "ResURL"            => '',
			"Items"             => array()
        );

        $this->SendExtend = array();
    }

    //產生訂單
    function CheckOut($target = "_self") {
		$arParameters = $this->Send;
        Pay_Send::CheckOut($target,$arParameters,$this->SendExtend,$this->hashK,'',$this->ServiceURL);
    }

    //取得付款結果通知的方法
    function CheckOutFeedback() {
		return $arFeedback = Pay_CheckOutFeedback::CheckOut(array_merge($_POST, array('EncryptType' => "")),$this->HashKey,0);
    }
}

/**
* 抽象類
*/
abstract class Pay_Aio
{
    protected static function HtmlEncode($target = "_self", $arParameters = array(), $ServiceURL = '', $szCheckMacValue = '', $paymentButton = '') {
		//生成表單，自動送出
        $szHtml =  '<!DOCTYPE html>';
        $szHtml .= '<html>';
        $szHtml .=     '<head>';
        $szHtml .=         '<meta charset="utf-8">';
        $szHtml .=     '</head>';
        $szHtml .=     '<body>';
        $szHtml .=         "<form id=\"__payForm\" method=\"post\" target=\"{$target}\" action=\"{$ServiceURL}\">";

        foreach ($arParameters as $keys => $value) {
            $szHtml .=         "<input type=\"hidden\" name=\"{$keys}\" value=\"". htmlentities(strval($value)) . "\" />";
        }

        if(!empty($paymentButton))
        {
            $szHtml .=          "<input type=\"submit\" id=\"__paymentButton\" value=\"{$paymentButton}\" />";
        }

        $szHtml .=         '</form>';

        if(empty($paymentButton))
        {
            $szHtml .=         '<script type="text/javascript">document.getElementById("__payForm").submit();</script>';
        }

        $szHtml .=     '</body>';
        $szHtml .= '</html>';


        return $szHtml;
    }
}

/**
*  產生訂單
*/
class Pay_Send extends Pay_Aio
{
    //付款方式物件
    public static $PaymentObj ;

    protected static function process($arParameters = array(),$arExtend = array())
    {
        //檢查參數
        $arParameters = self::check_string($arParameters);
        //合併共同參數及延伸參數
		$P = array_merge($arParameters,$arExtend);
		//過濾多餘參數並產生數位簽章
		$OutP = self::filter_Arr($P);
        return $OutP;
    }


    static function CheckOut($target = "_self",$arParameters = array(),$arExtend = array(),$HashKey='',$HashIV='',$ServiceURL=''){
		$arParameters = self::process($arParameters,$arExtend);
        //生成表單，自動送出
		$szCheckMacValue = "";
        $szHtml = parent::HtmlEncode($target, $arParameters, $ServiceURL, $szCheckMacValue, '確認結帳') ;
        echo $szHtml ;
        exit;
    }
	
	//檢查共同參數
    static function check_string($arParameters = array()){

		$PayType = $arParameters['PayType'];
		$arErrors = array();
        if (strlen($arParameters['PlatFormId']) == 0) {
            array_push($arErrors, '特店編號為空值');
        }
        if (strlen($arParameters['PlatFormId']) > 40) {
            array_push($arErrors, '特店編號長度最大為40');
        }
		if (strlen($arParameters['PayType']) == 0) {
            array_push($arErrors, '付款類型為空值');
        }
        if (strlen($arParameters['OrderId']) == 0) {
            array_push($arErrors, '訂單編號為空值');
        }
		if (strlen($arParameters['OrderId']) > 20) {
            array_push($arErrors, '訂單編號長度最大為20');
        }
		if (!is_Numeric($arParameters['Amount'])) {
            array_push($arErrors, '金額格式錯誤');
        }

        if (sizeof($arErrors)>0) throw new Exception(join('<br>', $arErrors));

        return $arParameters;
    }
	
	//檢查共同參數
    static function filter_Arr($Parameters = array()){

		$require = array(
		"PlatFormId","PayType","OrderId","Amount","ShippingFee","ProductName",
        "PayTitle","HeadquarterID","StatesID","BranchAndPlatformID","BusinessUnit","SubMerchantID",
        "OrderArea","OrderAddress","OrderReceiver","OrderEmail","ClientIP",
        "ResURL","TimeZone","CreateTime","TransTime","NextURL","InAccountNo","OutAccountNo",
        "OutBank","ID","FunCode","Apply","DueDate","AutoCap","TransType","PeriodNum",
        "BonusActionCode","TimeoutSecs","LagSelect","CustomResultPage",
        "Terminal","ProductDetail", "Buyer_Identifier","DonateMark","CUSTOMEREMAIL","CarrierId1",
        "NPOBAN" ,"Amount_TaxRate");
		
		$paras = array();
		$Resultparas = array();
		foreach($require as $k=>$v) {
			if(array_key_exists($v,$Parameters))
			{
				$Resultparas[$v] = $Parameters[$v];
				if(!is_null($Parameters[$v]) && strlen($Parameters[$v])>0)
				{
					$paras[$v] = $Parameters[$v]; 
				}
			}	
		}
		uksort( $paras, 'strnatcasecmp' );
		//var_dump(urldecode($Parameters['hashK'].http_build_query($paras)));
		$HashKey = strtoupper(hash('sha256', urldecode($Parameters['hashK'].http_build_query($paras))));
		$Resultparas['HashKey'] = $HashKey;
		if($Resultparas['PayType'] == "REG")
			$Resultparas['FromPayPage'] = "1";
		//var_dump($Resultparas);
        return $Resultparas;
    }
}

class Pay_CheckOutFeedback extends Pay_Aio
{
    static function CheckOut($arParameters = array(),$HashKey = ''){
		// 變數宣告。
        $arErrors = array();
        $arFeedback = array();
        $szCheckMacValue = '';

        unset($arParameters["EncryptType"]);		
		
        // 重新整理回傳參數。
        foreach ($arParameters as $keys => $value) {
            $arFeedback[$keys] = $value;
        }
		//進行回傳加密驗證
		$paras = array();
		foreach($arFeedback as $k=>$v) {
			if(!is_null($arFeedback[$k]) && strlen($arFeedback[$k])>0 && $k!="HashKey")
			{
				$paras[$k] = str_replace("\\\"","\"",$arFeedback[$k]);
			}	
		}
		
		uksort( $paras, 'strnatcasecmp' );
		//var_dump(urldecode($HashKey.http_build_query($paras)));
		$CheckValue = strtoupper(hash('sha256', urldecode($HashKey.http_build_query($paras))));

        if ($CheckValue != $arParameters['HashKey']) {
            array_push($arErrors, 'HASHKEY不符合.');
        }

        if (sizeof($arErrors) > 0) {
            throw new Exception(join('- ', $arErrors));
        }

        return $arParameters;
    }
}
?>
