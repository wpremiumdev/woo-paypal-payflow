<?php

function premium_pal_pro_payflow_setting_field() {
    return array(
        'premium_enabled' => array(
            'title' => __('Enable/Disable', 'woo-paypal-payflow'),
            'label' => __('Enable PayPal Pro PayFlow', 'woo-paypal-payflow'),
            'type' => 'checkbox',
            'description' => '',
            'default' => 'no'
        ),
        'premium_title' => array(
            'title' => __('Title', 'woo-paypal-payflow'),
            'type' => 'text',
            'description' => __('This controls the title which the user sees during checkout.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'default' => __('PayPal Pro PayFlow', 'woo-paypal-payflow')
        ),
        'premium_description' => array(
            'title' => __('Description', 'woo-paypal-payflow'),
            'type' => 'textarea',
            'description' => __('This controls the description which the user sees during checkout.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'default' => __("Pay with your credit card via PayPal Website Payments Pro PayFlow.", 'woo-paypal-payflow')
        ),
        'premium_soft_description' => array(
            'title' => __('Soft Descriptor', 'woo-paypal-payflow'),
            'type' => 'textarea',
            'description' => __('(Optional) Information that is usually displayed in the account holder\'s statement, for example your website name. Only 23 alphanumeric characters can be included, including the special characters dash (-) and dot (.) . Asterisks (*) and spaces ( ) are NOT permitted.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_testmode' => array(
            'title' => __('Test Mode', 'woo-paypal-payflow'),
            'type' => 'checkbox',
            'default' => 'yes',
            'description' => __('Place the payment gateway in development mode.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'label' => __('Enable PayPal Sandbox/Test Mode', 'woo-paypal-payflow')
        ),        
        'premium_sandbox_vendor' => array(
            'title' => __('PayPal Vendor', 'woo-paypal-payflow'),
            'type' => 'text',
            'description' => __('Your merchant login ID that you created when you registered for the account.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'label' => __('Create sandbox accounts and obtain API credentials from within your <a href="http://developer.paypal.com">PayPal developer account</a>.', 'woo-paypal-payflow'),
            'default' => ''
        ),
        'premium_sandbox_password' => array(
            'title' => __('PayPal Password', 'woo-paypal-payflow'),
            'type' => 'password',
            'description' => __('The password that you defined while registering for the account.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_sandbox_user' => array(
            'title' => __('PayPal User', 'woo-paypal-payflow'),
            'type' => 'text',
            'description' => __('If you set up one or more additional users on the account, this value is the ID of the user authorized to process transactions. Otherwise, leave this field blank.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_sandbox_partner' => array(
            'title' => __('PayPal Partner', 'woo-paypal-payflow'),
            'type' => 'text',
            'description' => __('The ID provided to you by the authorized PayPal Reseller who registered you for the Payflow SDK. If you purchased your account directly from PayPal, use PayPal or leave blank.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'default' => 'PayPal'
        ),
        'premium_live_vendor' => array(
            'title' => __('PayPal Vendor', 'woo-paypal-payflow'),
            'type' => 'text',
            'description' => __('Your merchant login ID that you created when you registered for the account.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'label' => __('Create sandbox accounts and obtain API credentials from within your <a href="http://developer.paypal.com">PayPal developer account</a>.', 'woo-paypal-payflow'),
            'default' => ''
        ),
        'premium_live_password' => array(
            'title' => __('PayPal Password', 'woo-paypal-payflow'),
            'type' => 'password',
            'description' => __('The password that you defined while registering for the account.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_live_user' => array(
            'title' => __('PayPal User', 'woo-paypal-payflow'),
            'type' => 'text',
            'description' => __('If you set up one or more additional users on the account, this value is the ID of the user authorized to process transactions. Otherwise, leave this field blank.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_live_partner' => array(
            'title' => __('PayPal Partner', 'woo-paypal-payflow'),
            'type' => 'text',
            'description' => __('The ID provided to you by the authorized PayPal Reseller who registered you for the Payflow SDK. If you purchased your account directly from PayPal, use PayPal or leave blank.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'default' => 'PayPal'
        ),    
        'premium_invoice_prefix' => array(
            'title' => __('Invoice ID Prefix', 'woo-paypal-payflow'),
            'type' => 'text',
            'description' => __('Add a prefix to the invoice ID sent to PayPal. This can resolve duplicate invoice problems when working with multiple websites on the same PayPal account.', 'pal-pro'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_action' => array(
            'title' => __('Payment Action', 'woo-paypal-payflow'),
            'type' => 'select',
            'description' => __('Choose whether you wish to capture funds immediately or authorize payment only.', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'options' => array(
                'Sale' => __('Sale', 'woo-paypal-payflow'),
                'Authorization' => __('Authorization', 'woo-paypal-payflow'),
            ),
        ),        
        'premium_debug_log' => array(
            'title' => __('Debug Log', 'woo-paypal-payflow'),
            'type' => 'checkbox',
            'description' => __('Enable Log Pal Pro', 'woo-paypal-payflow'),
            'desc_tip' => true,
            'default' => 'no'
        )
    );
}

function premium_pal_pro_payflow_notice_count($notice_type = '') {
    if (function_exists('wc_notice_count')) {
        return wc_notice_count($notice_type);
    }
    return 0;
}

function premium_pal_pro_payflow_is_card_details($posted){
    $card_number = isset($posted['pal_pro_payflow-card-number']) ? wc_clean($posted['pal_pro_payflow-card-number']) : '';
    $card_cvc = isset($posted['pal_pro_payflow-card-cvc']) ? wc_clean($posted['pal_pro_payflow-card-cvc']) : '';
    $card_expiry = isset($posted['pal_pro_payflow-card-expiry']) ? wc_clean($posted['pal_pro_payflow-card-expiry']) : '';

    // Format values
    $card_number = str_replace(array(' ', '-'), '', $card_number);
    $card_expiry = array_map('trim', explode('/', $card_expiry));
    $card_exp_month = str_pad($card_expiry[0], 2, "0", STR_PAD_LEFT);
    $card_exp_year = isset($card_expiry[1]) ? $card_expiry[1] : '';

    if (isset($_POST['pal_pro_payflow-card-start'])) {
        $card_start = wc_clean($_POST['pal_pro_payflow-card-start']);
        $card_start = array_map('trim', explode('/', $card_start));
        $card_start_month = str_pad($card_start[0], 2, "0", STR_PAD_LEFT);
        $card_start_year = $card_start[1];
    } else {
        $card_start_month = '';
        $card_start_year = '';
    }

    if (strlen($card_exp_year) == 2) {
        $card_exp_year += 2000;
    }

    if (strlen($card_start_year) == 2) {
        $card_start_year += 2000;
    }

    return (object) array(
                'number' => $card_number,
                'type' => '',
                'cvc' => $card_cvc,
                'exp_month' => $card_exp_month,
                'exp_year' => $card_exp_year,
                'start_month' => $card_start_month,
                'start_year' => $card_start_year
    );
}

function premium_pal_pro_payflow_get_user_ip(){    
   return (isset($_SERVER['HTTP_X_FORWARD_FOR']) && !empty($_SERVER['HTTP_X_FORWARD_FOR'])) ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'];
}