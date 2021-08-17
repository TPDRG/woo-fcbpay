<?php


/**
 * 訂單新增備註mail通知信(0:關閉/1:啟用)
 */
abstract class FCBPay_OrderNoteEmail
{
    const PAYMENT_METHOD                 = 1; // 付款方式
    const PAYMENT_RESULT_CREDIT          = 1; // 付款結果-信用卡
    const PAYMENT_RESULT_WEB_ATM         = 1; // 付款結果-WebATM
    const PAYMENT_INFO_ATM               = 1; // 取號結果-ATM
    const PAYMENT_RESULT_ATM             = 1; // 付款結果-信用卡
    const PAYMENT_INFO_CVS_AND_BARCODE   = 1; // 取號結果-超商代碼/超商條碼
    const PAYMENT_RESULT_CVS_AND_BARCODE = 1; // 付款結果-超商代碼/超商條碼
    const PAYMENT_RESULT_EXCEPTION       = 1; // 付款結果-錯誤訊息
    const CONFIRM_ORDER                  = 1; // 訂單完成
    const CANCEL_ORDER                   = 1; // 訂單取消
}

/**
 *  一般付款
 */
class WC_Gateway_FCBPay extends WC_Payment_Gateway
{
    public $ecpay_test_mode;
    public $merchant_id;
    public $pay_server;
    public $hash_key;
    public $ecpay_hash_iv;
    public $ecpay_choose_payment;
    public $payment_methods;
    public $helper;

    public function __construct()
    {
        $this->id = 'tpay';
        $this->method_title = '電商收款通';
        $this->method_description = '電商收款通是第一銀行提供的整合支付平台，能協助你處理複雜的金流交易業務';
        $this->has_fields = true;
        $this->icon = apply_filters('woocommerce_ecpay_icon', plugins_url('images/logo-firstbank.svg', dirname( __FILE__ )));

        # Load the form fields
        $this->init_form_fields();

        # Load the administrator settings
        $this->init_settings();

        $this->title                 = $this->get_option('title');
        $this->description           = $this->get_option('description');
        $this->pay_server            = $this->get_option('payServer');
        $this->merchant_id     = $this->get_option('merchant_id');
        $this->hash_key        = $this->get_option('hash_key');
        $this->ecpay_hash_iv         = '要移除的欄位';

        # Load the helper
        $this->helper = FCBPay_PaymentCommon::getHelper();
        $this->helper->setMerchantId($this->merchant_id);
		$this->helper->setPayServerUrl($this->pay_server);
        $this->ecpay_test_mode = ($this->helper->isTestMode($this->merchant_id)) ? 'yes' : 'no';

        # Get the payment methods
        $payment_methods = array();
        foreach($this->helper->PaymentMethods as $ecpayPaymentMethods) {
            $payment_methods[$ecpayPaymentMethods] = $this->get_payment_desc($ecpayPaymentMethods);
        }
        $this->payment_methods = $payment_methods;
        $this->get_payment_options();



        # Register a action to save administrator settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_payment_options'));

        $this->add_checkout_actions();
        $this->add_get_plugin_info_filters();

        # 訂單明細頁
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'action_woocommerce_admin_order_status_cancel'));
    }


    /**
     * 新增結帳 Actions
     *
     * @return void
     */
    protected function add_checkout_actions()
    {
        // 付款結果頁
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

        // 自訂轉導綠界付款頁
        add_action('fcbpay_redirect_payment_center', array($this, 'fcbpay_redirect_payment_center'));

        // 付款回應處理
        add_action('woocommerce_api_wc_gateway_' . $this->id, array($this, 'receive_response'));

        // "返回商店"感謝頁
        add_action('woocommerce_thankyou_ecpay', array($this, 'thankyou_page'));
    }

    /**
     * 付款結果頁 Action
     *
     * @param  int $order_id
     * @return void
     */
    public function receipt_page($order_id)
    {

        do_action('fcbpay_redirect_payment_center', $order_id);
    }
    /**
     * 後台 - 載入參數設定欄位
     */
    public function init_form_fields()
    {
        $this->form_fields = include( untrailingslashit( plugin_dir_path( TPAY_PAYMENT_MAIN_FILE ) ) . '/includes/settings-fcbcpay.php' );
    }

    /**
     *  當結帳要選擇支付方法時,顯示的方法
     */
    public function payment_fields()
    {
        if (!empty($this->title)) {
            echo $this->helper->addNextLine(esc_html($this->title) . '('. $this->merchant_id  .')' . '<br /><br />');
            echo $this->helper->addNextLine(esc_html($this->description) . '<br /><br />');
        }

        if ( is_checkout() && ! is_wc_endpoint_url( 'order-pay' ) ) {
            // 產生 Html
            $data = array(
                'payment_options' => $this->payment_options,
                'ecpay_payment_methods' => $this->payment_methods
            );

            echo $this->show_select_payment_methods($data);
        } else {
            echo '請重新下單：不支援重新付款';
        }
    }

    /**
     * Display the form when chooses ECPay payment
     *
     * @param  array $data
     * @return void
     */
    public function show_select_payment_methods($data)
    {
        // 宣告參數
        $payment_options = $data['payment_options'];
        $ecpay_payment_methods = $data['ecpay_payment_methods'];

        // Html
        $szHtml  = '';

        $szHtml .= '付款方法' . ' : ';
        $szHtml .= '<select name="ecpay_choose_payment">';
        foreach ($ecpay_payment_methods as $payment_method => $value) {
            if (in_array($payment_method, $payment_options)) {
                $szHtml .= '<option value="' . esc_attr($payment_method) . '">';
                $szHtml .=    esc_html($value);
                $szHtml .= '</option>';
            }
        }
        $szHtml .= '</select>';

        return $szHtml;
    }


    /**
     * 後台-付款方式區塊
     */
    function generate_ecpay_payment_methods_html()
    {
        ob_start();

        // 產生 Html
        $args = [
            'id' => $this->id,
            'payment_options' => $this->payment_options,
            'ecpay_payment_methods' => $this->payment_methods,
            'ecpay_payment_methods_special' => $this->helper->ecpayPaymentMethodsSpecial
        ];
        wc_get_template('admin/FCBPay-admin-settings-payment-methods.php', $args, '', TPAY_PAYMENT_PLUGIN_PATH . 'templates/');

        return ob_get_clean();
    }

    /**
     * 後台-更新付款方式
     */
    function process_admin_payment_options()
    {
        $options = array();
        if (isset($this->payment_methods) === true) {
            foreach ($this->payment_methods as $key => $value) {
                if (array_key_exists($key, $_POST)) $options[] = $key ;
            }
        }

        update_option($this->id . '_payment_options', $options);
        $this->get_payment_options();
    }

    /**
     * 取得當前開啟的付款方式
     */
    function get_payment_options()
    {
        $this->payment_options = array_filter( (array) get_option( $this->id . '_payment_options' ) );
    }

    /**
     * Check the payment method and the chosen payment
     */
    public function validate_fields()
    {
        $choose_payment = sanitize_text_field($_POST['ecpay_choose_payment']);
        $payment_desc = $this->get_payment_desc($choose_payment);
        if ($_POST['payment_method'] == $this->id && !empty($payment_desc)) {
            $this->ecpay_choose_payment = $choose_payment;
            return true;
        } else {
            $this->ECPay_add_error('錯誤支付'. $payment_desc);
            return false;
        }
    }

    /**
     * Process the payment
     */
    public function process_payment($order_id)
    {
        # Update order status
        $order = new WC_Order($order_id);
        $order->update_status('pending', '交易處理中');

        # Set the ECPay payment type to the order note
        $order->add_order_note($this->ecpay_choose_payment, FCBPay_OrderNoteEmail::PAYMENT_METHOD);

        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }

    /**
     * Process the callback
     */
    public function receive_response()
    {
        $result_msg = '1|OK';
        $order = null;
        try {
            # Retrieve the check out result
            $data = array(
                'hashKey' => $this->hash_key,
                'hashIv'=> $this->ecpay_hash_iv,
            );
            $ecpay_feedback = $this->helper->getValidFeedback($data);

            if (count($ecpay_feedback) < 1) {
                throw new Exception('Get ECPay feedback failed.');
            } else {
                # Get the cart order id
                $cart_order_id = $ecpay_feedback['MerchantTradeNo'];
                if ($this->ecpay_test_mode == 'yes') {
                    $cart_order_id = substr($ecpay_feedback['MerchantTradeNo'], 12);
                }

                # Get the cart order amount
                $order = new WC_Order($cart_order_id);
                $cart_amount = $order->get_total();

                # Check the amounts
                $ecpay_amount = $ecpay_feedback['TradeAmt'];
                if (round($cart_amount) != $ecpay_amount) {
                    throw new Exception('Order ' . $cart_order_id . ' amount are not identical.');
                } else {
                    # Set the common comments
                    $comments = sprintf(
                        $this->tran('Payment Method : %s<br />Trade Time : %s<br />'),
                        esc_html($ecpay_feedback['PaymentType']),
                        esc_html($ecpay_feedback['TradeDate'])
                    );

                    # Set the getting code comments
                    $return_code = esc_html($ecpay_feedback['RtnCode']);
                    $return_message = esc_html($ecpay_feedback['RtnMsg']);
                    $get_code_result_comments = sprintf(
                        '交易狀態 : (%s)%s',
                        $return_code,
                        $return_message
                    );

                    # Set the payment result comments
                    $payment_result_comments = sprintf(
                        '電商收款通付款結果(%s)%s',
                        $return_code,
                        $return_message
                    );

                    # Set the fail message
                    $fail_msg = sprintf('Order %s Exception.(%s: %s)', $cart_order_id, $return_code, $return_message);

                    # Get ECPay payment method
                    $ecpay_payment_method = $this->helper->getPaymentMethod($ecpay_feedback['PaymentType']);

                    # Set the order comments

                    //根據不同的支付方式做處理
                    switch($ecpay_payment_method) {
                        case TPay_PaymentMethod::CREDIT:
                        case TPay_PaymentMethod::UNION:
                        case TPay_PaymentMethod::IDP:
                        case TPay_PaymentMethod::eATM:
                        case TPay_PaymentMethod::REG:
                        case TPay_PaymentMethod::CS:
                        case TPay_PaymentMethod::WECHAT:
                        case TPay_PaymentMethod::TWPAY:
                        case TPay_PaymentMethod::JKOS:

                    }
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (!empty($order)) {
                $comments .= sprintf('付款失敗<br />錯誤訊息 : %s<br />', $error);
                $order->add_order_note($comments);
            }

            # Set the failure result
            $result_msg = '0|' . $error;
        }
        echo $result_msg;
        exit;
    }


    # Custom function

    /**
     * Translate the content
     * @param  string   translate target
     * @return string   translate result
     */
    private function tran($content, $domain = 'ecpay')
    {
        if ($domain == 'ecpay') {
            return __($content, 'ecpay');
        } else {
            return __($content, 'woocommerce');
        }
    }

    /**
     * Get the payment method description
     * @param  string   payment name
     * @return string   payment method description
     */
    private function get_payment_desc($payment_name)
    {
        $payment_desc = array(
            'CREDIT'        => '信用卡-一般',
            'CREDIT_3'      => '信用卡(3期)',
            'CREDIT_6'      => '信用卡(6期)',
            'CREDIT_9'      => '信用卡(9期)',
            'CREDIT_12'     => '信用卡(12期)',
            'CREDIT_15'     => '信用卡(15期)',
            'CREDIT_18'     => '信用卡(18期)',
            'CREDIT_24'     => '信用卡(24期)',
            'CREDIT_30'     => '信用卡(30期)',
            'CREDIT_REWARD' =>'信用卡-紅利折抵',
            'UNION'         => '銀聯卡',
            'IDP'           => '活期帳戶',
            'EATM'          => 'eATM',
            'REG'           => 'ATM',
            'CS'            => '四大超商',
            'WECHAT'        => '微信支付',
            'TWPAY'         => '台灣PAY',
            'JKOS'          => '街口支付'
        );

        return $payment_desc[$payment_name];
    }

    /**
     * Check if the order status is complete
     * @param  object   order
     * @return boolean  is the order complete
     */
    private function is_order_complete($order)
    {
        $status = '';
        $status = (method_exists($order,'get_status') == true ) ? $order->get_status() : $order->status;

        if ($status == 'pending') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the order comments
     * @param  array    ECPay feedback
     * @return string   order comments
     */
    public function get_order_comments($ecpay_feedback)
    {
        $comments = array(
            'ATM' =>
                sprintf(
                    '銀行代碼 : %s<br />虛擬帳號 : %s<br />付款截止日 : %s<br />',
                    esc_html($ecpay_feedback['BankCode']),
                    esc_html($ecpay_feedback['vAccount']),
                    esc_html($ecpay_feedback['ExpireDate'])
                ),
            'CS' =>
                sprintf(
                    '繳費代碼 : %s<br />付款截止日 : %s<br ',
                    esc_html($ecpay_feedback['PaymentNo']),
                    esc_html($ecpay_feedback['ExpireDate'])
                ),
            'BARCODE' =>
                sprintf(
                    '付款截止日 : %s<br />第1段條碼號碼 : %s<br />第2段條碼號碼 : %s<br />第3段條碼號碼 : %s<br />',
                    esc_html($ecpay_feedback['ExpireDate']),
                    esc_html($ecpay_feedback['Barcode1']),
                    esc_html($ecpay_feedback['Barcode2']),
                    esc_html($ecpay_feedback['Barcode3'])
                )
        );
        $payment_method = $this->helper->getPaymentMethod($ecpay_feedback['PaymentType']);

        return $comments[$payment_method];
    }

    /**
     * Complete the order and add the comments
     * @param  object   order
     */
    public function confirm_order($order, $comments, $ecpay_feedback)
    {
        // 判斷是否為模擬付款
        if ($ecpay_feedback['SimulatePaid'] == 0) {
            $order->add_order_note($comments, FCBPay_OrderNoteEmail::CONFIRM_ORDER);

            $order->payment_complete();

            // 加入信用卡後四碼，提供電子發票開立使用 v1.1.0911
            if(isset($ecpay_feedback['card4no']) && !empty($ecpay_feedback['card4no']))
            {
                add_post_meta( $order->get_id(), 'card4no', sanitize_text_field($ecpay_feedback['card4no']), true);
            }

            // 自動開立發票
            $this->auto_invoice($order->get_id(), $ecpay_feedback);
        } elseif ($ecpay_feedback['SimulatePaid'] == 1) {
            // 模擬付款，僅更新備註
            $order->add_order_note($this->tran($this->helper->msg['simulatePaid']));
        }
    }

    /**
     * Output for the order received page.
     *
     * @param int $order_id
     */
    public function thankyou_page( $order_id )
    {

        $this->payment_details( $order_id );

    }

    /**
     * Get payment details and place into a list format.
     *
     * @param int $order_id
     */
    private function payment_details( $order_id = '' )
    {
        $account_html = '';
        $has_details = false ;
        $a_has_details = array();

        $payment_method = get_post_meta($order_id, 'payment_method', true);

        switch($payment_method) {
            case TPay_PaymentMethod::CVS:
                $PaymentNo = get_post_meta($order_id, 'PaymentNo', true);
                $ExpireDate = get_post_meta($order_id, 'ExpireDate', true);

                $a_has_details = array(
                    'PaymentNo' => array(
                                'label' => '繳款編號',
                                'value' => $PaymentNo
                            ),
                    'ExpireDate' => array(
                                'label' => '到期日',
                                'value' => $ExpireDate
                            )
                );

                $has_details = true ;
                break;

            case TPay_PaymentMethod::ATM:
                $BankCode = get_post_meta($order_id, 'BankCode', true);
                $vAccount = get_post_meta($order_id, 'vAccount', true);
                $ExpireDate = get_post_meta($order_id, 'ExpireDate', true);

                $a_has_details = array(
                    'BankCode' => array(
                                'label' => '銀行代號',
                                'value' => $BankCode
                            ),
                    'vAccount' => array(
                                'label' => '匯款帳號',
                                'value' => $vAccount
                            ),
                    'ExpireDate' => array(
                                'label' => '到期日',
                                'value' => $ExpireDate
                            )
                );

                $has_details = true ;
                break;
        }

        $account_html .= '<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">' . PHP_EOL;

        foreach($a_has_details as $field_key => $field ) {
            $account_html .= '<li class="' . esc_attr( $field_key ) . '">' . wp_kses_post( $field['label'] ) . ': <strong>' . wp_kses_post( wptexturize( $field['value'] ) ) . '</strong></li>' . PHP_EOL ;
        }

        $account_html .= '</ul>';

        if ( $has_details ) {
            echo '<section><h2>' . $this->tran( 'Payment details' ) . '</h2>' . PHP_EOL . $account_html . '</section>';
        }
    }

    /**
     * 無效訂單狀態更新
     *
     * @return void
     */
    public function action_woocommerce_admin_order_status_cancel()
    {
        try {
            global $post;

            // 訂單編號
            $order_id = $post->ID;

            // 是否反查過訂單
            $is_expire = get_post_meta($order_id, '_ecpay_payment_is_expire', true);

            if ($is_expire === $this->helper->isExpire['no']) {

                // 取得傳入資料
                $order                      = wc_get_order($order_id);
                $order_status               = $order->get_status();                                                // 訂單狀態
                $payment_method             = $order->get_payment_method();                                        // 付款方式
                $date_created               = $order->get_date_created()->getTimestamp();                          // 訂單建立時間
                $ecpay_payment_method       = get_post_meta($order_id, '_ecpay_payment_method', true);             // 綠界付款方式
                $stage_payment_order_prefix = get_post_meta($order_id, '_ecpay_payment_stage_order_prefix', true); // 測試訂單編號前綴
                $hold_stock_minutes         = empty(get_option('woocommerce_hold_stock_minutes')) ? 0 : get_option('woocommerce_hold_stock_minutes'); // 取得保留庫存時間

                // 組合傳入資料
                $data = array(
                    'hashKey'            => $this->hash_key,
                    'hashIv'             => $this->ecpay_hash_iv,
                    'orderId'            => $order_id,
                    'holdStockMinute'    => $hold_stock_minutes,
                    'orderStatus'        => $order_status,
                    'paymentMethod'      => $payment_method,
                    'ecpayPaymentMethod' => $ecpay_payment_method,
                    'createDate'         => $date_created,
                    'stageOrderPrefix'   => $stage_payment_order_prefix,
                );
                $feedback = $this->helper->expiredOrder($data);

                // 交易失敗
                if (isset($feedback['TradeStatus']) && $feedback['TradeStatus'] == $this->helper->tradeStatusCodes['emptyPaymentMethod']) {
                    // 更新訂單狀態/備註
                    //$order->add_order_note($this->tran( $this->helper->msg['unpaidOrder'], 'woocommerce' ), ECPay_OrderNoteEmail::CANCEL_ORDER);

                    $order->update_status('cancelled');
                    update_post_meta($order_id, '_ecpay_payment_is_expire', $this->helper->isExpire['yes'] );

                    // 提示
                    $args = [
                        'msg' => '訂單已經改變,請刷新你的瀏覽器'
                    ];
                    wc_get_template('admin/FCBPay-admin-order-expire.php', $args, '', TPAY_PAYMENT_PLUGIN_PATH . 'templates/');
                }
            }
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 記錄目前成功付款次數
     *
     * @param  integer $order_id            訂單編號
     * @param  array   $total_success_times 付款次數
     * @return void
     */
    private function note_success_times($order_id, $total_success_times)
    {
        $nTotalSuccessTimes = ( isset($total_success_times) && ( empty($total_success_times) || $total_success_times == 1 ))  ? '' :  $total_success_times;
        update_post_meta($order_id, '_total_success_times', $nTotalSuccessTimes );
    }

    /**
     * 自動開立發票
     *
     * @param  integer $order_id
     * @return void
     */
    private function auto_invoice($order_id, $ecpay_feedback)
    {
        // call invoice model
        $invoice_active_ecpay   = 0 ;

        // 取得目前啟用的外掛
        $active_plugins = (array) get_option( 'active_plugins', array() );

        // 加入其他站點啟用的外掛
        $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );

        // 判斷ECPay發票模組是否有啟用
        foreach ($active_plugins as $key => $value) {
            if ((strpos($value, '/woocommerce-ecpayinvoice.php') !== false)) {
                $invoice_active_ecpay = 1;
            }
        }

        // 自動開立發票
        if ($invoice_active_ecpay == 1) {
            $aConfig_Invoice = get_option('wc_ecpayinvoice_active_model') ;

            // 記錄目前成功付款到第幾次
            $this->note_success_times($order_id, $ecpay_feedback['TotalSuccessTimes']);

            if (isset($aConfig_Invoice) && $aConfig_Invoice['wc_ecpay_invoice_enabled'] == 'enable' && $aConfig_Invoice['wc_ecpay_invoice_auto'] == 'auto' ) {
                do_action('ecpay_auto_invoice', $order_id, $ecpay_feedback['SimulatePaid']);
            }
        }
    }

    /**
     * Add a WooCommerce error message
     * @param string $error_message
     */
    private function ECPay_add_error($error_message)
    {
        wc_add_notice(esc_html($error_message), 'error');
    }

    /**
     * 轉導FCB Pay付款頁
     *
     * @param int $order_id
     * @return void
     */
    public function fcbpay_redirect_payment_center($order_id)
    {
        # Clean the cart
        global $woocommerce;
        $woocommerce->cart->empty_cart();

        // 撈取訂單資訊
        $order = new WC_Order($order_id);
        $notes = $order->get_customer_order_notes();

        // 儲存訂單資訊
        $stage_order_prefix = $this->helper->getMerchantOrderPrefix();
        $data = array(
            'ecpay_test_mode'    => $this->ecpay_test_mode,
            'order_id'           => $order_id,
            'notes'              => $notes[0],
            'stage_order_prefix' => $stage_order_prefix,
            'is_expire'          => $this->helper->isExpire['no'],
        );
        FCBPay_PaymentCommon::ecpay_save_payment_order_info($data);

        try {
            # Get the chosen payment and installment
            $notes = $order->get_customer_order_notes();
            $choose_payment = isset($notes[0]) ? $notes[0]->comment_content : '';

            $data = array(
                'choosePayment'     => $choose_payment,
                'hashKey'           => $this->hash_key,
                'hashIv'            => $this->ecpay_hash_iv,
                'returnUrl'         => add_query_arg('wc-api', 'WC_Gateway_FCBPay', home_url('/')),
                'clientBackUrl'     => $this->get_return_url($order),
                'orderId'           => $order->get_id(),
                'total'             => $order->get_total(),
                'itemName'          => 'A Package Of Online Goods',
                'cartName'          => 'woocommerce',
                'currency'          => $order->get_currency(),
                'needExtraPaidInfo' => 'Y',
            );

            $this->helper->checkout($data);
            exit;
        } catch(Exception $e) {
            $this->ECPay_add_error($e->getMessage());
        }
    }

    /**
     * 新增取得模組資訊 Filters
     *
     * @return void
     */
    private function add_get_plugin_info_filters()
    {
        $filters = array(
            'ecpay_is_payment_enabled',
            'ecpay_get_payment_plugin_version',
        );
        $parent = $this;
        array_walk($filters, function ($value) use ($parent) {
            add_filter($value, array($parent, $value));
        });
    }

    /**
     * 檢查金流模組是否啟用
     *
     * @return bool
     */
    public function ecpay_is_payment_enabled()
    {
        $enabled = false;
        try {
            if (!property_exists($this, 'id')) {
                throw new Exception('Property "id" does not exist!');
            }

            $setting = get_option( 'woocommerce_' . $this->id . '_settings', '' );
            if (empty($setting)) {
                throw new Exception('Payment settings is empty!');
            }

            if (!isset($setting['enabled'])) {
                throw new Exception('Payment settings "enabled" is empty!');
            }

            $enabled = $setting['enabled'];
        } catch (Exception $e) {

        }

        return $enabled;
    }

    /**
     * 取得金流模組版本
     *
     * @return string
     */
    public function ecpay_get_payment_plugin_version()
    {
        $version = '';
        if (defined('ECPAY_PAYMENT_PLUGIN_VERSION')) {
            $version = TPAY_PAYMENT_PLUGIN_VERSION;
        }

        return $version;
    }
}

/**
 * 金流共用功能
 */
class FCBPay_PaymentCommon
{
    /**
     * 取得Helper
     * @return object
     */
    public static function getHelper()
    {
        $helper = new FCBPayPaymentHelper();

        # 設定時區
        $helper->setTimezone(static::getTimezone());

        # 設定訂單狀態
        $helper->setOrderStatus(static::getOrderStatus());

        return $helper;
    }



    /**
     * 取得時區
     *
     * @return array
     */
    public static function getTimezone()
    {
        $timezone = (get_option('timezone_string') === '') ? date_default_timezone_get() : get_option('timezone_string');

        return $timezone;
    }

    /**
     * 訂單狀態
     *
     * @return array
     */
    public static function getOrderStatus()
    {
        $data = array(
            'Pending'    => 'pending',
            'Processing' => 'processing',
            'OnHold'     => 'on-hold',
            'Cancelled'  => 'cancelled',
            'Ecpay'      => 'ecpay',
        );

        return $data;
    }

    /**
     * 儲存訂單資訊
     * @param  integer $order_id 訂單編號
     * @return void
     */
    public static function ecpay_save_payment_order_info($data)
    {
        // 儲存測試模式訂單編號前綴
        $stage_order_prefix = isset($data['stage_order_prefix']) ? $data['stage_order_prefix'] : '' ;
        add_post_meta($data['order_id'], '_ecpay_payment_stage_order_prefix', sanitize_text_field($stage_order_prefix), true);

        // 儲存付款方式
        $notes_comment_content = isset($data['notes']) ? $data['notes']->comment_content : '' ;
        add_post_meta($data['order_id'], '_ecpay_payment_method', sanitize_text_field($notes_comment_content), true);

        // 是否做過訂單反查檢查，預設'N'(否)
        add_post_meta($data['order_id'], '_ecpay_payment_is_expire', sanitize_text_field($data['is_expire']), true);
    }
}
?>
