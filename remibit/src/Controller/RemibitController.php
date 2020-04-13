<?php

namespace Drupal\remibit\Controller;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use http\Env\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RemibitController implements ContainerInjectionInterface
{
    /**
     * The current request.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $currentRequest;

    /**
     * Constructs a new DummyRedirectController object.
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
     *   The request stack.
     */
    public function __construct(RequestStack $request_stack)
    {
        $this->currentRequest = $request_stack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('request_stack')
        );
    }

    private function validate()
    {
        if(isset($_POST['x_trans_id'])){
            $payment = $_SESSION['remibit']['payment'];
            /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
            $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
            $configuration = $payment_gateway_plugin->getConfiguration();
            $hashData = implode('^', [
                $_POST['x_trans_id'],
                $_POST['x_test_request'],
                $_POST['x_response_code'],
                $_POST['x_auth_code'],
                $_POST['x_cvv2_resp_code'],
                $_POST['x_cavv_response'],
                $_POST['x_avs_code'],
                $_POST['x_method'],
                $_POST['x_account_number'],
                $_POST['x_amount'],
                $_POST['x_company'],
                $_POST['x_first_name'],
                $_POST['x_last_name'],
                $_POST['x_address'],
                $_POST['x_city'],
                $_POST['x_state'],
                $_POST['x_zip'],
                $_POST['x_country'],
                $_POST['x_phone'],
                $_POST['x_fax'],
                $_POST['x_email'],
                $_POST['x_ship_to_company'],
                $_POST['x_ship_to_first_name'],
                $_POST['x_ship_to_last_name'],
                $_POST['x_ship_to_address'],
                $_POST['x_ship_to_city'],
                $_POST['x_ship_to_state'],
                $_POST['x_ship_to_zip'],
                $_POST['x_ship_to_country'],
                $_POST['x_invoice_num'],
            ]);

            $digest = strtoupper(hash_hmac('sha512', "^" . $hashData . "^", hex2bin($configuration['signature_key'])));
            if ($_POST['x_response_code'] == 1 && (strtoupper($_POST['x_SHA2_Hash']) == $digest)) {
                return true;
            } else {
                return false;
            }
        }else{
            return false;
        }

    }

    public function proceedPayment()
    {
        if($this->validate())
        {
	    	$_SESSION['remibit']['tx'] = $_POST['x_trans_id'];
            $return = $_SESSION['remibit']['form']['#return_url'];
	
            return new TrustedRedirectResponse($return);
        } else {
            $cancel = $_SESSION['remibit']['form']['#cancel_url'];
            return new TrustedRedirectResponse($cancel);
        }
    }

    /**
     * Callback method which accepts POST.
     *
     * @throws \Drupal\commerce\Response\NeedsRedirectException
     */
    public function checkoutReturn()
    {
        $post_string = array();

        foreach ($_POST as $key => $value) {
            $post_string[] = "<input type='hidden' name='$key' value='$value'/>";
        }

        $url = Url::fromRoute('remibit.checkout_procced', [], ['absolute' => TRUE])->toString();

        $loading = ' <div style="width: 100%; height: 100%;top: 50%; padding-top: 10px;padding-left: 10px;  left: 50%; transform: translate(40%, 40%)"><div style="width: 150px;height: 150px;border-top: #CC0000 solid 5px; border-radius: 50%;animation: a1 2s linear infinite;position: absolute"></div> </div> <style>*{overflow: hidden;}@keyframes a1 {to{transform: rotate(360deg)}}</style>';

        $html_form = '<form action="' . $url . '" method="post" id="authorize_payment_form">' . implode('', $post_string) . '<input type="submit" id="submit_authorize_payment_form" style="display: none"/>' . $loading . '</form><script>document.getElementById("submit_authorize_payment_form").click();</script>';

        echo $html_form;

        die;
    }
}
