<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Pal_Pro
 * @subpackage Premium_Pal_Pro_PayFlow_Gateway/includes
 * @author     wpremiumdev <wpremiumdev@gmail.com>
 */
class Premium_Pal_Pro_PayFlow_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        try {
            $this->id = 'pal_pro_payflow';
            $this->method_title = __('PayPal Pro PayFlow', 'woo-paypal-payflow');
            $this->method_description = __('PayPal Pro PayFlow Edition works by adding credit card fields on the checkout and then sending the details to PayPal for verification.', 'woo-paypal-payflow');
            $this->icon = apply_filters('woocommerce_pal_pro_icon', plugins_url('/images/cards.png', plugin_basename(dirname(__FILE__))));
            $this->has_fields = true;
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled = $this->get_option('premium_enabled');
            $this->title = $this->get_option('premium_title');
            $this->description = $this->get_option('premium_description');
            $this->soft_descriptor = str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\-\.]/', '', $this->get_option('premium_soft_description', "")));
            $this->testmode = $this->get_option('premium_testmode', "no") === "yes" ? true : false;
            $this->paymentaction = $this->get_option('premium_action', 'Sale');
            $this->debug = $this->get_option('premium_debug_log', "no") === "yes" ? true : false;
            $this->allowed_currencies = apply_filters('woocommerce_pal_pro_payflow_allowed_currencies', array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD'));
            $this->invoice_prefix = $this->get_option('premium_invoice_prefix');
            $this->post_data = array();
            $this->ITEMAMT = 0;
            $this->fee_total = 0;
            $this->item_loop = 0;
            if ($this->testmode) {
                $this->Pay_URL = "https://pilot-payflowpro.paypal.com";
                $this->paypal_vendor = ($this->get_option('premium_sandbox_vendor')) ? trim($this->get_option('premium_sandbox_vendor')) : '';
                $this->paypal_password = ($this->get_option('premium_sandbox_password')) ? trim($this->get_option('premium_sandbox_password')) : '';
                $this->paypal_user = ($this->get_option('premium_sandbox_user')) ? trim($this->get_option('premium_sandbox_user')) : '';
                $this->paypal_partner = ($this->get_option('premium_sandbox_partner')) ? trim($this->get_option('premium_sandbox_partner')) : '';
            } else {
                $this->Pay_URL = "https://payflowpro.paypal.com";
                $this->paypal_vendor = ($this->get_option('premium_live_vendor')) ? trim($this->get_option('premium_live_vendor')) : '';
                $this->paypal_password = ($this->get_option('premium_live_password')) ? trim($this->get_option('premium_live_password')) : '';
                $this->paypal_user = ($this->get_option('premium_live_user')) ? trim($this->get_option('premium_live_user')) : '';
                $this->paypal_partner = ($this->get_option('premium_live_partner')) ? trim($this->get_option('premium_live_partner')) : '';
            }
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        } catch (Exception $ex) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-paypal-payflow') . '</strong>: ' . $ex->getMessage(), 'error');
            return;
        }
    }

    public function init_form_fields() {
        return $this->form_fields = premium_pal_pro_payflow_setting_field();
    }

    public function is_available() {
        if ($this->enabled === "yes") {
            if (!is_ssl() && !$this->testmode) {
                return false;
            }
            // Currency check
            if (!in_array(get_option('woocommerce_currency'), $this->allowed_currencies)) {
                return false;
            }
            // Required fields check
            if (!$this->paypal_vendor || !$this->paypal_password) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function admin_options() {
        parent::admin_options();
        ?>      
        <script type="text/javascript">
            jQuery('#woocommerce_pal_pro_payflow_premium_testmode').change(function () {
                var sandbox = jQuery('#woocommerce_pal_pro_payflow_premium_sandbox_vendor, #woocommerce_pal_pro_payflow_premium_sandbox_password, #woocommerce_pal_pro_payflow_premium_sandbox_user, #woocommerce_pal_pro_payflow_premium_sandbox_partner').closest('tr'),
                        production = jQuery('#woocommerce_pal_pro_payflow_premium_live_vendor, #woocommerce_pal_pro_payflow_premium_live_password, #woocommerce_pal_pro_payflow_premium_live_user, #woocommerce_pal_pro_payflow_premium_live_partner').closest('tr');

                if (jQuery(this).is(':checked')) {
                    sandbox.show();
                    production.hide();
                } else {
                    sandbox.hide();
                    production.show();
                }
            }).change();
        </script> 
        <?php

    }

    public function payment_fields() {
        if ($this->description) {
            echo '<p>' . wp_kses_post($this->description);
            if ($this->testmode == "yes") {
                echo '<p>';
                _e('NOTICE: SANDBOX (TEST) MODE ENABLED.', 'woo-paypal-payflow');
                echo '<br />';
                _e('For testing purposes you can use the card number 4012 8888 8888 1881 with any CVC and a valid expiration date.', 'woo-paypal-payflow');
                echo '</p>';
            }
        }
        if (class_exists('WC_Payment_Gateway_CC')) {
            $cc_form = new WC_Payment_Gateway_CC;
            $cc_form->id = $this->id;
            $cc_form->supports = $this->supports;
            $cc_form->form();
        } else {
            $fields = $this->premium_pal_pro_payflow_credit_card_form_fields($default_fields = null, $this->id);
            $this->credit_card_form(array(), $fields);
        }
    }

    public function validate_fields() {
        try {
            $card = premium_pal_pro_payflow_is_card_details($_POST);

            if (empty($card->exp_month) || empty($card->exp_year)) {
                throw new Exception(__('Card expiration date is invalid', 'woo-paypal-payflow'));
            }

            // Validate values
            if (!ctype_digit($card->cvc)) {
                throw new Exception(__('Card security code is invalid (only digits are allowed)', 'woo-paypal-payflow'));
            }

            if (
                    !ctype_digit($card->exp_month) ||
                    !ctype_digit($card->exp_year) ||
                    $card->exp_month > 12 ||
                    $card->exp_month < 1 ||
                    $card->exp_year < date('y')
            ) {
                throw new Exception(__('Card expiration date is invalid', 'woo-paypal-payflow'));
            }

            if (empty($card->number) || !ctype_digit($card->number)) {
                throw new Exception(__('Card number is invalid', 'woo-paypal-payflow'));
            }
            return true;
        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
            return false;
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '') {

        $order = wc_get_order($order_id);
        if (!$order || !$order->get_transaction_id() || !$this->paypal_user || !$this->paypal_vendor || !$this->paypal_password) {
            return false;
        }
        // get transaction details
        $details = $this->premium_pal_pro_payflow_transaction_details($order->get_transaction_id());
        // check if it is authorized only we need to void instead
        if ($details && strtolower($details['TRANSSTATE']) === '3') {
            $order->add_order_note(__('This order cannot be refunded due to an authorized only transaction.  Please use cancel instead.', 'woo-paypal-payflow'));
            $this->premium_pal_pro_payflow_log_write('Refund order # ', $order_id . ': authorized only transactions need to use cancel/void instead.');
            throw new Exception(__('This order cannot be refunded due to an authorized only transaction.  Please use cancel instead.', 'woo-paypal-payflow'));
        }
        $post_data = array(
            'USER' => $this->paypal_user,
            'VENDOR' => $this->paypal_vendor,
            'PARTNER' => $this->paypal_partner,
            'PWD' => $this->paypal_password,
            'METHOD' => 'RefundTransaction',
            'TRXTYPE' => 'C',
            'ORIGID' => $order->get_transaction_id()
        );
        if (!is_null($amount)) {
            $post_data['AMT'] = number_format($amount, 2, '.', '');
            $post_data['CURRENCY'] = $order->get_order_currency();
        }
        if ($reason) {
            if (255 < strlen($reason)) {
                $reason = substr($reason, 0, 252) . '...';
            }
            $post_data['COMMENT1'] = html_entity_decode($reason, ENT_NOQUOTES, 'UTF-8');
        }
        $response = wp_remote_post($this->Pay_URL, array(
            'method' => 'POST',
            'body' => $post_data,
            'timeout' => 70,
            'user-agent' => 'woo-paypal-payflow',
            'httpversion' => '1.1'
        ));

        parse_str($response['body'], $parsed_response);

        if (is_wp_error($response)) {
            $this->premium_pal_pro_payflow_log_write('Error ', $response->get_error_message());
            throw new Exception(__('There was a problem connecting to the payment gateway.', 'woo-paypal-payflow'));
        } elseif ($parsed_response['RESULT'] !== '0') {
            $this->premium_pal_pro_payflow_log_write('Parsed Response (refund) ', $response->get_error_message());
        } else {
            $order->add_order_note(sprintf(__('Refunded %s - PNREF: %s', 'woo-paypal-payflow'), wc_price(number_format($amount, 2, '.', '')), $parsed_response['PNREF']));
            return true;
        }
        return false;
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $this->premium_pal_pro_payflow_log_write('Processing order # ', $order_id);
        $card = premium_pal_pro_payflow_is_card_details($_POST);
        return $this->premium_pal_pro_payflow_do_payment($order, $card);
    }

    public function premium_pal_pro_payflow_do_payment($order, $card) {
        try {
            $this->premium_pal_pro_payflow_cart_order_details($order, $card);
            $this->premium_pal_pro_payflow_log_write('Do payment request ', $this->post_data);
            $response = $this->premium_pal_pro_payflow_wp_remote_post($order);
            if (is_wp_error($response)) {
                $this->premium_pal_pro_payflow_log_write('Error ', $response->get_error_message());
                throw new Exception(__('There was a problem connecting to the payment gateway.', 'woo-paypal-payflow'));
            }
            if (empty($response['body'])) {
                $this->premium_pal_pro_payflow_log_write('Empty response! ', $response->get_error_message());
                throw new Exception(__('Empty Paypal response.', 'woo-paypal-payflow'));
            }
            parse_str($response['body'], $parsed_response);
            $this->premium_pal_pro_payflow_log_write('Parsed Response ', $parsed_response);
            return $this->premium_pal_pro_payflow_update_notes($parsed_response, $order);
        } catch (Exception $e) {
            wc_add_notice(__('Connection error:', 'woo-paypal-payflow') . ': "' . $e->getMessage() . '"', 'error');
            return;
        }
    }

    public function premium_pal_pro_payflow_cart_item($order_get_items, $order) {
        foreach ($order_get_items as $item) {
            $_product = $order->get_product_from_item($item);
            if ($item['qty']) {
                $this->post_data['L_NAME' . $this->item_loop] = $item['name'];
                $this->post_data['L_COST' . $this->item_loop] = $order->get_item_total($item, true);
                $this->post_data['L_QTY' . $this->item_loop] = $item['qty'];
                if ($_product->get_sku()) {
                    $this->post_data['L_SKU' . $this->item_loop] = $_product->get_sku();
                }
                $this->ITEMAMT += $order->get_item_total($item, true) * $item['qty'];
                $this->item_loop++;
            }
        }
        return TRUE;
    }

    public function premium_pal_pro_payflow_cart_shipping($order) {
        $this->post_data['L_NAME' . $this->item_loop] = 'Shipping';
        $this->post_data['L_DESC' . $this->item_loop] = 'Shipping and shipping taxes';
        $this->post_data['L_COST' . $this->item_loop] = $order->get_total_shipping() + $order->get_shipping_tax();
        $this->post_data['L_QTY' . $this->item_loop] = 1;
        $this->ITEMAMT += round($order->get_total_shipping() + $order->get_shipping_tax(), 2);
        $this->item_loop++;
        return TRUE;
    }

    public function premium_pal_pro_payflow_cart_discount($order) {
        $this->post_data['L_NAME' . $this->item_loop] = 'Order Discount';
        $this->post_data['L_DESC' . $this->item_loop] = 'Discounts after tax';
        $this->post_data['L_COST' . $this->item_loop] = '-' . $order->get_order_discount();
        $this->post_data['L_QTY' . $this->item_loop] = 1;
        $this->item_loop++;
        return TRUE;
    }

    public function premium_pal_pro_payflow_cart_fix_rounding($order) {
        $this->post_data['L_NAME' . $this->item_loop] = 'Rounding amendment';
        $this->post_data['L_DESC' . $this->item_loop] = 'Correction if rounding is off (this can happen with tax inclusive prices)';
        $this->post_data['L_COST' . $this->item_loop] = ( absint($order->get_total() * 100) - absint($ITEMAMT * 100) ) / 100;
        $this->post_data['L_QTY' . $this->item_loop] = 1;
        return TRUE;
    }

    public function premium_pal_pro_payflow_cart_order_details($order, $card) {
        $this->post_data = array(
            'USER' => $this->paypal_user,
            'VENDOR' => $this->paypal_vendor,
            'PARTNER' => $this->paypal_partner,
            'PWD' => $this->paypal_password,
            'TENDER' => 'C',
            'TRXTYPE' => $this->paymentaction,
            'AMT' => $order->get_total(),
            'CURRENCY' => $order->get_order_currency(),
            'CUSTIP' => premium_pal_pro_payflow_get_user_ip(),
            'EMAIL' => $order->billing_email,
            'INVNUM' => $this->invoice_prefix . str_replace("#", "", $order->get_order_number()),
            'CREDITCARDTYPE' => $card->type,
            'ACCT' => $card->number,
            'EXPDATE' => $card->exp_month . $card->exp_year,
            'STARTDATE' => $card->start_month . $card->start_year,
            'CVV2' => $card->cvc,
            'BUTTONSOURCE' => 'mbjtechnolabs_SP'
        );
        if ($this->soft_descriptor) {
            $post_data['MERCHDESCR'] = $this->soft_descriptor;
        }
        if (sizeof($order->get_items()) > 0) {
            $this->premium_pal_pro_payflow_cart_item($order->get_items(), $order);
            if (( $order->get_total_shipping() + $order->get_shipping_tax() ) > 0) {
                $this->premium_pal_pro_payflow_cart_shipping($order);
            }
            if ($order->get_total_discount() > 0) {
                $this->premium_pal_pro_payflow_cart_discount($order);
            }
            $this->ITEMAMT = round($this->ITEMAMT, 2);
            if (absint($order->get_total() * 100) !== absint($this->ITEMAMT * 100)) {
                $this->premium_pal_pro_payflow_cart_fix_rounding($order);
            }
            $this->post_data['ITEMAMT'] = $order->get_total();
            $this->post_data['TAXAMT'] = round($order->get_total_tax(), 2);
        }
        $this->post_data['ORDERDESC'] = 'Order ' . $order->get_order_number() . ' on ' . wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $this->post_data['FIRSTNAME'] = $order->billing_first_name;
        $this->post_data['LASTNAME'] = $order->billing_last_name;
        $this->post_data['STREET'] = $order->billing_address_1 . ' ' . $order->billing_address_2;
        $this->post_data['CITY'] = $order->billing_city;
        $this->post_data['STATE'] = $order->billing_state;
        $this->post_data['COUNTRY'] = $order->billing_country;
        $this->post_data['ZIP'] = $order->billing_postcode;
        if ($order->shipping_address_1) {
            $this->post_data['SHIPTOFIRSTNAME'] = $order->shipping_first_name;
            $this->post_data['SHIPTOLASTNAME'] = $order->shipping_last_name;
            $this->post_data['SHIPTOSTREET'] = $order->shipping_address_1;
            $this->post_data['SHIPTOCITY'] = $order->shipping_city;
            $this->post_data['SHIPTOSTATE'] = $order->shipping_state;
            $this->post_data['SHIPTOCOUNTRY'] = $order->shipping_country;
            $this->post_data['SHIPTOZIP'] = $order->shipping_postcode;
        }
        return TRUE;
    }

    public function premium_pal_pro_payflow_wp_remote_post($order) {
        return wp_remote_post($this->Pay_URL, array(
            'method' => 'POST',
            'body' => $this->post_data,
            'timeout' => 70,
            'user-agent' => 'woo-paypal-payflow',
            'httpversion' => '1.1'
        ));
    }

    public function premium_pal_pro_payflow_update_notes($parsed_response, $order) {
        try {
            if (isset($parsed_response['RESULT']) && in_array($parsed_response['RESULT'], array(0, 126, 127))) {
                switch ($parsed_response['RESULT']) {
                    // Approved or screening service was down
                    case 0 :
                    case 127 :
                        $txn_id = (!empty($parsed_response['PNREF']) ) ? wc_clean($parsed_response['PNREF']) : '';
                        // get transaction details
                        $details = $this->premium_pal_pro_payflow_transaction_details($txn_id);
                        // check if it is captured or authorization only [transstate 3 is authoriztion only]
                        if ($details && strtolower($details['TRANSSTATE']) === '3') {
                            // Store captured value
                            update_post_meta($order->id, '_paypalpro_charge_captured', 'no');
                            add_post_meta($order->id, '_transaction_id', $txn_id, true);
                            // Mark as on-hold
                            $order->update_status('on-hold', sprintf(__('PayPal Pro (PayFlow) charge authorized (Charge ID: %s). Process order to take payment, or cancel to remove the pre-authorization.', 'woo-paypal-payflow'), $txn_id));
                            // Reduce stock levels
                            $order->reduce_order_stock();
                        } else {
                            // Add order note
                            $order->add_order_note(sprintf(__('PayPal Pro (Payflow) payment completed (PNREF: %s)', 'woo-paypal-payflow'), $parsed_response['PNREF']));
                            // Payment complete
                            $order->payment_complete($txn_id);
                        }
                        // Remove cart
                        WC()->cart->empty_cart();
                        break;
                    // Under Review by Fraud Service
                    case 126 :
                        $order->add_order_note($parsed_response['RESPMSG']);
                        $order->add_order_note($parsed_response['PREFPSMSG']);
                        $order->update_status('on-hold', __('The payment was flagged by a fraud filter. Please check your PayPal Manager account to review and accept or deny the payment and then mark this order "processing" or "cancelled".', 'woo-paypal-payflow'));
                        break;
                }
                $redirect = $order->get_checkout_order_received_url();
                // Return thank you page redirect
                return array(
                    'result' => 'success',
                    'redirect' => $redirect
                );
            } else {
                // Payment failed :(
                $order->update_status('failed', __('PayPal Pro (Payflow) payment failed. Payment was rejected due to an error: ', 'woo-paypal-payflow') . '(' . $parsed_response['RESULT'] . ') ' . '"' . $parsed_response['RESPMSG'] . '"');
                wc_add_notice(__('Payment error:', 'woo-paypal-payflow') . ' ' . $parsed_response['RESPMSG'], 'error');
                return;
            }
        } catch (Exception $ex) {
            wc_add_notice(__('Connection error:', 'woo-paypal-payflow') . ': "' . $ex->getMessage() . '"', 'error');
            return;
        }
    }

    public function premium_pal_pro_payflow_transaction_details($transaction_id = 0) {
        $post_data = array(
            'USER' => $this->paypal_user,
            'VENDOR' => $this->paypal_vendor,
            'PARTNER' => $this->paypal_partner,
            'PWD' => $this->paypal_password,
            'TRXTYPE' => 'I',
            'ORIGID' => $transaction_id
        );
        $response = wp_remote_post($this->Pay_URL, array(
            'method' => 'POST',
            'body' => $post_data,
            'timeout' => 70,
            'user-agent' => 'woo-paypal-payflow',
            'httpversion' => '1.1'
        ));
        if (is_wp_error($response)) {
            $this->premium_pal_pro_payflow_log_write('Error ', $response->get_error_message());
            throw new Exception(__('There was a problem connecting to the payment gateway.', 'woo-paypal-payflow'));
        }
        parse_str($response['body'], $parsed_response);
        if ($parsed_response['RESULT'] === '0') {
            return $parsed_response;
        }
        return false;
    }

    public function premium_pal_pro_payflow_credit_card_form_fields($default_fields, $current_gateway_id) {
        if ($current_gateway_id == $this->id) {
            $fields = array(
                'card-number-field' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr($this->id) . '-card-number">' . __('Credit Card Number', 'woo-paypal-payflow') . ' <span class="required">*</span></label>
				<input id="' . esc_attr($this->id) . '-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••" name="' . $this->id . '-card-number' . '" />
			</p>',
                'card-expiry-field' => '<p class="form-row form-row-last">
					<label for="' . esc_attr($this->id) . '-card-expiry">' . __('Expiry (MM/YY)', 'pal-pro') . ' <span class="required">*</span></label>
					<input id="' . esc_attr($this->id) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="' . esc_attr__('MM / YY', 'pal-pro') . '" name="' . $this->id . '-card-expiry' . '" />
				</p>',
                'card-cvc-field' => '<p class="form-row form-row-last">
				<label for="' . esc_attr($this->id) . '-card-cvc">' . __('Card Security Code', 'woo-paypal-payflow') . ' <span class="required">*</span></label>
				<input id="' . esc_attr($this->id) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="' . esc_attr__('CVC', 'woo-paypal-payflow') . '" name="' . $this->id . '-card-cvc' . '" />
			</p>'
            );
            return $fields;
        } else {
            return $default_fields;
        }
    }

    public function premium_pal_pro_payflow_log_write($text = null, $message) {
        if ($this->debug) {
            if (empty($this->log)) {
                $this->log = new WC_Logger();
            }
            if (is_array($message) && count($message) > 0) {
                $message = $this->premium_pal_pro_payflow_personal_detail_square($message);
            }
            $this->log->add('pal_pro_payflow_', $text . ' ' . print_r($message, true));
        }
    }

    public function premium_pal_pro_payflow_personal_detail_square($message) {
        foreach ($message as $key => $value) {
            if ($key == "VENDOR" || $key == "PWD" || $key == "USER" || $key == "ACCT" || $key == "EXPDATE" || $key == "CVV2") {
                $str_length = strlen($value);
                $ponter_data = "";
                for ($i = 0; $i <= $str_length; $i++) {
                    $ponter_data .= '*';
                }
                $message[$key] = $ponter_data;
            }
        }
        return $message;
    }

}