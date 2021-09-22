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
		if($inputs['InvoiceFlag'] == "yes")
		{
			$this->sdk->Send['Amount_TaxRate'] = $inputs['Amount_TaxRate'];
			$this->sdk->Send['DonateMark'] = $inputs['DonateMark'];
			$this->sdk->Send['CUSTOMEREMAIL'] = $inputs['CUSTOMEREMAIL'];
			$this->sdk->Send['CarrierId1'] = $inputs['CarrierId1'];
			if($inputs['DonateMark'] == "0")
			{
				if(strlen($inputs['Buyer_Identifier']) > 0)
					$this->sdk->Send['Buyer_Identifier'] = $inputs['Buyer_Identifier'];
				else
					$this->sdk->Send['Buyer_Identifier'] = "00000000";
			}
			else if($inputs['DonateMark'] == "1")
			{
				$this->sdk->Send['NPOBAN'] = $inputs['NPOBAN'];
			}
			//商品明細
		
			$Item = array(
				'Description' => "WOItem",
				'UnitPrice' => $this->sdk->Send['Amount'],
				'Amount'  => $this->sdk->Send['Amount'],
				'Quantity' => 1,
				'TaxType' => '1',
			);
			$this->sdk->SendExtend['ProductDetail'] = json_encode($Item);	
		}
		//var_dump($inputs);
		//echo("</br>");
        
		
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
