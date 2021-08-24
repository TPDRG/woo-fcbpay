<?php
include_once('FCBPay.Payment.Integration.php');

final class FCBPay_Woo_AllInOne extends FCBPaySDK {

    //訂單查詢作業
    function QueryTradeInfo() {
		exit("FCBPay.Payment.Integration.Shell.php - TPay_Woo_AllInOne - QueryTradeInfo");
        return $arFeedback = Pay_Woo_QueryTradeInfo::CheckOut(array_merge($this->Query,array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType)) ,$this->HashKey ,$this->HashIV ,$this->ServiceURL) ;
    }

    //信用卡定期定額訂單查詢的方法
    function QueryPeriodCreditCardTradeInfo() {
		exit("FCBPay.Payment.Integration.Shell.php - TPay_Woo_AllInOne - QueryPeriodCreditCardTradeInfo");
        return $arFeedback = Pay_Woo_QueryPeriodCreditCardTradeInfo::CheckOut(array_merge($this->Query,array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType)) ,$this->HashKey ,$this->HashIV ,$this->ServiceURL);
    }

    //信用卡關帳/退刷/取消/放棄的方法
    function DoAction() {
		exit("FCBPay.Payment.Integration.Shell.php - TPay_Woo_AllInOne - DoAction");
        return $arFeedback = Pay_Woo_DoAction::CheckOut(array_merge($this->Action,array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType)) ,$this->HashKey ,$this->HashIV ,$this->ServiceURL);
    }

    //合作特店申請撥款
    function AioCapture(){
		exit("FCBPay.Payment.Integration.Shell.php - TPay_Woo_AllInOne - AioCapture");
        return $arFeedback = Pay_Woo_AioCapture::Capture(array_merge($this->Capture,array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType)) ,$this->HashKey ,$this->HashIV ,$this->ServiceURL);
    }

    //查詢信用卡單筆明細紀錄
    function QueryTrade(){
		exit("FCBPay.Payment.Integration.Shell.php - TPay_Woo_AllInOne - QueryTrade");
        return $arFeedback = Pay_Woo_QueryTrade::CheckOut(array_merge($this->Trade,array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType)) ,$this->HashKey ,$this->HashIV ,$this->ServiceURL);
    }

    // 產生訂單(站內付)
    function CreateTrade() {
		exit("FCBPay.Payment.Integration.Shell.php - TPay_Woo_AllInOne - CreateTrade");
        $arParameters = array_merge( array('MerchantID' => $this->MerchantID, 'EncryptType' => $this->EncryptType) ,$this->Send);
        return $arFeedback = Pay_Woo_CreateTrade::CheckOut($arParameters,$this->SendExtend,$this->HashKey,$this->HashIV,$this->ServiceURL);
    }
}

/**
 * cURL 設定值
 */
abstract class ECPay_Woo_Payment_Curl {

    /**
     * @var int 逾時時間
     */
    const TIMEOUT = 30;

}

/**
 * 抽象類
 */
abstract class Pay_Woo_Aio extends Pay_Aio
{

    protected static function ServerPost($Params ,$ServiceURL) {
		exit("FCBPay.Payment.Integration.Shell.php - ECPay_Woo_Aio - ServerPost");
        $fields_string = http_build_query($Params);

        $rs = wp_remote_post($ServiceURL, array(
            'method'      => 'POST',
            'timeout'     => ECPay_Woo_Payment_Curl::TIMEOUT,
            'headers'     => array(),
            'httpversion' => '1.0',
            'sslverify'   => true,
            'body'        => $fields_string
        ));

        if ( is_wp_error($rs) ) {
            throw new Exception($rs->get_error_message());
        }

        return $rs['body'];
    }
}

class Pay_Woo_QueryTradeInfo extends Pay_QueryTradeInfo
{
    protected static function ServerPost($Params ,$ServiceURL)
    {
		exit("FCBPay.Payment.Integration.Shell.php - ECPay_Woo_QueryTradeInfo - ServerPost");
        return Pay_Woo_Aio::ServerPost($Params ,$ServiceURL);
    }
}

class Pay_Woo_QueryPeriodCreditCardTradeInfo extends Pay_QueryPeriodCreditCardTradeInfo
{
    protected static function ServerPost($Params ,$ServiceURL)
    {
		exit("FCBPay.Payment.Integration.Shell.php - ECPay_Woo_QueryPeriodCreditCardTradeInfo - ServerPost");
        return Pay_Woo_Aio::ServerPost($Params ,$ServiceURL);
    }
}

class Pay_Woo_DoAction extends Pay_DoAction
{
    protected static function ServerPost($Params ,$ServiceURL)
    {
		exit("FCBPay.Payment.Integration.Shell.php - ECPay_Woo_DoAction - ServerPost");
        return Pay_Woo_Aio::ServerPost($Params ,$ServiceURL);
    }
}

class Pay_Woo_AioCapture extends Pay_AioCapture
{
    protected static function ServerPost($Params ,$ServiceURL)
    {
		exit("FCBPay.Payment.Integration.Shell.php - ECPay_Woo_AioCapture - ServerPost");
        return Pay_Woo_Aio::ServerPost($Params ,$ServiceURL);
    }
}

class Pay_Woo_QueryTrade extends Pay_QueryTrade
{
    protected static function ServerPost($Params ,$ServiceURL)
    {
		exit("FCBPay.Payment.Integration.Shell.php - ECPay_Woo_QueryTrade - ServerPost");
        return Pay_Woo_Aio::ServerPost($Params ,$ServiceURL);
    }
}

class Pay_Woo_CreateTrade extends Pay_CreateTrade
{
    protected static function ServerPost($Params ,$ServiceURL)
    {
		exit("FCBPay.Payment.Integration.Shell.php - ECPay_Woo_CreateTrade - ServerPost");
        return Pay_Woo_Aio::ServerPost($Params ,$ServiceURL);
    }
}
