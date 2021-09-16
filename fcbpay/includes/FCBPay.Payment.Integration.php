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
/**
 * 額外付款資訊。
 */
abstract class Pay_ExtraPaymentInfo {

    /**
     * 需要額外付款資訊。
     */
    const Yes = 'Y';

    /**
     * 不需要額外付款資訊。
     */
    const No = 'N';

}

/**
 * 額外付款資訊。
 */
abstract class Pay_DeviceType {

    /**
     * 桌機版付費頁面。
     */
    const PC = 'P';

    /**
     * 行動裝置版付費頁面。
     */
    const Mobile = 'M';

}

/**
 * 信用卡訂單處理動作資訊。
 */
abstract class CREDIT_ActionType {

    /**
     * 關帳
     */
    const C = 'C';

    /**
     * 退刷
     */
    const R = 'R';

    /**
     * 取消
     */
    const E = 'E';

    /**
     * 放棄
     */
    const N = 'N';

}

/**
 * 電子發票開立註記。
 */
abstract class InvoiceState {
    /**
     * 需要開立電子發票。
     */
    const Yes = 'Y';

    /**
     * 不需要開立電子發票。
     */
    const No = '';
}

/**
 * 電子發票載具類別
 */
abstract class Pay_CarruerType {
  // 無載具
  const None = '';

  // 會員載具
  const Member = '1';

  // 買受人自然人憑證
  const Citizen = '2';

  // 買受人手機條碼
  const Cellphone = '3';
}

/**
 * 電子發票列印註記
 */
abstract class Pay_PrintMark {
  // 不列印
  const No = '0';

  // 列印
  const Yes = '1';
}

/**
 * 電子發票捐贈註記
 */
abstract class Pay_Donation {
  // 捐贈
  const Yes = '1';

  // 不捐贈
  const No = '2';
}

/**
 * 通關方式
 */
abstract class ECPay_ClearanceMark {
  // 經海關出口
  const Yes = '1';

  // 非經海關出口
  const No = '2';
}

/**
 * 課稅類別
 */
abstract class Pay_TaxType {
  // 應稅
  const Dutiable = '1';

  // 零稅率
  const Zero = '2';

  // 免稅
  const Free = '3';

  // 應稅與免稅混合(限收銀機發票無法分辦時使用，且需通過申請核可)
  const Mix = '9';
}

/**
 * 字軌類別
 */
abstract class Pay_InvType {
  // 一般稅額
  const General = '07';

  // 特種稅額
  const Special = '08';
}

if(!class_exists('Pay_EncryptType', false))
{
    abstract class Pay_EncryptType {
        // MD5(預設)
        const ENC_MD5 = 0;

        // SHA256
        const ENC_SHA256 = 1;
    }
}

/**

 */
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
    public $Query = 'Query';
    public $Action = 'Action';
	
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
			"InvoiceMark"       => InvoiceState::No,
			"Items"             => array()
        );

        $this->SendExtend = array();

        $this->Query = array(
            'MerchantTradeNo' => '',
            'TimeStamp' => ''
        );
        $this->Action = array(
            'MerchantTradeNo' => '',
            'TradeNo' => '',
            'Action' => CREDIT_ActionType::C,
            'TotalAmount' => 0
        );
        $this->Capture = array(
            'MerchantTradeNo' => '',
            'CaptureAMT' => 0,
            'UserRefundAMT' => 0,
            'PlatFormId' => ''
        );

        $this->TradeNo = array(
            'DateType' => '',
            'BeginDate' => '',
            'EndDate' => '',
            'MediaFormated' => ''
        );

        $this->Trade = array(
            'CreditRefundId' => '',
            'CreditAmount' => '',
            'CreditCheckCode' => ''
        );

        $this->Funding = array(
            "PayDateType" => '',
            "StartDate" => '',
            "EndDate" => ''
        );

    }

    //產生訂單
    function CheckOut($target = "_self") {
		$arParameters = $this->Send;
        Pay_Send::CheckOut($target,$arParameters,$this->SendExtend,$this->hashK,'',$this->ServiceURL);
    }

    //產生訂單html code
    function CheckOutString($paymentButton = 'Submit', $target = "_self") {
		exit("FCBPay.Payment.Integration.php - TPaySDK - CheckOutString");
        $arParameters = array_merge( array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType) ,$this->Send);
        return Pay_Send::CheckOutString($paymentButton,$target = "_self",$arParameters,$this->SendExtend,$this->HashKey,$this->HashIV,$this->ServiceURL);
    }

    //取得付款結果通知的方法
    function CheckOutFeedback() {
		return $arFeedback = Pay_CheckOutFeedback::CheckOut(array_merge($_POST, array('EncryptType' => "")),$this->HashKey,0);
    }

    //訂單查詢作業
    function QueryTradeInfo() {
		exit("FCBPay.Payment.Integration.php - TPaySDK - QueryTradeInfo");
        return $arFeedback = Pay_QueryTradeInfo::CheckOut(array_merge($this->Query,array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType)) ,$this->HashKey ,$this->HashIV ,$this->ServiceURL) ;
    }

    //信用卡定期定額訂單查詢的方法
    function QueryPeriodCreditCardTradeInfo() {
		exit("FCBPay.Payment.Integration.php - TPaySDK - QueryPeriodCreditCardTradeInfo");
        return $arFeedback = Pay_QueryPeriodCreditCardTradeInfo::CheckOut(array_merge($this->Query,array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType)) ,$this->HashKey ,$this->HashIV ,$this->ServiceURL);
    }

    //信用卡關帳/退刷/取消/放棄的方法
    function DoAction() {
		exit("FCBPay.Payment.Integration.php - TPaySDK - DoAction");
        return $arFeedback = Pay_DoAction::CheckOut(array_merge($this->Action,array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType)) ,$this->HashKey ,$this->HashIV ,$this->ServiceURL);
    }

    //合作特店申請撥款
    function AioCapture(){
		exit("FCBPay.Payment.Integration.php - TPaySDK - AioCapture");
        return $arFeedback = Pay_AioCapture::Capture(array_merge($this->Capture,array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType)) ,$this->HashKey ,$this->HashIV ,$this->ServiceURL);
    }

    //下載會員對帳媒體檔
    function TradeNoAio($target = "_self"){
		exit("FCBPay.Payment.Integration.php - TPaySDK - TradeNoAio");
        $arParameters = array_merge( array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType) ,$this->TradeNo);
        Pay_TradeNoAio::CheckOut($target,$arParameters,$this->HashKey,$this->HashIV,$this->ServiceURL);
    }

    //查詢信用卡單筆明細紀錄
    function QueryTrade(){
		exit("FCBPay.Payment.Integration.php - TPaySDK - QueryTrade");
        return $arFeedback = Pay_QueryTrade::CheckOut(array_merge($this->Trade,array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType)) ,$this->HashKey ,$this->HashIV ,$this->ServiceURL);
    }

    //下載信用卡撥款對帳資料檔
    function FundingReconDetail($target = "_self"){
		exit("FCBPay.Payment.Integration.php - TPaySDK - FundingReconDetail");
        $arParameters = array_merge( array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType) ,$this->Funding);
        Pay_FundingReconDetail::CheckOut($target,$arParameters,$this->HashKey,$this->HashIV,$this->ServiceURL);
    }

    // 產生訂單(站內付) v1.0.11128 wesley
    function CreateTrade() {
		exit("FCBPay.Payment.Integration.php - TPaySDK - CreateTrade");
        $arParameters = array_merge( array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType) ,$this->Send);
        return $arFeedback = Pay_CreateTrade::CheckOut($arParameters,$this->SendExtend,$this->HashKey,$this->HashIV,$this->ServiceURL);
    }
}

/**
* 抽象類
*/
abstract class Pay_Aio
{

    protected static function ServerPost($parameters ,$ServiceURL) {
		exit("FCBPay.Payment.Integration.php - ECPay_Aio - ServerPost");
        $ch = curl_init();

        if (FALSE === $ch) {
            throw new Exception('curl failed to initialize');
        }

        curl_setopt($ch, CURLOPT_URL, $ServiceURL);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        $rs = curl_exec($ch);

        if (FALSE === $rs) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }

        curl_close($ch);

        return $rs;
    }

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

    static function CheckOutString($paymentButton = 'Submit',$target = "_self",$arParameters = array(),$arExtend = array(),$HashKey='',$HashIV='',$ServiceURL=''){

		exit("FCBPay.Payment.Integration.php - ECPay_Send - CheckOutString");
        $arParameters = self::process($arParameters,$arExtend);
        //產生檢查碼
        $szCheckMacValue = ECPay_CheckMacValue::generate($arParameters,$HashKey,$HashIV,$arParameters['EncryptType']);

        //生成表單
        $szHtml = parent::HtmlEncode($target, $arParameters, $ServiceURL, $szCheckMacValue, $paymentButton) ;
        return  $szHtml ;
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
		var_dump(urldecode($Parameters['hashK'].http_build_query($paras)));
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

        $EncryptType = $arParameters["EncryptType"];
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

class Pay_QueryTradeInfo extends Pay_Aio
{
    static function CheckOut($arParameters = array(),$HashKey ='',$HashIV ='',$ServiceURL = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_QueryTradeInfo - CheckOut");
        $arErrors = array();
        $arParameters['TimeStamp'] = time();
        $arFeedback = array();
        $arConfirmArgs = array();

        // 注意: 查詢訂單 API 未提供 EncryptType 參數
        $EncryptType = $arParameters["EncryptType"];
        unset($arParameters["EncryptType"]);

        // 呼叫查詢。
        if (sizeof($arErrors) == 0) {
            $arParameters["CheckMacValue"] = ECPay_CheckMacValue::generate($arParameters,$HashKey,$HashIV,$EncryptType);
            // 送出查詢並取回結果。
            $szResult = static::ServerPost($arParameters,$ServiceURL);
            $szResult = str_replace(' ', '%20', $szResult);
            $szResult = str_replace('+', '%2B', $szResult);

            // 轉結果為陣列。
            parse_str($szResult, $arResult);
            // 重新整理回傳參數。
            foreach ($arResult as $keys => $value) {
                if ($keys == 'CheckMacValue') {
                    $szCheckMacValue = $value;
                } else {
                    $arFeedback[$keys] = $value;
                    $arConfirmArgs[$keys] = $value;
                }
            }

            // 驗證檢查碼。
            if (sizeof($arFeedback) > 0) {
                $szConfirmMacValue = ECPay_CheckMacValue::generate($arConfirmArgs,$HashKey,$HashIV,$EncryptType);
                if ($szCheckMacValue != $szConfirmMacValue) {
                    array_push($arErrors, 'CheckMacValue verify fail.');
                }
            }
        }

        if (sizeof($arErrors) > 0) {
            throw new Exception(join('- ', $arErrors));
        }

        return $arFeedback ;

    }
}

class Pay_QueryPeriodCreditCardTradeInfo extends Pay_Aio
{
    static function CheckOut($arParameters = array(),$HashKey ='',$HashIV ='',$ServiceURL = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_QueryPeriodCreditCardTradeInfo - CheckOut");
        $arErrors = array();
        $arParameters['TimeStamp'] = time();
        $arFeedback = array();
        $arConfirmArgs = array();

        $EncryptType = $arParameters["EncryptType"];
        unset($arParameters["EncryptType"]);

        // 呼叫查詢。
        if (sizeof($arErrors) == 0) {
            $arParameters["CheckMacValue"] = ECPay_CheckMacValue::generate($arParameters,$HashKey,$HashIV,$EncryptType);
            // 送出查詢並取回結果。
            $szResult = static::ServerPost($arParameters,$ServiceURL);
            $szResult = str_replace(' ', '%20', $szResult);
            $szResult = str_replace('+', '%2B', $szResult);

            // 轉結果為陣列。
            $arResult = json_decode($szResult,true);
            // 重新整理回傳參數。
            foreach ($arResult as $keys => $value) {
                $arFeedback[$keys] = $value;
            }

        }

        if (sizeof($arErrors) > 0) {
            throw new Exception(join('- ', $arErrors));
        }

        return $arFeedback ;
    }
}

class Pay_DoAction extends Pay_Aio
{
    static function CheckOut($arParameters = array(),$HashKey ='',$HashIV ='',$ServiceURL = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_DoAction - CheckOut");
                // 變數宣告。
        $arErrors = array();
        $arFeedback = array();

        $EncryptType = $arParameters["EncryptType"];
        unset($arParameters["EncryptType"]);

        //產生驗證碼
        $szCheckMacValue = ECPay_CheckMacValue::generate($arParameters,$HashKey,$HashIV,$EncryptType);
        $arParameters["CheckMacValue"] = $szCheckMacValue;
        // 送出查詢並取回結果。
        $szResult = static::ServerPost($arParameters,$ServiceURL);
        // 轉結果為陣列。
        parse_str($szResult, $arResult);
        // 重新整理回傳參數。
        foreach ($arResult as $keys => $value) {
            if ($keys == 'CheckMacValue') {
                $szCheckMacValue = $value;
            } else {
                $arFeedback[$keys] = $value;
            }
        }

        if (array_key_exists('RtnCode', $arFeedback) && $arFeedback['RtnCode'] != '1') {
            array_push($arErrors, vsprintf('#%s: %s', array($arFeedback['RtnCode'], $arFeedback['RtnMsg'])));
        }

        if (sizeof($arErrors) > 0) {
            throw new Exception(join('- ', $arErrors));
        }

        return $arFeedback ;

    }
}

class Pay_AioCapture extends Pay_Aio
{
    static function Capture($arParameters=array(),$HashKey='',$HashIV='',$ServiceURL=''){

		exit("FCBPay.Payment.Integration.php - ECPay_AioCapture - Capture");
        $arErrors   = array();
        $arFeedback = array();

        $EncryptType = $arParameters["EncryptType"];
        unset($arParameters["EncryptType"]);

        $szCheckMacValue = ECPay_CheckMacValue::generate($arParameters,$HashKey,$HashIV,$EncryptType);
        $arParameters["CheckMacValue"] = $szCheckMacValue;

        // 送出查詢並取回結果。
        $szResult = static::ServerPost($arParameters,$ServiceURL);

        // 轉結果為陣列。
        parse_str($szResult, $arResult);

        // 重新整理回傳參數。
        foreach ($arResult as $keys => $value) {
            $arFeedback[$keys] = $value;
        }

        if (sizeof($arErrors) > 0) {
            throw new Exception(join('- ', $arErrors));
        }

        return $arFeedback;

    }
}

class Pay_TradeNoAio extends Pay_Aio
{
    static function CheckOut($target = "_self",$arParameters = array(),$HashKey='',$HashIV='',$ServiceURL=''){
		
		exit("FCBPay.Payment.Integration.php - ECPay_TradeNoAio - CheckOut");
        //產生檢查碼
        $EncryptType = $arParameters['EncryptType'];
        unset($arParameters['EncryptType']);

        $szCheckMacValue = ECPay_CheckMacValue::generate($arParameters,$HashKey,$HashIV,$EncryptType);

        //生成表單，自動送出
        $szHtml = parent::HtmlEncode($target, $arParameters, $ServiceURL, $szCheckMacValue, '') ;
        echo $szHtml ;
        exit;
    }
}

class Pay_QueryTrade extends Pay_Aio
{
    static function CheckOut($arParameters = array(),$HashKey ='',$HashIV ='',$ServiceURL = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_QueryTrade - CheckOut");
        $arErrors = array();
        $arFeedback = array();
        $arConfirmArgs = array();

        $EncryptType = $arParameters["EncryptType"];
        unset($arParameters["EncryptType"]);

        // 呼叫查詢。
        if (sizeof($arErrors) == 0) {
            $arParameters["CheckMacValue"] = ECPay_CheckMacValue::generate($arParameters,$HashKey,$HashIV,$EncryptType);
            // 送出查詢並取回結果。
            $szResult = static::ServerPost($arParameters,$ServiceURL);

            // 轉結果為陣列。
            $arResult = json_decode($szResult,true);

            // 重新整理回傳參數。
            foreach ($arResult as $keys => $value) {
                $arFeedback[$keys] = $value;
            }
        }

        if (sizeof($arErrors) > 0) {
            throw new Exception(join('- ', $arErrors));
        }

        return $arFeedback ;
    }
}

class Pay_FundingReconDetail extends Pay_Aio
{
    static function CheckOut($target = "_self",$arParameters = array(),$HashKey='',$HashIV='',$ServiceURL=''){
		exit("FCBPay.Payment.Integration.php - ECPay_FundingReconDetail - CheckOut");
        //產生檢查碼
        $EncryptType = $arParameters["EncryptType"];
        unset($arParameters["EncryptType"]);

        $szCheckMacValue = ECPay_CheckMacValue::generate($arParameters,$HashKey,$HashIV,$EncryptType);

        //生成表單，自動送出
        $szHtml = parent::HtmlEncode($target, $arParameters, $ServiceURL, $szCheckMacValue, '') ;
        echo $szHtml ;
        exit;
    }
}

class Pay_CreateTrade extends Pay_Aio
{
    //付款方式物件
    public static $PaymentObj ;

    protected static function process($arParameters = array(),$arExtend = array())
    {
		exit("FCBPay.Payment.Integration.php - ECPay_CreateTrade - process");
        //宣告付款方式物件
        $PaymentMethod    = 'ECPay_'.$arParameters['ChoosePayment'];
        self::$PaymentObj = new $PaymentMethod;

        //檢查參數
        $arParameters = self::$PaymentObj->check_string($arParameters);

        //檢查商品
        $arParameters = self::$PaymentObj->check_goods($arParameters);

        //檢查各付款方式的額外參數&電子發票參數
        $arExtend = self::$PaymentObj->check_extend_string($arExtend,$arParameters['InvoiceMark']);

        //過濾
        $arExtend = self::$PaymentObj->filter_string($arExtend,$arParameters['InvoiceMark']);

        //合併共同參數及延伸參數
        return array_merge($arParameters,$arExtend) ;
    }

    static function CheckOut($arParameters = array(),$arExtend = array(),$HashKey='',$HashIV='',$ServiceURL=''){

		exit("FCBPay.Payment.Integration.php - ECPay_CreateTrade - CheckOut");
        $arErrors   = array();
        $arFeedback = array();
        $szCheckMacValueReturn = '' ;

        $arParameters = self::process($arParameters,$arExtend);

        //產生檢查碼
        $szCheckMacValue = ECPay_CheckMacValue::generate($arParameters,$HashKey,$HashIV,$arParameters['EncryptType']);
        $arParameters["CheckMacValue"] = $szCheckMacValue;

        // 送出查詢並取回結果。
        $szResult = static::ServerPost($arParameters,$ServiceURL);

        // 轉結果為陣列。
        $arResult = json_decode($szResult,true);

        // 重新整理回傳參數。
        foreach ($arResult as $keys => $value) {
            if ($keys == 'CheckMacValue') {
                $szCheckMacValueReturn = $value;
            } else {
                $arFeedback[$keys] = $value;
            }
        }

        if (array_key_exists('RtnCode', $arFeedback) && $arFeedback['RtnCode'] != '1') {
            array_push($arErrors, vsprintf('#%s: %s', array($arFeedback['RtnCode'], $arFeedback['RtnMsg'])));
        }
        else{
            // 參數取回壓碼驗證
            $szCheckMacValueReturnParameters = ECPay_CheckMacValue::generate($arFeedback,$HashKey,$HashIV,$arParameters['EncryptType']);

            if($szCheckMacValueReturnParameters != $szCheckMacValueReturn){
                array_push($arErrors, 'CheckMacValue verify fail.');
            }
        }

        if (sizeof($arErrors) > 0) {
            throw new Exception(join('- ', $arErrors));
        }

        return $arFeedback ;
    }
}

Abstract class Pay_Verification
{
    // 電子發票延伸參數。
    public $arInvoice = array(
            "RelateNumber",
            "CustomerIdentifier",
            "CarruerType" ,
            "CustomerID" ,
            "Donation" ,
            "Print" ,
            "TaxType",
            "CustomerName" ,
            "CustomerAddr" ,
            "CustomerPhone" ,
            "CustomerEmail" ,
            "ClearanceMark" ,
            "CarruerNum" ,
            "LoveCode" ,
            "InvoiceRemark" ,
            "DelayDay",
            "InvoiceItemName",
            "InvoiceItemCount",
            "InvoiceItemWord",
            "InvoiceItemPrice",
            "InvoiceItemTaxType",
            "InvType"
        );

    // 付款方式延伸參數
    public $arPayMentExtend = array();

    //檢查共同參數
    public function check_string($arParameters = array()){

		exit("FCBPay.Payment.Integration.php - ECPay_Verification - CheckOut");
        $arErrors = array();
        if (strlen($arParameters['MerchantID']) == 0) {
            array_push($arErrors, 'MerchantID is required.');
        }
        if (strlen($arParameters['MerchantID']) > 10) {
            array_push($arErrors, 'MerchantID max langth as 10.');
        }

        if (strlen($arParameters['ReturnURL']) == 0) {
            array_push($arErrors, 'ReturnURL is required.');
        }
        if (strlen($arParameters['ClientBackURL']) > 200) {
            array_push($arErrors, 'ClientBackURL max langth as 200.');
        }
        if (strlen($arParameters['OrderResultURL']) > 200) {
            array_push($arErrors, 'OrderResultURL max langth as 200.');
        }

        if (strlen($arParameters['MerchantTradeNo']) == 0) {
            array_push($arErrors, 'MerchantTradeNo is required.');
        }
        if (strlen($arParameters['MerchantTradeNo']) > 20) {
            array_push($arErrors, 'MerchantTradeNo max langth as 20.');
        }
        if (strlen($arParameters['MerchantTradeDate']) == 0) {
            array_push($arErrors, 'MerchantTradeDate is required.');
        }
        if (strlen($arParameters['TotalAmount']) == 0) {
            array_push($arErrors, 'TotalAmount is required.');
        }
        if (strlen($arParameters['TradeDesc']) == 0) {
            array_push($arErrors, 'TradeDesc is required.');
        }
        if (strlen($arParameters['TradeDesc']) > 200) {
            array_push($arErrors, 'TradeDesc max langth as 200.');
        }
        if (strlen($arParameters['ChoosePayment']) == 0) {
            array_push($arErrors, 'ChoosePayment is required.');
        }
        if (strlen($arParameters['NeedExtraPaidInfo']) == 0) {
            array_push($arErrors, 'NeedExtraPaidInfo is required.');
        }
        if (sizeof($arParameters['Items']) == 0) {
            array_push($arErrors, 'Items is required.');
        }

        // 檢查CheckMacValue加密方式
        if (strlen($arParameters['EncryptType']) > 1) {
            array_push($arErrors, 'EncryptType max langth as 1.');
        }

        if (sizeof($arErrors)>0) throw new Exception(join('<br>', $arErrors));

        if (!$arParameters['PlatFormId']) {
            unset($arParameters['PlatFormId']);
        }

        // 檢查是否支援 IgnorePayment 參數，不支援則移除此參數
        if ($this->support_ignore_payment($arParameters) === false) {
            unset($arParameters['IgnorePayment']);
        }

        return $arParameters ;
    }

    //檢查延伸參數
    public function check_extend_string($arExtend = array(),$InvoiceMark = ''){
		
		exit("FCBPay.Payment.Integration.php - ECPay_Verification - check_extend_string");
        //沒設定參數的話，就給預設參數
        foreach ($this->arPayMentExtend as $key => $value) {
            if(!isset($arExtend[$key])) $arExtend[$key] = $value;
        }

        //若有開發票，檢查一下發票參數
        if ($InvoiceMark == 'Y') $arExtend = $this->check_invoiceString($arExtend);

        return $arExtend ;
    }

    //檢查商品
    public function check_goods($arParameters = array()){
		exit("FCBPay.Payment.Integration.php - ECPay_Verification - check_goods");
        // 檢查產品名稱。
        $szItemName = '';
        $arErrors   = array();
        if (sizeof($arParameters['Items']) > 0) {
            foreach ($arParameters['Items'] as $keys => $value) {
                $szItemName .= vsprintf('#%s %d %s x %u', $arParameters['Items'][$keys]);
                if (!array_key_exists('ItemURL', $arParameters)) {
                    if(array_key_exists('URL', $arParameters['Items'][$keys])) {
                        $arParameters['ItemURL'] = $arParameters['Items'][$keys]['URL'];
                    }
                }
            }

            if (strlen($szItemName) > 0) {
                $szItemName = mb_substr($szItemName, 1, 200);
                $arParameters['ItemName'] = $szItemName ;
            }
        } else {
            array_push($arErrors, "Goods information not found.");
        }

        if(sizeof($arErrors)>0) throw new Exception(join('<br>', $arErrors));

        unset($arParameters['Items']);
        return $arParameters ;
    }

    //過濾多餘參數
    public function filter_string($arExtend = array(),$InvoiceMark = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_Verification - filter_string");
        $arPayMentExtend = array_merge(array_keys($this->arPayMentExtend), ($InvoiceMark == '') ? array() : $this->arInvoice);
        foreach ($arExtend as $key => $value) {
            if (!in_array($key,$arPayMentExtend )) {
                unset($arExtend[$key]);
            }
        }

        return $arExtend ;
    }

    //檢查電子發票參數
    public function check_invoiceString($arExtend = array()){
		exit("FCBPay.Payment.Integration.php - ECPay_Verification - check_invoiceString");
        $arErrors = array();

        // 廠商自訂編號RelateNumber(不可為空)
        if(!array_key_exists('RelateNumber', $arExtend)){
            array_push($arErrors, 'RelateNumber is required.');
        }else{
            if (strlen($arExtend['RelateNumber']) > 30) {
                array_push($arErrors, "RelateNumber max length as 30.");
            }
        }

        // 統一編號CustomerIdentifier(預設為空字串)
        if(!array_key_exists('CustomerIdentifier', $arExtend)){
            $arExtend['CustomerIdentifier'] = '';
        }else{

            if( strlen( $arExtend['CustomerIdentifier'] ) > 0  )
            {
                if( !preg_match('/^[0-9]{8}$/', $arExtend['CustomerIdentifier']) )
                {
                    array_push($arErrors, '6:CustomerIdentifier length should be 8.');
                }
            }
        }

        // 載具類別CarruerType(預設為None)
        if(!array_key_exists('CarruerType', $arExtend)){
            $arExtend['CarruerType'] = Pay_CarruerType::None ;
        }else{
            //有設定統一編號的話，載具類別不可為合作特店載具或自然人憑證載具。
            $notPrint = array(Pay_CarruerType::Member, Pay_CarruerType::Citizen);
            if(strlen($arExtend['CustomerIdentifier']) > 0 && in_array($arExtend['CarruerType'], $notPrint)){
                array_push($arErrors, "CarruerType should NOT be Member or Citizen.");
            }
        }

        // 客戶代號CustomerID(預設為空字串)
        if(!array_key_exists('CustomerID', $arExtend)) {
            $arExtend['CustomerID'] = '';
        }
        // 捐贈註記 Donation(預設為No)
        if(!array_key_exists('Donation', $arExtend)){
            $arExtend['Donation'] = Pay_Donation::No ;
        }else{
            //若有帶統一編號，不可捐贈
            if(strlen($arExtend['CustomerIdentifier']) > 0 && $arExtend['Donation'] != Pay_Donation::No){
                array_push($arErrors, "Donation should be No.");
            }
        }

        // 列印註記Print(預設為No)
        if(!array_key_exists('Print', $arExtend)){
            $arExtend['Print'] = Pay_PrintMark::No;
        }else{
            //捐贈註記為捐贈(Yes)時，請設定不列印(No)
            if($arExtend['Donation'] == Pay_Donation::Yes && $arExtend['Print'] != Pay_PrintMark::No){
                array_push($arErrors, "Print should be No.");
            }
            // 統一編號不為空字串時，請設定列印(Yes)
            if(strlen($arExtend['CustomerIdentifier']) > 0 && $arExtend['Print'] != Pay_PrintMark::Yes){
                array_push($arErrors, "Print should be Yes.");
            }
        }
        // 客戶名稱CustomerName(UrlEncode, 預設為空字串)
        if(!array_key_exists('CustomerName', $arExtend)){
            $arExtend['CustomerName'] = '';
        }else{
            if (mb_strlen($arExtend['CustomerName'], 'UTF-8') > 60) {
                  array_push($arErrors, "CustomerName max length as 60.");
            }
            // 列印註記為列印(Yes)時，此參數不可為空字串
            if($arExtend['Print'] == Pay_PrintMark::Yes && strlen($arExtend['CustomerName']) == 0){
                array_push($arErrors, "CustomerName is required.");
            }
        }

        // 客戶地址CustomerAddr(UrlEncode, 預設為空字串)
        if(!array_key_exists('CustomerAddr', $arExtend)){
            $arExtend['CustomerAddr'] = '';
        }else{
            if (mb_strlen($arExtend['CustomerAddr'], 'UTF-8') > 200) {
                  array_push($arErrors, "CustomerAddr max length as 200.");
            }
            // 列印註記為列印(Yes)時，此參數不可為空字串
            if($arExtend['Print'] == Pay_PrintMark::Yes && strlen($arExtend['CustomerAddr']) == 0){
                array_push($arErrors, "CustomerAddr is required.");
            }
        }
        // 客戶電話CustomerPhone
        if(!array_key_exists('CustomerPhone', $arExtend)){
            $arExtend['CustomerPhone'] = '';
        }else{
            if (strlen($arExtend['CustomerPhone']) > 20) array_push($arErrors, "CustomerPhone max length as 20.");
        }

        // 客戶信箱CustomerEmail
        if(!array_key_exists('CustomerEmail', $arExtend)){
            $arExtend['CustomerEmail'] = '';
        }else{
            if (strlen($arExtend['CustomerEmail']) > 200) array_push($arErrors, "CustomerEmail max length as 200.");
        }

        //(CustomerEmail與CustomerPhone擇一不可為空)
        if (strlen($arExtend['CustomerPhone']) == 0 and strlen($arExtend['CustomerEmail']) == 0) array_push($arErrors, "CustomerPhone or CustomerEmail is required.");

        //課稅類別 TaxType(不可為空)
        if (strlen($arExtend['TaxType']) == 0) array_push($arErrors, "TaxType is required.");

        //通關方式 ClearanceMark(預設為空字串)
        if(!array_key_exists('ClearanceMark', $arExtend)) {
            $arExtend['ClearanceMark'] = '';
        }else{
            //課稅類別為零稅率(Zero)時，ClearanceMark不可為空字串
            if($arExtend['TaxType'] == Pay_TaxType::Zero && ($arExtend['ClearanceMark'] != ECPay_ClearanceMark::Yes || $arExtend['ClearanceMark'] != ECPay_ClearanceMark::No)) {
                array_push($arErrors, "ClearanceMark is required.");
            }
            if (strlen($arExtend['ClearanceMark']) > 0 && $arExtend['TaxType'] != Pay_TaxType::Zero) {
                array_push($arErrors, "Please remove ClearanceMark.");
            }
        }

        // CarruerNum(預設為空字串)
        if (!array_key_exists('CarruerNum', $arExtend)) {
            $arExtend['CarruerNum'] = '';
        } else {
            switch ($arExtend['CarruerType']) {
                // 載具類別為無載具(None)或會員載具(Member)時，系統自動忽略載具編號
                case Pay_CarruerType::None:
                case Pay_CarruerType::Member:
                break;
                // 載具類別為買受人自然人憑證(Citizen)時，請設定自然人憑證號碼，前2碼為大小寫英文，後14碼為數字
                case Pay_CarruerType::Citizen:
                    if (!preg_match('/^[a-zA-Z]{2}\d{14}$/', $arExtend['CarruerNum'])){
                        array_push($arErrors, "Invalid CarruerNum.");
                    }
                break;
                // 載具類別為買受人手機條碼(Cellphone)時，請設定手機條碼，第1碼為「/」，後7碼為大小寫英文、數字、「+」、「-」或「.」
                case Pay_CarruerType::Cellphone:
                    if (!preg_match('/^\/{1}[0-9a-zA-Z+-.]{7}$/', $arExtend['CarruerNum'])) {
                        array_push($arErrors, "Invalid CarruerNum.");
                    }
                break;

                default:
                    array_push($arErrors, "Please remove CarruerNum.");
            }
        }

        // 愛心碼 LoveCode(預設為空字串)
        if(!array_key_exists('LoveCode', $arExtend)) $arExtend['LoveCode'] = '';
        // 捐贈註記為捐贈(Yes)時，參數長度固定3~7碼，請設定全數字或第1碼大小寫「X」，後2~6碼全數字
        if ($arExtend['Donation'] == Pay_Donation::Yes) {
            if (!preg_match('/^([xX]{1}[0-9]{2,6}|[0-9]{3,7})$/', $arExtend['LoveCode'])) {
                array_push($arErrors, "Invalid LoveCode.");
            }
        }

        //備註 InvoiceRemark(UrlEncode, 預設為空字串)
        if(!array_key_exists('InvoiceRemark', $arExtend)) $arExtend['InvoiceRemark'] = '';

        // 延遲天數 DelayDay(不可為空, 預設為0) 延遲天數，範圍0~15，設定為0時，付款完成後立即開立發票
        if(!array_key_exists('DelayDay', $arExtend)) $arExtend['DelayDay'] = 0 ;
        if ($arExtend['DelayDay'] < 0 or $arExtend['DelayDay'] > 15) array_push($arErrors, "DelayDay should be 0 ~ 15.");


        // 字軌類別 InvType(不可為空)
        if (!array_key_exists('InvType', $arExtend)) array_push($arErrors, "InvType is required.");

        //商品相關整理
        if(!array_key_exists('InvoiceItems', $arExtend)){
            array_push($arErrors, "Invoice Goods information not found.");
        }else{
            $InvSptr = '|';
            $tmpItemName = array();
            $tmpItemCount = array();
            $tmpItemWord = array();
            $tmpItemPrice = array();
            $tmpItemTaxType = array();
            foreach ($arExtend['InvoiceItems'] as $tmpItemInfo) {
                if (mb_strlen($tmpItemInfo['Name'], 'UTF-8') > 0) {
                    array_push($tmpItemName, $tmpItemInfo['Name']);
                }
                if (strlen($tmpItemInfo['Count']) > 0) {
                    array_push($tmpItemCount, $tmpItemInfo['Count']);
                }
                if (mb_strlen($tmpItemInfo['Word'], 'UTF-8') > 0) {
                    array_push($tmpItemWord, $tmpItemInfo['Word']);
                }
                if (strlen($tmpItemInfo['Price']) > 0) {
                    array_push($tmpItemPrice, $tmpItemInfo['Price']);
                }
                if (strlen($tmpItemInfo['TaxType']) > 0) {
                    array_push($tmpItemTaxType, $tmpItemInfo['TaxType']);
                }
            }

            if ($arExtend['TaxType'] == Pay_TaxType::Mix) {
                if (in_array(Pay_TaxType::Dutiable, $tmpItemTaxType) and in_array(Pay_TaxType::Free, $tmpItemTaxType)) {
                    // Do nothing
                }  else {
                    $tmpItemTaxType = array();
                }
            }
            if ((count($tmpItemName) + count($tmpItemCount) + count($tmpItemWord) + count($tmpItemPrice) + count($tmpItemTaxType)) == (count($tmpItemName) * 5)) {
                $arExtend['InvoiceItemName']    = implode($InvSptr, $tmpItemName);
                $arExtend['InvoiceItemCount']   = implode($InvSptr, $tmpItemCount);
                $arExtend['InvoiceItemWord']    = implode($InvSptr, $tmpItemWord);
                $arExtend['InvoiceItemPrice']   = implode($InvSptr, $tmpItemPrice);
                $arExtend['InvoiceItemTaxType'] = implode($InvSptr, $tmpItemTaxType);
            }

            unset($arExtend['InvoiceItems']);
        }

        $encode_fields = array(
                'CustomerName',
                'CustomerAddr',
                'CustomerEmail',
                'InvoiceItemName',
                'InvoiceItemWord',
                'InvoiceRemark'
            );
        foreach ($encode_fields as $tmp_field) {
            $arExtend[$tmp_field] = static::ecpay_urlencode($arExtend[$tmp_field]);
        }

        if (sizeof($arErrors) > 0) {
            throw new Exception(join('<br>', $arErrors));
        }

        return $arExtend ;
    }

    /**
     * URL Encode編碼，特殊字元取代
     *
     * @param  string $sParameters
     * @return string $sParameters
     */
    public static function ecpay_urlencode($sParameters) {

		exit("FCBPay.Payment.Integration.php - ECPay_Verification - ecpay_urlencode");
        // URL Encode編碼
        $sParameters = urlencode($sParameters);

        // 轉成小寫
        $sParameters = strtolower($sParameters);

        // 參數內特殊字元取代
        $sParameters = ECPay_CheckMacValue::Replace_Symbol($sParameters);

        return $sParameters;
    }

    // 是否支援 IgnorePayment 參數
    private function support_ignore_payment($arParameters = array()){
		exit("FCBPay.Payment.Integration.php - ECPay_Verification - support_ignore_payment");
        $arSupportPayments = array(
            TPay_PaymentMethod::ALL,
            TPay_PaymentMethod::Credit,
        );
        return (in_array($arParameters['ChoosePayment'], $arSupportPayments) === true);
    }
}

/**
*  付款方式：超商代碼
*/
class ECPay_CVS extends Pay_Verification
{
    public  $arPayMentExtend = array(
                            'Desc_1'           =>'',
                            'Desc_2'           =>'',
                            'Desc_3'           =>'',
                            'Desc_4'           =>'',
                            'PaymentInfoURL'   =>'',
                            'ClientRedirectURL'=>'',
                            'StoreExpireDate'  =>''
                        );

    // 過濾多餘參數
    function filter_string($arExtend = array(),$InvoiceMark = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_CVS - filter_string");
        $arExtend = parent::filter_string($arExtend, $InvoiceMark);
        return $arExtend ;
    }
}

/**
* 付款方式 : BARCODE
*/
class ECPay_BARCODE extends Pay_Verification
{
	
    public  $arPayMentExtend = array(
                            'Desc_1'           =>'',
                            'Desc_2'           =>'',
                            'Desc_3'           =>'',
                            'Desc_4'           =>'',
                            'PaymentInfoURL'   =>'',
                            'ClientRedirectURL'=>'',
                            'StoreExpireDate'  =>''
                        );

    //過濾多餘參數
    function filter_string($arExtend = array(),$InvoiceMark = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_BARCODE - filter_string");
        $arExtend = parent::filter_string($arExtend, $InvoiceMark);
        return $arExtend ;
    }
}

/**
*  付款方式 ATM
*/
class ECPay_ATM extends Pay_Verification
{
    public  $arPayMentExtend = array(
                            'ExpireDate'       => 3,
                            'PaymentInfoURL'   => '',
                            'ClientRedirectURL'=> '',
                        );

    //過濾多餘參數
    function filter_string($arExtend = array(),$InvoiceMark = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_ATM - filter_string");
        $arExtend = parent::filter_string($arExtend, $InvoiceMark);
        return $arExtend ;
    }
}

/**
*  付款方式 WebATM
*/
class ECPay_WebATM extends Pay_Verification
{
    public  $arPayMentExtend = array();

    //過濾多餘參數
    function filter_string($arExtend = array(),$InvoiceMark = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_WebATM - filter_string");
        $arExtend = parent::filter_string($arExtend, $InvoiceMark);
        return $arExtend ;
    }
}

/**
* 付款方式 : 信用卡
*/
class ECPay_Credit extends Pay_Verification
{
    public $arPayMentExtend = array(
                                    "CreditInstallment" => '',
                                    "InstallmentAmount" => 0,
                                    "Redeem"            => FALSE,
                                    "UnionPay"          => FALSE,
                                    "Language"          => '',
                                    "BindingCard"       => '',
                                    "MerchantMemberID"  => '',
                                    "PeriodAmount"      => '',
                                    "PeriodType"        => '',
                                    "Frequency"         => '',
                                    "ExecTimes"         => '',
                                    "PeriodReturnURL"   => ''
                                );

    function filter_string($arExtend = array(),$InvoiceMark = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_Credit - filter_string");
        $arExtend = parent::filter_string($arExtend, $InvoiceMark);
        return $arExtend ;
    }
}

/**
*  付款方式：全功能
*/
class ECPay_ALL extends Pay_Verification
{
    public  $arPayMentExtend = array();

    function filter_string($arExtend = array(),$InvoiceMark = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_ALL - filter_string");
        return $arExtend ;
    }
}



/**
* 付款方式 : Google Pay
*/
class ECPay_GooglePay extends Pay_Verification
{
    public $arPayMentExtend = array();

    function filter_string($arExtend = array(), $InvoiceMark = ''){
		exit("FCBPay.Payment.Integration.php - ECPay_GooglePay - filter_string");
        $arExtend = parent::filter_string($arExtend, $InvoiceMark);
        return $arExtend ;
    }
}

/**
*  檢查碼
*/
if(!class_exists('ECPay_CheckMacValue', false))
{

    class ECPay_CheckMacValue{

        public static function generate($arParameters = array(),$HashKey = '' ,$HashIV = '',$encType = 0){
			exit("FCBPay.Payment.Integration.php - ECPay_CheckMacValue - generate");
            $sMacValue = '' ;

            if(isset($arParameters))
            {
                unset($arParameters['CheckMacValue']);
                uksort($arParameters, array('ECPay_CheckMacValue','merchantSort'));

                // 組合字串
                $sMacValue = 'HashKey=' . $HashKey ;
                foreach($arParameters as $key => $value)
                {
                    $sMacValue .= '&' . $key . '=' . $value ;
                }

                $sMacValue .= '&HashIV=' . $HashIV ;

                // URL Encode編碼
                $sMacValue = static::ecpay_urlencode($sMacValue);

                // 編碼
                switch ($encType) {
                    case Pay_EncryptType::ENC_SHA256:
                        // SHA256 編碼
                        $sMacValue = hash('sha256', $sMacValue);
                    break;

                    case Pay_EncryptType::ENC_MD5:
                    default:
                    // MD5 編碼
                        $sMacValue = md5($sMacValue);
                }

                    $sMacValue = strtoupper($sMacValue);
            }

            return $sMacValue ;
        }

        /**
        * 自訂排序使用
        */
        private static function merchantSort($a,$b)
        {
			exit("FCBPay.Payment.Integration.php - ECPay_CheckMacValue - merchantSort");
            return strcasecmp($a, $b);
        }

        /**
         * URL Encode編碼，特殊字元取代
         *
         * @param  string $sParameters
         * @return string $sParameters
         */
        public static function ecpay_urlencode($sParameters) {

			exit("FCBPay.Payment.Integration.php - ECPay_CheckMacValue - ecpay_urlencode");
            // URL Encode編碼
            $sParameters = urlencode($sParameters);

            // 轉成小寫
            $sParameters = strtolower($sParameters);

            // 參數內特殊字元取代
            $sParameters = static::Replace_Symbol($sParameters);

            return $sParameters;
        }

        /**
        * 參數內特殊字元取代
        * 傳入    $sParameters    參數
        * 傳出    $sParameters    回傳取代後變數
        */
        public static function Replace_Symbol($sParameters){
			exit("FCBPay.Payment.Integration.php - ECPay_CheckMacValue - Replace_Symbol");
            if(!empty($sParameters)){

                $sParameters = str_replace('%2D', '-', $sParameters);
                $sParameters = str_replace('%2d', '-', $sParameters);
                $sParameters = str_replace('%5F', '_', $sParameters);
                $sParameters = str_replace('%5f', '_', $sParameters);
                $sParameters = str_replace('%2E', '.', $sParameters);
                $sParameters = str_replace('%2e', '.', $sParameters);
                $sParameters = str_replace('%21', '!', $sParameters);
                $sParameters = str_replace('%2A', '*', $sParameters);
                $sParameters = str_replace('%2a', '*', $sParameters);
                $sParameters = str_replace('%28', '(', $sParameters);
                $sParameters = str_replace('%29', ')', $sParameters);
            }

            return $sParameters ;
        }

    }
}


?>
