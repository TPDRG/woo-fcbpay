<?php
require_once(dirname( __FILE__ ) . '/FCBPayPaymentModuleHelper.php');

class FCBPayPaymentHelper extends FCBPayPaymentModuleHelper
{
    /**
     * @var string SDK class name(required)
     */
    private $prefix = 'ecpay';

    /**
     * @var string SDK class name(required)
     */
    protected $sdkClassName = 'FCBPay_Woo_AllInOne';

    /**
     * @var string SDK file path(required)
     */
    protected $sdkFilePath = 'FCBPay.Payment.Integration.Shell.php';

    /**
     * @var string Service provider
     */
    private $provider = 'ECPay';

    /**
     * @var int Encrypt type
     */
    private $encryptType = ''; // Encrypt type

    /**
     * @var array Service Urls
     */
    private $serviceUrls = array(
        'prod' => '',
        'stage' => '',
    );

    /**
     * @var array API success return code
     */
    private $successCodes = array(
            'payment' => 1,
            'atmGetCode' => 2,
            'cvsGetCode' => 10100073,
            'barcodeGetCode' => 10100073,
    );

    /**
     * @var array 綠界付款方式
     */
    public $ecpayPayment = array('ecpay', 'ecpay_dca');

    /**
     * @var array FCB Pay
     */
    public $PaymentMethods = array(
        TPay_PaymentMethod::CREDIT   ,
        TPay_PaymentMethod::CREDIT_3 ,
        TPay_PaymentMethod::CREDIT_6 ,
        TPay_PaymentMethod::CREDIT_9 ,
        TPay_PaymentMethod::CREDIT_12 ,
        TPay_PaymentMethod::CREDIT_15 ,
        TPay_PaymentMethod::CREDIT_18 ,
        TPay_PaymentMethod::CREDIT_24 ,
        TPay_PaymentMethod::CREDIT_30 ,
        TPay_PaymentMethod::CREDIT_REWARD ,
        TPay_PaymentMethod::JKOS ,
        TPay_PaymentMethod::CS ,
        TPay_PaymentMethod::eATM ,
        TPay_PaymentMethod::REG ,
        TPay_PaymentMethod::IDP ,
        TPay_PaymentMethod::TWPAY ,
        TPay_PaymentMethod::UNION ,
        TPay_PaymentMethod::WECHAT

    );

    /**
     * @var array 是否到期
     */
    public $isExpire = array(
        'yes' => 'Y',
        'no' => 'N',
    );

    /**
     * @var array 交易狀態代碼
     */
    public $tradeStatusCodes = array(
        'notFoundTradeData'  => '10200047',
        'emptyPaymentMethod' => '10200095',
    );

    /**
     * @var array 訂單狀態
     */
    public $orderStatus = array(
        'pending'    => '', // 等待付款
        'processing' => '', // 處理中(已付款)
        'onHold'     => '', // 保留
        'cancelled'  => '', // 取消
        'Pay'        => '',   // 已付款
    );

    /**
     * @var array 提示訊息
     */
    public $msg = array(
        'unpaidOrder'     => 'Unpaid order cancelled - time limit reached.', // 未付款訂單已取消 - 付款期限已過。
        'invalidPayment'  => 'Invalid payment method.',                      // 無效的付款方式.
        'testOrderPrefix' => 'Test order will add date as prefix.',          // 測試訂單將加上日期作為前綴
        'simulatePaid'    => 'Simulate paid, update the note only.',         // 模擬付款，僅更新備註。
    );

    /**
     * ECPayPaymentHelper constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * checkoutPrepare
     * @param  array $data The data for checkout
     * @return void
     */
    private function checkoutPrepare($data)
    {
		$inputs = $data;
        // Set SDK parameters
		$this->sdk->Send['PlatFormId'] = $this->getMerchantId();
		$this->sdk->Send['PayType'] = $this->getPaymentMethod($inputs['PayType']);
		$this->sdk->Send['OrderId'] = $inputs['orderId'];
		$this->sdk->Send['Amount'] = $this->getAmount($inputs['total']);
		$this->sdk->Send['CreateTime'] = $this->getDateTime('Y/m/d H:i:s', '');
		$this->sdk->Send['TransTime'] = $this->getDateTime('Y/m/d H:i:s', '');
		$this->sdk->Send['ResURL'] = $inputs['returnUrl'];
        $this->sdk->Send['hashK'] = $inputs['hashK'];
        $this->sdk->ServiceURL = $this->getPayServerUrl();
		
		//var_dump($inputs);
        //商品明細
		/*
        $this->sdk->Send['Items'][] = array(
            'Name' => $inputs['itemName'],
            'Price' => $this->sdk->Send['Amount'],
            'Currency'  => $inputs['currency'],
            'Quantity' => 1,
            'URL' => '',
        );
		*/
        // 針對支付種類加屬性
        switch ($this->sdk->Send['PayType']) {
			case "UNION":
            case "CREDIT":
				$this->sdk->SendExtend['TransType'] = '1';
				$this->sdk->SendExtend['TimeoutSecs'] = '60';
				break;
			case "CREDIT_3":
			case "CREDIT_6":
			case "CREDIT_9":
			case "CREDIT_12":
			case "CREDIT_15":
			case "CREDIT_18":
			case "CREDIT_24":
			case "CREDIT_30":
				$this->sdk->SendExtend['TransType'] = '2';
				$this->sdk->SendExtend['PeriodNum'] = str_replace("CREDIT_","",$this->sdk->Send['PayType']);
				$this->sdk->SendExtend['TimeoutSecs'] = '60';
				$this->sdk->Send['PayType'] = 'CREDIT';
				break;
			case "CREDIT_REWARD":
				$this->sdk->SendExtend['TransType'] = '3';
				$this->sdk->SendExtend['BonusActionCode'] = $inputs['BonusActionCode'];
				$this->sdk->SendExtend['TimeoutSecs'] = '60';
				$this->sdk->Send['PayType'] = 'CREDIT';
                break;
            case 'CS':
				if($this->getAmount($inputs['total']) > 60000)
				{
					throw new Exception("四大超商僅限6萬以下");
				}
				else if($this->getAmount($inputs['total']) > 40000)
				{	if(strlen($inputs['CSInAccountNo3']) < 5)
					{
						throw new Exception("四大超商交易異常");
					}
					else if(strlen($inputs['CSInAccountNo3']) > 5)
					{
						$this->sdk->SendExtend['InAccountNo'] = $inputs['CSInAccountNo3'].str_pad($inputs['orderId'],9,"0",STR_PAD_LEFT); 
					}
					else
					{
						$this->sdk->SendExtend['InAccountNo'] = $inputs['CSInAccountNo3'].str_pad($inputs['orderId'],11,"0",STR_PAD_LEFT); 
					}
				}
				else if($this->getAmount($inputs['total']) > 20000)
				{	if(strlen($inputs['CSInAccountNo2']) < 5)
					{
						throw new Exception("四大超商交易異常");
					}
					else if(strlen($inputs['CSInAccountNo2']) > 5)
					{
						$this->sdk->SendExtend['InAccountNo'] = $inputs['CSInAccountNo2'].str_pad($inputs['orderId'],9,"0",STR_PAD_LEFT); 
					}
					else
					{
						$this->sdk->SendExtend['InAccountNo'] = $inputs['CSInAccountNo2'].str_pad($inputs['orderId'],11,"0",STR_PAD_LEFT); 
					}
				}
				else
				{	if(strlen($inputs['CSInAccountNo1']) < 5)
					{
						throw new Exception("四大超商交易異常");
					}
					else if(strlen($inputs['CSInAccountNo1']) > 5)
					{
						$this->sdk->SendExtend['InAccountNo'] = $inputs['CSInAccountNo1'].str_pad($inputs['orderId'],9,"0",STR_PAD_LEFT); 
					}
					else
					{
						$this->sdk->SendExtend['InAccountNo'] = $inputs['CSInAccountNo1'].str_pad($inputs['orderId'],11,"0",STR_PAD_LEFT); 
					}
				}
				if($this->sdk->SendExtend['InAccountNo'] > 16)
					$this->sdk->SendExtend['InAccountNo'] = substr($this->sdk->SendExtend['InAccountNo'], 0, 16);
				break;
			case "WECHAT":
				$this->sdk->SendExtend['Terminal'] = $inputs['Terminal'];
				break;
			case "REG":
			case "EATM":
				if($inputs['Apply'] == "yes")
					$Apply = "Y";
				else
					$Apply = "";
				$this->sdk->SendExtend['InAccountNo'] = $this->calInAcc($inputs['orderId'],$inputs['checkType'],$inputs['InAccountNo'],$this->getAmount($inputs['total']));
				$this->sdk->SendExtend['Apply'] = $Apply;
				$this->sdk->SendExtend['DueDate'] = $this->getDateTime('Ymd', '');
				break;
			case "IDP":
				$this->sdk->SendExtend['InAccountNo'] = $this->calInAcc($inputs['orderId'],$inputs['checkType'],$inputs['InAccountNo'],$this->getAmount($inputs['total']));
				$this->sdk->SendExtend['OutAccountNo'] = $inputs['OutAccountNo'];
				$this->sdk->SendExtend['OutBank'] = $inputs['OutBank'];
				$this->sdk->SendExtend['ID'] = $inputs['ID'];	
				break;
			case "JKOS":
			case "TWPAY":
				break;
            default:
                throw new Exception('Invalid payment method.');
                break;
        }
    }

    /**
     * Checkout
     * @param  array $data The data for checkout
     * @return void
     * @throws Exception
     */
    public function checkout($data)
    {
		$this->checkoutPrepare($data);
        $this->sdk->CheckOut();
    }

    /**
     * 反查綠界訂單-取消過期訂單
     *
     * @param  array $data
     * @return array
     */
    public function expiredOrder($data)
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - expiredOrder");
        // Filter inputs
        $whiteList = array(
            'hashKey'            ,
            'hashIv'             ,
            'orderId'            ,
            'holdStockMinute'    ,
            'orderStatus'        ,
            'paymentMethod'      ,
            'ecpayPaymentMethod' ,
            'createDate'         ,
            'stageOrderPrefix'   ,
        );
        $inputs = $this->only($data, $whiteList);

        $feedback = array();

        // 確認付款方式為'綠界'且訂單狀態為'等待付款中'或'取消'
        if (in_array($inputs['paymentMethod'], $this->ecpayPayment) && ($inputs['orderStatus'] == $this->getOrderStatusPending() || $inputs['orderStatus'] == $this->getOrderStatusCancelled())) {

            // 計算訂單建立時間是否超過指定時間
            if (strpos($inputs['ecpayPaymentMethod'], "Credit") === false) {
                $offset =  30; // 非信用卡
            } else {
                $offset =  60; // 信用卡
            }

            // 若使用者自訂的保留時間 > 綠界時間，則使用使用者設定的時間
            if ($inputs['holdStockMinute'] > $offset) {
                $offset = $inputs['holdStockMinute'];
            }

            // 比對時間，使用 Unix time 比對
            $createDate  = $inputs['createDate'];
            $dateCompare = $this->getUnixTime('- '. $offset .' minute');

            if ($createDate <= $dateCompare) {

                // 反查綠界訂單記錄API
                $merchantTradeNo = $inputs['orderId'];

                $data = array(
                    'hashKey'         => $inputs['hashKey'],
                    'hashIv'          => $inputs['hashIv'],
                    'merchantTradeNo' => $merchantTradeNo,
                );
                $feedback =  $this->getTradeInfo($data);
            }
        }

        return $feedback;
    }

    /**
     * Get checkout form
     * @param  array $data The data for checkout
     * @return void
     * @throws Exception
     */
    public function getCheckoutForm($data)
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getCheckoutForm");
        $this->checkoutPrepare($data);
        return $this->sdk->CheckOutString();
    }

    /**
     * Get valid feedback
     * @param  array $data The data for getting AIO feedback
     * @return array
     * @throws Exception
     */
    public function getValidFeedback($data)
    {
		
        $feedback = $this->getFeedback($data); // feedback
        return $feedback;
    }

    /**
     * Get the order id from AIO merchant trade number
     * @param  string $merchantTradeNo AIO merchant trade number
     * @return string|false
     */
    public function getOrderId($merchantTradeNo = '')
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getOrderId");
        // Filter inputs
        if (empty($merchantTradeNo) === true) {
            return false;
        }
        unset($inputs);

        $merchantId = $this->getMerchantId();
        $orderId = $merchantTradeNo;
        return $orderId;
    }

    /**
     * Get AIO response state
     * @param  array $feedback  AIO feedback
     * @param  array $orderInfo Order info
     * @return integer
     * @throws Exception
     */
    public function getResponseState($feedback = array(), $orderInfo = array())
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getResponseState");
        // Filter inputs
        $whiteList = array(
            'PaymentType',
            'SimulatePaid',
            'RtnCode',
            'RtnMsg',
        );
        $inputFeedback = $this->only($feedback, $whiteList);
        unset($whiteList);

        $whiteList = array(
            'validState',
            'orderId',
        );
        $inputOrder = $this->only($orderInfo, $whiteList);
        unset($whiteList);

        // Set parameters
        $orderId = $inputOrder['orderId'];
        $validState = $inputOrder['validState'];
        $paymentMethod = $this->getPaymentMethod($inputFeedback['PaymentType']);
        $paymentFailed = $this->getPaymentFailed($orderId, $inputFeedback);
        $getSuccessData = array(
            'validState' => $validState,
            'simulatePaid' => $inputFeedback['SimulatePaid'],
        );
        unset($inputOrder);

        // Check the response state
        //   1:Paid
        //   2:ATM get code
        //   3:CVS get code
        //   4:BARCODE get code
        //   5:State error
        //   6:Simulate Paid
        switch($paymentMethod) {
            case $this->getSdkPaymentMethod('credit'):
            case $this->getSdkPaymentMethod('unionpay'):
            case $this->getSdkPaymentMethod('webatm'):
            case $this->getSdkPaymentMethod('androidpay'):
            case $this->getSdkPaymentMethod('googlepay'):
                if ($this->isSuccess($inputFeedback, 'payment') === true) {
                    $responseState = $this->getSuccessState($getSuccessData);
                    if ($responseState === false) {
                        throw new Exception($paymentFailed);
                    }
                } else {
                    throw new Exception($paymentFailed);
                }
                break;
            case $this->getSdkPaymentMethod('atm'):
                if ($this->isSuccess($inputFeedback, 'payment') === true) {
                    $responseState = $this->getSuccessState($getSuccessData);
                    if ($responseState === false) {
                        throw new Exception($paymentFailed);
                    }
                } elseif ($this->isSuccess($inputFeedback, 'atmGetCode') === true) {
                    $responseState = 2; // ATM get code
                } else {
                    throw new Exception($paymentFailed);
                }
                break;
            case $this->getSdkPaymentMethod('cvs'):
                if ($this->isSuccess($inputFeedback, 'payment') === true) {
                    $responseState = $this->getSuccessState($getSuccessData);
                    if ($responseState === false) {
                        throw new Exception($paymentFailed);
                    }
                } elseif ($this->isSuccess($inputFeedback, 'cvsGetCode') === true) {
                    $responseState = 3; // CVS get code
                } else {
                    throw new Exception($paymentFailed);
                }
                break;
            case $this->getSdkPaymentMethod('barcode'):
                if ($this->isSuccess($inputFeedback, 'payment') === true) {
                    $responseState = $this->getSuccessState($getSuccessData);
                    if ($responseState === false) {
                        throw new Exception($paymentFailed);
                    }
                } elseif ($this->isSuccess($inputFeedback, 'barcodeGetCode') === true) {
                    $responseState = 4; // Barcode get code
                } else {
                    throw new Exception($paymentFailed);
                }
                break;
            default:
                throw new Exception($this->getInvalidPayment($orderId));
        }
        return $responseState;
    }

    /**
     * Get payment success message
     * @param  string $pattern  Message pattern
     * @param  array  $feedback AIO feedback
     * @return string
     */
    public function getPaymentSuccessComment($pattern = '', $feedback = array())
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getPaymentSuccessComment");
        // Filter inputs
        if (empty($pattern) === true) {
            return false;
        }

        $list = array(
            'RtnCode',
            'RtnMsg',
            'PaymentType',
        );
        $inputs = $this->only($feedback, $list);
        if ($this->hasEmpty($inputs) === true) {
            return false;
        }

        // Set the parameters
        $paymentType = $this->getFeedbackPaymentType($inputs['PaymentType']);
        $paymentMethod = $this->getPaymentMethod($paymentType);
        unset($paymentType);

        return sprintf(
            $pattern,
            $paymentMethod,
            $inputs['RtnCode'],
            $inputs['RtnMsg']
        );
    }

    /**
     * Get obtaining code comment
     * @param  string $pattern  Message pattern
     * @param  string  $error    Error message
     * @return string|boolean
     */
    public function getFailedComment($pattern = '', $error = '')
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getFailedComment");
        if (empty($pattern) === true) {
            return false;
        }

        if (empty($error) === true) {
            return false;
        }

        return sprintf($pattern, $error);
    }

    /**
     * Get the feedback payment type option
     * @param  string  $paymentType AIO payment type
     * @return string
     */
    public function getFeedbackPaymentType($paymentType = '')
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getFeedbackPaymentType");
        $pieces = explode('_', $paymentType);
        return strtolower($pieces[0]);
    }

    /**
     * Get obtaining code comment
     * @param  string $pattern  Message pattern
     * @param  array  $feedback AIO feedback
     * @return string
     */
    public function getObtainingCodeComment($pattern = '', $feedback = array())
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getObtainingCodeComment");
        // Filter inputs
        $undefinedMessage = 'undefined';
        if (empty($pattern) === true) {
            return $undefinedMessage;
        }

        $list = array(
            'PaymentType',
            'RtnCode',
            'RtnMsg',
            'BankCode',
            'vAccount',
            'ExpireDate',
            'PaymentNo',
            'Barcode1',
            'Barcode2',
            'Barcode3',
        );
        $inputs = $this->only($feedback, $list);

        $type = $this->getPaymentMethod($inputs['PaymentType']);
        switch($type) {
            case 'ATM':
                return sprintf(
                    $pattern,
                    $inputs['RtnCode'],
                    $inputs['RtnMsg'],
                    $inputs['BankCode'],
                    $inputs['vAccount'],
                    $inputs['ExpireDate']
                );
                break;
            case 'CVS':
                return sprintf(
                    $pattern,
                    $inputs['RtnCode'],
                    $inputs['RtnMsg'],
                    $inputs['PaymentNo'],
                    $inputs['ExpireDate']
                );
                break;
                case 'BARCODE':
                    return sprintf(
                        $pattern,
                        $inputs['RtnCode'],
                        $inputs['RtnMsg'],
                        $inputs['ExpireDate'],
                        $inputs['Barcode1'],
                        $inputs['Barcode2'],
                        $inputs['Barcode3']
                    );
                    break;
            default:
                break;
        }
        return $undefinedMessage;
    }

    /**
     * Filter the specific character
     * @param  string $url URL
     * @return string
     */
    private function filterUrl($url)
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - filterUrl");
        return str_replace('&amp;', '&', $url);
    }

    /**
     * Get the module description
     * @param  string $cartName Cart name
     * @return string
     */
    private function getModuleDescription($cartName = '')
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getModuleDescription");
        return strtolower($this->provider) . '_module_' . strtolower($cartName);
    }

    /**
     * Get SDK payment method
     * @param  string $paymentType payment type
     * @return string|bool
     */
    private function getSdkPaymentMethod($paymentType = '')
    {
        // Filter inputs
        if (empty($paymentType) === true) {
            return false;
        }

        $lower = strtolower($paymentType);
        switch ($lower) {
            case 'all':
                $sdkPayment = "ALL";
                break;
            case 'credit':
                $sdkPayment = "CREDIT";
                break;
            case 'credit_3':
                $sdkPayment = "CREDIT_3";
                break;
			case 'credit_6':
                $sdkPayment = "credit_6";
                break;
			case 'credit_9':
                $sdkPayment = "credit_9";
                break;
			case 'credit_12':
                $sdkPayment = "credit_12";
                break;
			case 'credit_15':
                $sdkPayment = "credit_15";
                break;
			case 'credit_18':
                $sdkPayment = "credit_18";
                break;
			case 'credit_24':
                $sdkPayment = "credit_24";
                break;
			case 'credit_30':
                $sdkPayment = "credit_30";
                break;
			case 'credit_reward':
                $sdkPayment = "CREDIT_REWARD";
                break;
            case 'union':
                $sdkPayment = "UNION";
                break;
            case 'idp':
                $sdkPayment = "IDP";
                break;
			case 'eatm':
                $sdkPayment = "EATM";
                break;
			case 'reg':
                $sdkPayment = "REG";
                break;
			case 'cs':
                $sdkPayment = "CS";
                break;
			case 'wechat':
                $sdkPayment = "WECHAT";
                break;
			case 'twpay':
                $sdkPayment = "TWPAY";
                break;
			case 'jkos':
                $sdkPayment = "JKOS";
                break;
            default:
                $sdkPayment = '';
                break;
        }
        return $sdkPayment;
    }

    /**
     * Get SDK NeedExtraPaidInfo option
     * @param  string  $type Type
     * @return string
     */
    private function getSdkExtraPaymentInfoOption($type = '')
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getSdkExtraPaymentInfoOption");
        if ($type === 'Y') {
            return Pay_ExtraPaymentInfo::Yes;
        }
        return Pay_ExtraPaymentInfo::No;
    }

    /**
     * Get the credit installment
     * @param  string $paymentType Payment type
     * @return integer|bool
     */
    private function getInstallment($paymentType = '')
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getInstallment");
        // Filter inputs
        if (empty($paymentType) === true) {
            return false;
        }

        $pieces = explode('_', $paymentType);

        return $pieces;
    }

    /**
     * Get the payment method from the payment type
     * @param  string $paymentType Payment type
     * @return string|bool
     */
    public function getPaymentMethod($paymentType = '')
    {
        // Filter inputs
        if (empty($paymentType) === true) {
            return false;
        }

        return $this->getSdkPaymentMethod($paymentType);
    }

    /**
     * Get the feedback
     * @param  array $data The data for the feedback
     * @return mixed
     * @throws Exception
     */
    public function getFeedback($data)
    {
		// Filter inputs
        $whiteList = array(
            'hashKey'
        );
        $inputs = $this->only($data, $whiteList);

        // Set SDK parameters
        $this->sdk->MerchantID = $this->getMerchantId();
        $this->sdk->HashKey = $inputs['hashKey'];
        try {
            $feedback = $this->sdk->CheckOutFeedback();
        } catch (Exception $e) {
            $error = $e->getMessage();
            throw new Exception ($error);
        }

        if (count($feedback) < 1) {
            throw new Exception($this->provider . ' feedback is empty.');
        }
        return $feedback;
    }

    /**
     * Get the trade info
     * @param  array $feedback AIO feedback
     * @param  array $data     The data for querying aio trade info
     * @return array
     * @throws Exception
     */
    public function getTradeInfo($data)
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getTradeInfo");
        // Filter inputs
        $whiteList = array(
            'hashKey',
            'hashIv',
            'merchantTradeNo',
        );
        $inputs = $this->only($data, $whiteList);

        // Set SDK parameters
        $this->sdk->MerchantID = $this->getMerchantId();
        $this->sdk->HashKey = $inputs['hashKey'];
        $this->sdk->HashIV = $inputs['hashIv'];
        $this->sdk->ServiceURL = $this->getUrl('queryTrade');
        $this->sdk->EncryptType = $this->encryptType;
        $this->sdk->Query['MerchantTradeNo'] = $inputs['merchantTradeNo'];
        $info = $this->sdk->QueryTradeInfo();
        if (count($info) < 1) {
            throw new Exception($this->provider . ' trade info is empty.');
        }
        return $info;
    }

    /**
     * Check AIO feedback state
     * @param  array   $feedback AIO feedback
     * @param  string  $type     Feedback type
     * @return bool
     */
    private function isSuccess($feedback, $type)
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - isSuccess");
        // Filter inputs
        $whiteList = array(
            'RtnCode',
        );
        $inputs = $this->only($feedback, $whiteList);
        if ($this->hasEmpty($inputs) === true) {
            return false;
        }

        return ($this->toInt($feedback['RtnCode']) === $this->toInt($this->successCodes[$type]));
    }

    /**
     * Get payment failed message
     * @param  mixed $orderId  Order id
     * @param  array $feedback AIO feedback
     * @return string|bool
     */
    private function getPaymentFailed($orderId = 0, $feedback = array())
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getPaymentFailed");
        // Filter inputs
        if (empty($orderId) === true) {
            return false;
        }

        $whiteList = array(
            'RtnCode',
            'RtnMsg'
        );
        $inputs = $this->only($feedback, $whiteList);
        if ($this->hasEmpty($inputs) === true) {
            return false;
        }

        return sprintf('Order %s Exception.(%s: %s)', $orderId, $inputs['RtnCode'], $inputs['RtnMsg']);
    }

    /**
     * Get success state
     * @param array $data Check data
     * @return bool|int
     */
    private function getSuccessState($data = array())
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getSuccessState");
        // Filter inputs
        $whiteList = array(
            'validState',
            'simulatePaid'
        );
        $inputs = $this->only($data, $whiteList);

        if ($inputs['validState'] === true) {
            if ($this->toInt($inputs['simulatePaid']) === 0) {
                $responseState = 1; // Paid
            } else {
                $responseState = 6; // Simulate Paid
            }
        } else {
            $responseState = 5; // State error
        }
        return $responseState;
    }

    /**
     * Get invalid payment message
     * @param  mixed   $orderId  Order id
     * @return string|boolean
     */
    private function getInvalidPayment($orderId = 0)
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getInvalidPayment");
        // Filter inputs
        if (empty($orderId) === true) {
            return false;
        }

        return sprintf('Order %s, payment method is invalid.', $orderId);
    }

    /**
     * getOrderStatusPending function
     * 取得購物車訂單狀態 - 等待付款
     *
     * @return string 等待付款
     */
    public function getOrderStatusPending()
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getOrderStatusPending");
        return $this->orderStatus['pending'];
    }

    /**
     * getOrderStatusProcessing function
     * 取得購物車訂單狀態 - 處理中(已付款)
     *
     * @return string 處理中(已付款)
     */
    public function getOrderStatusProcessing()
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getOrderStatusProcessing");
        return $this->orderStatus['processing'];
    }

    /**
     * getOrderStatusOnHold function
     * 取得購物車訂單狀態 - 保留
     *
     * @return string 保留
     */
    public function getOrderStatusOnHold()
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getOrderStatusOnHold");
        return $this->orderStatus['onHold'];
    }

    /**
     * getOrderStatusCancelled function
     * 取得購物車訂單狀態 - 取消
     *
     * @return string 取消
     */
    public function getOrderStatusCancelled()
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getOrderStatusCancelled");
        return $this->orderStatus['cancelled'];
    }

    /**
     * getOrderStatusEcpay function
     * 取得購物車訂單狀態 - ECPay Shipping
     *
     * @return string ECPay Shipping
     */
    public function getOrderStatusEcpay()
    {
		exit("FCBPayPaymentHelper.php - FCBPayPaymentHelper - getOrderStatusEcpay");
        return $this->orderStatus['ecpay'];
    }

    /**
     * setOrderStatus function
     * 設定購物車訂單狀態 - 全部
     *
     * @param  array $data
     * @return void
     */
    public function setOrderStatus($data)
    {
        $status = array('Pending', 'Processing', 'OnHold', 'Cancelled', 'Pay');

        foreach($status as $value) {
            $funName = 'setOrderStatus' . $value; // 組合 function name
            $this->$funName($data[$value]);
        }
    }

    /**
     * setOrderStatusPending function
     * 設定購物車訂單狀態 - 等待付款
     *
     * @param  string $value 要儲存的值
     * @return void
     */
    public function setOrderStatusPending($value)
    {
        $this->orderStatus['pending'] = $value;
    }

    /**
     * setOrderStatusProcessing function
     * 設定購物車訂單狀態 - 處理中(已付款)
     *
     * @param  string $value 要儲存的值
     * @return void
     */
    public function setOrderStatusProcessing($value)
    {
        $this->orderStatus['processing'] = $value;
    }

    /**
     * setOrderStatusOnHold function
     * 設定購物車訂單狀態 - 保留
     *
     * @param  string $value 要儲存的值
     * @return void
     */
    public function setOrderStatusOnHold($value)
    {
        $this->orderStatus['onHold'] = $value;
    }

    /**
     * setOrderStatusCancelled function
     * 設定購物車訂單狀態 - 取消
     *
     * @param  string $value 要儲存的值
     * @return void
     */
    public function setOrderStatusCancelled($value)
    {
        $this->orderStatus['cancelled'] = $value;
    }

    /**
     * setOrderStatusEcpay function
     * 設定購物車訂單狀態 - pay
     *
     * @param  string $value 要儲存的值
     * @return void
     */
    public function setOrderStatuspay($value)
    {
        $this->orderStatus['pay'] = $value;
    }
}
