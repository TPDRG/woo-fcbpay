<?php

class FCBPayPaymentModuleHelper
{
    /**
     * @var string SDK class name(required)
     */
    protected $sdkClassName = '';

    /**
     * @var string SDK file path(required)
     */
    protected $sdkFilePath = '';

    /**
     * @var bool|null|object SDK object
     */
    protected $sdk = null;

    /**
     * @var array Stage merchant ids
     */
    private $stageMerchantIds = array('2000132', '2000933');

    /**
     * @var string Merchant Id
     */
    private $merchantId = '';

    /**
     * @var string Merchant order number prefix
     */
    private $merchantOrderPrefix = '';

    /**
     * @var string Log directorygetMerchantTradeNo
     */
    private $logDirPath = '';

    /**
     * @var string Log file name
     */
    private $logFileName = '';

    /**
     * @var string Timezone
     */
    private $timezone = 'Asia/Taipei';
	
	private $serviceUrl = '';

    /**
     * ModuleHelper constructor.
     */
    public function __construct()
    {
        $this->setLogDir('C:\xampp\htdocs\wp-content\plugins\fcbpay\LOG');
        $this->setLogFileName('');
        $this->sdk = $this->factory();
        $this->merchantOrderPrefix = $this->getDateTime('ymdHis', '');
    }

    /**
     * Create SDK
     * @return object|bool
     */
    private function factory()
    {
        if (empty($this->sdkClassName) === true) {
            return false;
        }

        if (class_exists($this->sdkClassName, false) === false) {
            require_once($this->sdkFilePath);
        }

        if (empty($this->sdk) === true) {
            return new $this->sdkClassName();
        }

        return false;
    }

    /**
     * Set the exist property value
     * @param $name
     * @param $value
     * @return bool
     */
    private function set($name, $value)
    {
		if (property_exists($this, $name) === true) {
            $this->{$name} = $value;
            return true;
        } else {
            return false;
        }
    }

    /**
     * addNextLine function
     * 加入換行字元
     *
     * @param       String  $content 內容
     * @return      String
     */
    public function addNextLine($content)
    {
        return $content . PHP_EOL;
    }

    /**
     * Set merchant id
     * @param string $merchantId
     * @return bool
     */
    public function setMerchantId($merchantId = '')
    {
        return $this->set('merchantId', $merchantId);
    }

    /**
     * Get merchant id
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * Chang the value to integer
     * @param  mixed $value Value
     * @return integer
     */
    public function toInt($value = 0)
    {
        return intval($value, 10);
    }

    /**
		設定LOG存取路徑
     */
    public function setLogDir($path = '')
    {
		$defaultDirPath = '.';
        if (empty($path) === false) {
            if (file_exists($path) === true) {
                return $this->set('logDirPath', $path);
            } else {
                return $this->set('logDirPath', $defaultDirPath);
            }
        } else {
            return $this->set('logDirPath', $defaultDirPath);
        }
    }

    /**
     * Get the log directory
     * @return string
     */
    public function getLogDir()
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - getLogDir");
        return $this->logDirPath;
    }

    /**
     * Set the log file name
     * @param string $fileName
     * @return bool
     */
    public function setLogFileName($fileName = '')
    {
        $format = 'debug_log_%s.txt';
        $dateString = $this->getDateTime('ymd', '');

        if (empty($fileName) === true) {
            return $this->set('logFileName', sprintf($format, $dateString));
        } else {
            return $this->set('logFileName', $fileName);
        }
    }

    /**
     * Get the log file name
     * @return string
     */
    public function getLogFileName()
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - getLogFileName");
        return $this->logFileName;
    }

    /**
     * Get the full log path
     * @return string
     */
    public function getFullLogPath()
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - getFullLogPath");
        $format = '%s/%s';
        return sprintf($format, $this->getLogDir(), $this->getLogFileName());
    }

    /**
     * Get the log content
     * @param string $content Log content
     * @return string
     */
    public function getLogContent($content = '')
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - getLogContent");
        $format = '%s %s';
        $parseList = array('array', 'object');
        $logDate = $this->getDateTime('Y-m-d H:i:s', '');
        $dataType = gettype($content);
        if (in_array($dataType, $parseList) === true) {
            $logContent = print_r($content, true);
        } else {
            $logContent = $content;
        }
        return sprintf($format, $logDate, $logContent) . PHP_EOL;
    }

    /**
     * Save debug log
     * @param  string $content Log content
     * @return integer
     */
    public function saveDebugLog($content = '')
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - saveDebugLog");
        // Save log
        $logPath = $this->getFullLogPath();
        $logContent = $this->getLogContent($content);
        return file_put_contents($logPath, $logContent, FILE_APPEND);
    }

    /**
     * Filter the inputs
     * @param array $source Source data
     * @param array $whiteList White list
     * @return array
     */
    public function only($source = array(), $whiteList = array())
    {
		$variables = array();

        // Return empty array when do not set white list
        if (empty($whiteList) === true) {
            return $source;
        }

        foreach ($whiteList as $name) {
            if (isset($source[$name]) === true) {
                $variables[$name] = $source[$name];
            } else {
                $variables[$name] = '';
            }
        }
        return $variables;
    }

    /**
     * Check if has empty data
     * @param array $data
     * @return bool
     */
    public function hasEmpty($data = array())
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - hasEmpty");
        foreach ($data as $value) {
            if (empty($value) === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Echo the parameters in json format and exit
     * @param array $parameters Parameters
     */
    public function echoJson($parameters = array())
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - echoJson");
        $json = json_encode($parameters);
        $this->echoAndExit($json);
    }

    /**
     * Echo and exit
     * @param string $message
     */
    public function echoAndExit($message = '')
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - echoAndExit");
        echo $message;
        exit;
    }

    /**
     * Set merchant order number prefix
     * @param string $prefix
     * @return bool
     */
    public function setMerchantOrderPrefix($prefix = '')
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - setMerchantOrderPrefix");
        return $this->set('merchantOrderPrefix', $prefix);
    }

    /**
     * Get merchant order number prefix
     * @return string
     */
    public function getMerchantOrderPrefix()
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - getMerchantOrderPrefix");
        return $this->merchantOrderPrefix;
    }

    /**
     * Set merchant trade number
     * @param  integer $orderId Order id
     * @return string
     */
    public function setMerchantTradeNo($orderId = 0)
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - setMerchantTradeNo");
        $merchantId = $this->getMerchantId();
        return strval($orderId);
    }

    /**
     * Get merchant trade number
     * @param  integer $orderId Order id
     * @return string
     */
    public function getMerchantTradeNo($merchantTradeNo = 0)
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - getMerchantTradeNo");
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
     * Get the length of merchant order number prefix
     * @return int
     */
    public function getMerchantOrderPrefixLength()
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - getMerchantOrderPrefixLength");
        return strlen($this->getMerchantOrderPrefix());
    }

    /**
     * Set timezone
     * @param  string $timezone
     * @return string
     */
    public function setTimezone($timezone)
    {
		return $this->set('timezone', $timezone);
    }

    /**
     * Get timezone
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Get the unixtime
     * @param  string $dateString Date string
     * @return integer
     */
    public function getUnixTime($dateString = '')
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - getUnixTime");
        return strtotime($dateString);
    }

    /**
		取得日期時間
     */
    public function getDateTime($pattern = 'Y-m-d H:i:s', $dateString = '')
    {
        // 更改時區
        $cacheTimezone = date_default_timezone_get();
        date_default_timezone_set($this->getTimezone());

        // 取得日期時間
        if ($dateString !== '') {
            $dateString = date($pattern, $this->getUnixTime($dateString));
        } else {
            $dateString = date($pattern);
        }

        // 復原時區
        date_default_timezone_set($cacheTimezone);

        return $dateString;
    }

    /**
     * Get the amount
     * @param  mixed $amount Amount
     * @return integer
     */
    public function getAmount($amount = 0)
    {
		return round($amount, 0);
    }

    /**
     * Validate the amounts
     * @param  mixed $source Source amount
     * @param  mixed $target Target amount
     * @return boolean
     */
    public function validAmount($source = 0, $target = 0)
    {
		return ($this->getAmount($source) === $this->getAmount($target));
    }

    /**
     * responseSuccess function
     * 接收API回應-成功
     *
     * @return string
     */
    public function responseSuccess()
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - responseSuccess");
        exit('1|OK');
    }

    /**
     * responseError function
     * 接收API回應-失敗
     *
     * @return string
     */
    public function responseError($msg)
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - responseError");
        exit('0|' . $msg);
    }

    /**
     * isHttps function
     * 判斷URL是否為 https
     *
     * @param  string $site 判斷的URL
     * @return bool
     */
    public function isHttps($site)
    {
		exit("FCBPayPaymentModuleHelper.php - FCBPayPaymentModuleHelper - isHttps");
        if (strpos($site,"https://") !== false) {
            return true;
        } else {
            return false;
        }
    }
	
	public function setPayServerUrl($Url = '')
    {
        return $this->set('serviceUrl', $Url);
    }
	public function getPayServerUrl($Url = '')
    {
		return $this->serviceUrl;
    }
	public function calInAcc($orderId,$checkType,$CAcc,$CAmount)
	{
		
		$result = "";
		$CVal = "";
		$n = 11;
		if(substr($CAcc, 0, 1) == "4")
		{
			$n = 9;
			$CAcc = "00".$CAcc;
		}
		if(strlen($CAmount) < 8) {
			  $CAmount = str_pad($CAmount,8,"0",STR_PAD_LEFT); 
		}
		$ValueMultiplier = "";
		$AmountMultiplier = "";
		$X1 = 0;
		$X2 = 0;
		switch (strval($checkType)) {
            case "5":
				$n = intval($n) - 1;
				$oVal = $orderId;
				if(strlen($oVal) > $n)
					$oVal = substr($oVal, intval($n)* -1);
				$CVal = $CAcc.str_pad($oVal,$n,"0",STR_PAD_LEFT); 
				$ValueMultiplier = "371371371371371";
				$AmountMultiplier = "87654321";
				for ( $i=0 ; $i<15 ; $i++ ) {
					$str = $CVal[$i];
					$MultiplierStr = $ValueMultiplier[$i];
					$A = intval($str) * intval($MultiplierStr);
					$X1 = intval($X1) + $A%10;
				}
				$X1 = $X1%10;
				for ( $i=0 ; $i<8 ; $i++ ) {
					$str = $CAmount[$i];
					$MultiplierStr = $AmountMultiplier[$i];
					$A = intval($str) * intval($MultiplierStr);
					$X2 = intval($X2) + $A%10;
				}
				$X2 = $X2%10;
				$X3 = ($X1 + $X2)%10;
				$P = (10-$X3)%10;
				$result = $CVal.$P;
				break;
			case "7":
				$n = intval($n) - 2;
				$oVal = $orderId;
				if(strlen($oVal) > $n)
					$oVal = substr($oVal, intval($n)* -1);
				$CVal = $CAcc.str_pad($oVal,$n,"0",STR_PAD_LEFT); 
				$ValueMultiplier = "37137137137137";
				$AmountMultiplier = "37137137";
			
				for ( $i=0 ; $i<14 ; $i++ ) {
					$str = $CVal[$i];
					$MultiplierStr = $ValueMultiplier[$i];
					$A = intval($str) * intval($MultiplierStr);
					$X1 = intval($X1) + $A%10;
				}
				$X1 = $X1%10;
			
				for ( $i=0 ; $i<8 ; $i++ ) {
					$str = $CAmount[$i];
					$MultiplierStr = $AmountMultiplier[$i];
					$A = intval($str) * intval($MultiplierStr);
					$X2 = intval($X2) + $A%10;
				}
				$X2 = $X2%10;
				$X3 = ($X1 + $X2)%10;
				$O = (10-$X3)%10;
				$P = (10-$X2)%10;
				$result = $CVal.$O.$P;
				break;
			case "A":
				$n = intval($n) - 2;
				$oVal = $orderId;
				if(strlen($oVal) > $n)
					$oVal = substr($oVal, intval($n)* -1);
				$CVal = $CAcc.str_pad($oVal,$n,"0",STR_PAD_LEFT); 
				$ValueMultiplier = "37137137137137";
				$AmountMultiplier = "13713713";
			
				for ( $i=0 ; $i<14 ; $i++ ) {
					$str = $CVal[$i];
					$MultiplierStr = $ValueMultiplier[$i];
					$A = intval($str) * intval($MultiplierStr);
					$X1 = intval($X1) + $A%10;
				}
				for ( $i=0 ; $i<8 ; $i++ ) {
					$str = $CAmount[$i];
					$MultiplierStr = $AmountMultiplier[$i];
					$A = intval($str) * intval($MultiplierStr);
					$X2 = intval($X2) + $A%10;
				}
				$Y1 = ($X1 + $X2)%10;

				$ValueMultiplier = "87654321876543";
				$AmountMultiplier = "21876543";
				$X1 = 0;
				$X2 = 0;
				for ( $i=0 ; $i<14 ; $i++ ) {
					$str = $CVal[$i];
					$MultiplierStr = $ValueMultiplier[$i];
					$A = intval($str) * intval($MultiplierStr);
					$X1 = intval($X1) + $A%10;
				}
				for ( $i=0 ; $i<8 ; $i++ ) {
					$str = $CAmount[$i];
					$MultiplierStr = $AmountMultiplier[$i];
					$A = intval($str) * intval($MultiplierStr);
					$X2 = intval($X2) + $A%10;
				}
				$Y2 = ($X1 + $X2)%10;
				$O = (10-$Y1)%10;
				$P = (10-$Y2)%10;
				$result = $CVal.$O.$P;
				break;
			case "8":
				$oVal = $orderId;
				if(strlen($oVal) > $n)
					$oVal = substr($oVal, intval($n)* -1);
				$CVal = $CAcc.str_pad($oVal,$n,"0",STR_PAD_LEFT); 
				$result = $CVal;
				break;
            default:
                throw new Exception('Invalid checkType.');
                break;
        }
		return $result;
	}
}
