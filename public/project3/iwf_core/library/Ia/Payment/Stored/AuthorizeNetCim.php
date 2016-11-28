<?php
namespace Ia\Payment\Stored;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */

class AuthorizeNetCim extends \Ia\Payment\StoredAbstract implements \Ia\Payment\StoredInterface {

    protected $_config = array();

    protected $_cimClass = null;

    public static function getKey()
    {
        return 'anetcim';
    }

    public function __construct(array $config)
    {
        if(isset($config['login_id']) && isset($config['transaction_key']) && isset($config['sandbox'])){
            $this->_config = $config;
            return $this;
        }
        throw new \Exception('Missing one of the required parameters in config: login_id, transaction_key, sandbox');
    }

    protected function _loadLibrary()
    {
        $fullPath = realpath(APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'library'.
                                DIRECTORY_SEPARATOR.'anet_php_sdk'.DIRECTORY_SEPARATOR.'autoload.php');
        if(!$fullPath)
            throw new \Exception('Could not locate Authorize.NET CIM library');
        require_once($fullPath);
    }

    /**
     * Creates a new customer profile from Authorize.NET
     * and returns the customer profile ID
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $description - custom string on our end with user ID
     * @return string - ID of the customer profile
     * @throws \Exception if response is not okay, we throw the exception
     */
    public function retrieveCustomerToken($email,$firstName,$lastName,$description)
    {
        $cimClass = $this->getCimClass();
        $customerProfile = new \AuthorizeNetCustomer;
        $customerProfile->description        = $description;
        $customerProfile->merchantCustomerId = time();
        $customerProfile->email              = $email;
        $response = $cimClass->createCustomerProfile($customerProfile);
        if ($response->isOk()) {
            return $response->getCustomerProfileId();
        }
        throw new \Exception((string) $response->xml->messages->message->text);
    }

    /**
     * Generates a new customer payment profile token for the user
     * @param $customerProfileId - customer token
     * @param $billTo - address form
     * @param $payment - payment form
     * @param $type - payment type
     * @return \SimpleXMLElement[] - returns customer profile id
     * @throws \Exception
     */
    public function retrievePaymentToken($customerProfileId,$billTo,$payment,$type){

        if(!in_array($type, array(\Ia\Payment\StoredInterface::PAYMENT_TYPE_CC)))
            throw new \Exception('Payment type '.$type.' not yet implemented');

        $cimClass = $this->getCimClass();

        $paymentProfile = $this->preparePaymentProfile($billTo, $payment);

        $response = $cimClass->createCustomerPaymentProfile($customerProfileId, $paymentProfile, "none");
        if ($response->isOk()) {
            return $response->xml->customerPaymentProfileId;
        }
        throw new \Exception($response->xml->messages->message->text);
    }

    public function updatePaymentProfile($customerProfileId, $customerPaymentProfileId, $billTo, $payment, $type)
    {
        if(!in_array($type, array(\Ia\Payment\StoredInterface::PAYMENT_TYPE_CC)))
            throw new \Exception('Payment type '.$type.' not yet implemented');

        $cimClass = $this->getCimClass();
        $paymentProfile = $this->preparePaymentProfile($billTo, $payment);

        $response = $cimClass->updateCustomerPaymentProfile($customerProfileId, $customerPaymentProfileId, $paymentProfile, "none");
        if ($response->isOk()) {
            return $response->xml->customerPaymentProfileId;
        }
        throw new \Exception($response->xml->messages->message->text);
    }

    public function getCimClass()
    {
        if($this->_cimClass === null){
            $this->_loadLibrary();
            define("AUTHORIZENET_API_LOGIN_ID", $this->_config['login_id']);
            define("AUTHORIZENET_TRANSACTION_KEY", $this->_config['transaction_key']);
            define("AUTHORIZENET_SANDBOX", ($this->_config['sandbox'] ? true : false));
            $this->_cimClass = new \AuthorizeNetCIM($this->_config['login_id'], $this->_config['transaction_key']);
        }
        return $this->_cimClass;
    }

    public function authCapture($customerToken,$paymentToken,$amount,$values=array())
    {
        $cimClass = $this->getCimClass();
        // Create Auth & Capture Transaction
        $transaction = new \AuthorizeNetTransaction;
        $transaction->amount = $amount;
        $transaction->customerProfileId = $customerToken;
        $transaction->customerPaymentProfileId = $paymentToken;
        $response = $cimClass->createCustomerProfileTransaction("AuthCapture", $transaction);
        return $response;
    }

    /**
     * @param $billTo - Address Information from form
     * @param $payment - Payment information from form
     * @return \AuthorizeNetPaymentProfile
     */
    protected function preparePaymentProfile($billTo, $payment)
    {
        $address = new \AuthorizeNetAddress;
        $address->firstName = (isset($billTo['first_name'])) ? $billTo['first_name'] : '';
        $address->lastName = (isset($billTo['last_name'])) ? $billTo['last_name'] : '';
        $address->company = (isset($billTo['company'])) ? $billTo['company'] : '';
        $address->address = ((isset($billTo['address_line_1'])) ? $billTo['address_line_1'] : '') . ((isset($billTo['address_line_2'])) ? ' ' . $billTo['address_line_2'] : '');
        $address->city = (isset($billTo['city'])) ? $billTo['city'] : '';
        $address->state = (isset($billTo['state'])) ? $billTo['state'] : '';
        $address->zip = (isset($billTo['zip'])) ? $billTo['zip'] : '';
        $address->country = (isset($billTo['country'])) ? $billTo['country'] : '';
        $address->phoneNumber = (isset($billTo['phone'])) ? $billTo['phone'] : '';
        $address->faxNumber = (isset($billTo['fax'])) ? $billTo['fax'] : '';
        //$address->customerAddressId;

        $creditCard = new \AuthorizeNetCreditCard;
        $creditCard->cardNumber = $payment['cc_number'];

        $expires = \DateTime::createFromFormat('m/Y', $payment['cc_expire']);
        $creditCard->expirationDate = $expires->format('Y-m');

        $creditCard->cardCode = $payment['cc_cvv'];

        $payment = new \AuthorizeNetPayment;
        $payment->creditCard = $creditCard;

        $paymentProfile = new \AuthorizeNetPaymentProfile;
        $paymentProfile->billTo = $address;
        $paymentProfile->payment = $payment;

        return $paymentProfile;
    }

}