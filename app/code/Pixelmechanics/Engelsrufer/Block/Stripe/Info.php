<?php
/**
* display the value of the credit card in the admin and email 
* extend by N.A on 5.11.19
* trello todo:https://trello-attachments.s3.amazonaws.com/5c7fce608f73ec77926941d7/5c86d83c4ea2116b3e541d88/fd0d88d7cde59957bdf3692470993d19/image.png 
*/
namespace Pixelmechanics\Engelsrufer\Block\Stripe;

use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Model\Order\Payment;
//use Stripeofficial\Core\Api\PaymentInterface;

class Info extends ConfigurableInfo
{
    /**
     * @var PaymentInterface
     */
    protected $creditCardPayment;

    /**
     * @var State
     */
    protected $state;

    /**
     * Info constructor.
     * @param Context $context
     * @param ConfigInterface $config
     * @param PaymentInterface $creditCardPayment
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        //PaymentInterface $creditCardPayment,
        array $data = []
    ) {
        $this->state = $context->getAppState();
        parent::__construct($context, $config, $data);
        $this->creditCardPayment = $creditCardPayment;
    }

    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Get some specific information in format of array($label => $value)
     *
     * @return array
     * @throws LocalizedException
     */
    public function getSpecificInformation()
    {
        $info = parent::getSpecificInformation();
        /*
         * Updated by AA on 8.11.2019
         * https://trello.com/c/sIPgox5k/111-042einrichtung-bezahlmethoden-setup-payment-methods-stripe#comment-5dc3f17bce6fba04adcefffa
         * Override file for credit card
         * Credit card is secure payment method so put value as 1
         */
        //$secureCheck = $this->getIsSecureMode();
        $secureCheck = 1;
        if ($secureCheck) {

            /** @var Payment $payment */
            $payment = $this->getInfo();

            try {
                $chargeId = $payment->getAdditionalInformation('base_charge_id');
                $charge = $this->creditCardPayment->getCharge($chargeId)->jsonSerialize();
            } catch (\Exception $e) {
            }

            if (empty($charge)) {
                return $info;
            }

            $additional = [];

            // Check if 3ds is authenticated or not a
            if ($charge['source']['type'] == 'three_d_secure') {
                if ($charge['source']['three_d_secure']['authenticated'] == true) {
                    $additional['3DS Authenticated'] = __('Yes');
                } else {
                    $additional['3DS Authenticated'] = __('No');
                }

                $card = $charge['source']['three_d_secure']['card'];
                $source = $this->creditCardPayment->getSource($card)->jsonSerialize();
                $additional['Last 4 digits'] = $source['card']['last4'];
            }

            if ($charge['source']['type'] == 'card') {
                /*add the Stripe Credit card name and XXXX values*/
                $additional[$charge['source']['card']['brand']] = 'XXXX XXXX XXXX '.$charge['source']['card']['last4'];
            }
            /*
             * Commented the charge ID and Source ID
             * https://trello.com/c/sIPgox5k/111-042einrichtung-bezahlmethoden-setup-payment-methods-stripe#comment-5dc2b52aa8106d8fcc0eed8b
             * Updated by AA on 6.11.2019
             */

//            if (!empty($charge['id'])) {
//                $additional['Charge ID'] = $charge['id'];
//                $additional['Source ID'] = $charge['source']['id'];
//            }
            
            $info = array_merge($info, $additional);
        }

        return $info;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function getIsSecureMode()
    {
        $method = $this->getMethod();

        if (!$method) {
            return true;
        }

        return $this->state->getAreaCode() === 'adminhtml';
    }
}
