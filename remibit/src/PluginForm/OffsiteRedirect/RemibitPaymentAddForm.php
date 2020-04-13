<?php

namespace Drupal\remibit\PluginForm\OffsiteRedirect;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Url;
use Drupal\profile\Entity\Profile;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RemibitPaymentAddForm extends BasePaymentOffsiteForm
{
    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @return array|void
     */
    public function buildConfigurationForm(array $form, FormStateInterface $form_state)
    {
        $form = parent::buildConfigurationForm($form, $form_state);
        /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
        $payment = $this->entity;
        /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
        $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
        $configuration      = $payment_gateway_plugin->getConfiguration();
        $url                = Url::fromRoute('remibit.checkout_return', [], ['absolute' => TRUE])->toString();
        $order_id           = $payment->getOrderId();
        $timeStamp          = time();
        $order_total        = $payment->getBalance()->getNumber();
        $currency           = $payment->getBalance()->getCurrencyCode();
        $transactionKey     = $configuration['transaction_key'];
        $order              = $payment->getOrder();
        $billing_address    = $order->getBillingProfile()->get('address')->get(0)->getValue();
        
        if (function_exists('hash_hmac')) {
            $hash_d = hash_hmac('md5', sprintf('%s^%s^%s^%s^%s',
                $configuration['login_id'],
                $order_id,
                $timeStamp,
                $order_total,
                $currency
            ), $transactionKey);
        } else {
            $hash_d = bin2hex(mhash(MHASH_MD5, sprintf('%s^%s^%s^%s^%s',
                $configuration['login_id'],
                $order_id,
                $timeStamp,
                $order_total,
                $currency
            ), $transactionKey));
        }

        $params = array(
            'x_invoice_num'         => $order_id,
            'x_amount'              => $order_total,
            'x_login'               => $configuration['login_id'],
            'x_relay_response'      => 'TRUE',
            'x_fp_sequence'         => $order_id,
            'x_show_form'           => 'PAYMENT_FORM',
            'x_relay_url'           => $url,
            'x_fp_hash'             => $hash_d,
            'x_version'             => '1.0',
            'x_type'                => 'AUTH_CAPTURE',
            'x_currency_code'       => $currency,
            'x_fp_timestamp'        => $timeStamp,
            'x_first_name'          => $billing_address['given_name'],
            'x_last_name'           => $billing_address['family_name'],
            'x_company'             => $billing_address['organization'],
            'x_address'             => $billing_address['address_line1'].' '.$billing_address['address_line2'],
            'x_state'               => $billing_address['administrative_area'],
            'x_city'                => $billing_address['locality'],
            'x_zip'                 => $billing_address['postal_code'],
            'x_cancel_url_text'     => 'Cancel Payment',
            'x_test_request'        => 'FALSE',
            'x_cancel_url'          => $form['#cancel_url'],
            'x_country'             => '',
            'x_phone'               => '',
            'x_email'               => $order->getCustomer()->getEmail(),
            'x_tax'                 => '',
            'x_ship_to_first_name'  => '',
            'x_ship_to_last_name'   => '',
            'x_ship_to_country'     => '',
            'x_ship_to_company'     => '',
            'x_ship_to_address'     => '',
            'x_ship_to_city'        => '',
            'x_ship_to_state'       => '',
            'x_ship_to_zip'         => '',
        );
        $_SESSION['remibit']['form']         =$form;
        $_SESSION['remibit']['form_state']   =$form_state;
        $_SESSION['remibit']['payment']      =$payment;

        $post_string = array();

        foreach ($params as $key => $value) {
            $post_string[] = "<input type='hidden' name='$key' value='$value'/>";
        }

        $url = $configuration['gateway_url'];

        $loading = ' <div style="width: 100%; height: 100%;top: 50%; padding-top: 10px;padding-left: 10px;  left: 50%; transform: translate(40%, 40%)"><div style="width: 150px;height: 150px;border-top: #CC0000 solid 5px; border-radius: 50%;animation: a1 2s linear infinite;position: absolute"></div> </div> <style>*{overflow: hidden;}@keyframes a1 {to{transform: rotate(360deg)}}</style>';

        $html_form = '<form action="' . $url . '" method="post" id="authorize_payment_form">' . implode('', $post_string) . '<input type="submit" id="submit_authorize_payment_form" style="display: none"/>' . $loading . '</form><script>document.getElementById("submit_authorize_payment_form").click();</script>';

        echo $html_form;

        die;
    }


}
