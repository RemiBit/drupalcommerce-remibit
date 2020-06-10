<?php

namespace Drupal\remibit\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the QuickPay offsite Checkout payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "remibit",
 *   label = "REMIBIT Payment Method",
 *   display_label = "REMIBIT",
 *   forms = {
 *     "offsite-payment" = "Drupal\remibit\PluginForm\OffsiteRedirect\RemibitPaymentAddForm",
 *   },
 *   payment_method_types = {"credit_card"},
 *   modes = {
 *      "Live" = @Translation("Live"),
 *   },
 *   requires_billing_information = FALSE
 * )
 */
class remibit extends OffsitePaymentGatewayBase {

    /**
     * {@inheritdoc}
     */

    public function defaultConfiguration() {
        return [
                'login_id' => '',
                'transaction_key' => '',
                'signature_key' => '',
                'md5_hash' => '',
                'gateway_url' => 'https://app.remibit.com/pay',
            ] + parent::defaultConfiguration();
    }

    public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
        $form = parent::buildConfigurationForm($form, $form_state);
		$form['display_label'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Display Name'),
//            '#description' => $this->t('This is the private key from the Quickpay manager.'),
            '#default_value' => $this->configuration['display_label'],
            '#required' => TRUE,
        ];
		
        $form['login_id'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Login ID'),
//            '#description' => $this->t('This is the private key from the Quickpay manager.'),
            '#default_value' => $this->configuration['login_id'],
            '#required' => TRUE,
        ];

        $form['transaction_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Transaction key'),
//            '#description' => $this->t('The API key for the same user as used in Agreement ID.'),
            '#default_value' => $this->configuration['transaction_key'],
            '#required' => TRUE,
        ];

        $form['signature_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Signature key'),
//            '#description' => $this->t('The API key for the same user as used in Agreement ID.'),
            '#default_value' => $this->configuration['signature_key'],
            '#required' => TRUE,
        ];

        $form['md5_hash'] = [
            '#type' => 'textfield',
            '#title' => $this->t('MD5 Hash Value'),
//            '#description' => $this->t('The API key for the same user as used in Agreement ID.'),
            '#default_value' => $this->configuration['md5_hash'],
            '#required' => TRUE,
        ];

        $form['gateway_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Endpoint URL'),
//            '#description' => $this->t('The API key for the same user as used in Agreement ID.'),
            '#default_value' => $this->configuration['gateway_url'],
            '#required' => TRUE,
        ];

        return $form;
    }


    public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
        parent::submitConfigurationForm($form, $form_state);

        if (!$form_state->getErrors()) {
            $values = $form_state->getValue($form['#parents']);
            $this->configuration['login_id']        = $values['login_id'];
            $this->configuration['transaction_key'] = $values['transaction_key'];
            $this->configuration['signature_key']   = $values['signature_key'];
            $this->configuration['md5_hash']        = $values['md5_hash'];
            $this->configuration['gateway_url']     = $values['gateway_url'];
        }
    }

    public function onReturn(OrderInterface $order, Request $request) {
        $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
        $payment = $payment_storage->create([
            'state'             => 'authorization',
            'amount'            => $order->getBalance(),
            'payment_gateway'   => $this->entityId,
            'order_id'          => $order->id(),
            'remote_id'         => 'tx: '.$_SESSION['remibit']['tx'],
            'remote_state'      => $request->query->get('payment_status'),
        ]);
	    unset($_SESSION['remibit']);
        $payment->save();
    }

}
