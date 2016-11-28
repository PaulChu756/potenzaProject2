<?php
namespace Ia\Payment\Stored;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */

class TransaX extends \Ia\Payment\StoredAbstract implements \Ia\Payment\StoredInterface {

    const TRANSAX_STATUS_SUCCESS = 1;

    const TRANSAX_SEC_CODE = 'CCD';

    protected $_config = array();

    public static function getKey()
    {
        return 'transax';
    }

    public function __construct(array $config)
    {
        if(isset($config['GatewayUserName']) && isset($config['GatewayPassword'])){
            $this->_config = $config;
            return $this;
        }
        throw new \Exception('Missing one of the required parameters in config: GatewayUserName, GatewayPassword');
    }

    public function retrieveCustomerToken($email,$firstName,$lastName,$description)
    {
        //transax does not tokenize customers, so we return our own scheme
        return 'transax_'.md5($email);
    }

    protected function _formatExpire($expire)
    {
        $parts = explode('/',$expire);
        $parts[0] = sprintf("%02s", $parts[0]);
        $parts[1] = sprintf("%02s", substr($parts[1], 2, 2));
        return implode('',$parts);
    }

    public function retrievePaymentToken($customerProfileId,$billTo,$payment,$type){

        if(!in_array($type, array(\Ia\Payment\StoredInterface::PAYMENT_TYPE_CC,\Ia\Payment\StoredInterface::PAYMENT_TYPE_ACH)))
            throw new \Exception('Payment type '.$type.' not yet implemented');        

        $values['GatewayUserName'] = $this->_config['GatewayUserName'];
        $values['GatewayPassword'] = $this->_config['GatewayPassword'];

        switch($type){
            case \Ia\Payment\StoredInterface::PAYMENT_TYPE_ACH:
                $values['PaymentType'] = 'check';
                if(isset($payment['account_holder_type'])){
                    $values['TransactionType'] = 'credit';
                    if(APPLICATION_ENV=='production')
                        $values['Amount'] = '0.01';
                    else
                        $values['Amount'] = '1.02';
                    $values['AccountHolderType'] = $payment['account_holder_type'];
                    $values['AccountType'] = $payment['account_type'];
                    $values['CheckABA'] = $payment['routing_number'];
                    $values['CheckAccount'] = $payment['bank_account_number'];
                    $values['CheckName'] = \Ia\Config::get('title').' - Test Transaction';
                }
                break;
            case \Ia\Payment\StoredInterface::PAYMENT_TYPE_CC;
                $values['PaymentType'] = 'creditcard';
                if(isset($payment['cc_number'])){
                    $values['TransactionType'] = 'auth';
                    if(APPLICATION_ENV=='production')
                        $values['Amount'] = '0.01';
                    else
                        $values['Amount'] = '1.01'; 
                    $values['CCNumber'] = $payment['cc_number'];
                    $values['CCExpDate'] = $this->_formatExpire($payment['cc_expire']);
                    $values['CVV'] = $payment['cc_cvv'];
                }
                break;
        }

        if(isset($payment['safe_id']))
            $values['SAFE_ID'] = $payment['safe_id'];

        $values['SecCode'] = self::TRANSAX_SEC_CODE;
        $values['FirstName'] = (isset($billTo['first_name'])) ? $billTo['first_name'] : '';
        $values['LastName'] = (isset($billTo['last_name'])) ? $billTo['last_name'] : '';
        $values['Company'] = (isset($billTo['company'])) ? $billTo['company'] : '';
        $values['Address1'] = (isset($billTo['address_line_1'])) ? $billTo['address_line_1'] : '';
        $values['Address2'] = (isset($billTo['address_line_2'])) ? $billTo['address_line_2'] : '';
        $values['City'] = (isset($billTo['city'])) ? $billTo['city'] : '';
        $values['State'] = (isset($billTo['state'])) ? $billTo['state'] : '';
        $values['Zip'] = (isset($billTo['zip'])) ? $billTo['zip'] : '';
        $values['Country'] = (isset($billTo['country'])) ? $billTo['country'] : '';
        $values['Phone'] = (isset($billTo['phone'])) ? $billTo['phone'] : '';
        $values['Fax'] = (isset($billTo['fax'])) ? $billTo['fax'] : '';
        $values['EMail'] = (isset($billTo['email_address'])) ? $billTo['email_address'] : '';

        if(isset($payment['safe_action']) && $payment['safe_action'] == 'update_safe'){
            //skip validation on update
        } else {
            $client = @new \SoapClient("https://secure.transaxgateway.com/roxapi/rox.asmx?WSDL");
            $res = $client->ProcessTransaction(array("objparameters",objparameters => $values));

            if($res->ProcessTransactionResult->STATUS_CODE != self::TRANSAX_STATUS_SUCCESS)
                throw new \Exception('Gateway Error: '.$res->ProcessTransactionResult->STATUS_MSG);
        }

        //safe
        unset($values['TransactionType']);
        unset($values['Amount']);

        if(isset($payment['safe_action']))
            $values['SAFE_Action'] = $payment['safe_action']; 
        else        
            $values['SAFE_Action'] = 'add_safe';

        $arrValues = array("objparameters",objparameters => $values);
        $client = @new \SoapClient("https://secure.transaxgateway.com/roxapi/rox.asmx?WSDL");
        $res = $client->ProcessTransaction($arrValues);

        if($res->ProcessTransactionResult->STATUS_CODE != self::TRANSAX_STATUS_SUCCESS)
            throw new \Exception('Safe Error: '.$res->ProcessTransactionResult->STATUS_MSG);

        return $res->ProcessTransactionResult->SAFE_ID;
    }

    public function authCapture($safeId,$amount,$values=array())
    {
        $values['GatewayUserName'] = $this->_config['GatewayUserName'];
        $values['GatewayPassword'] = $this->_config['GatewayPassword'];
        $values['TransactionType'] = 'sale';
        $values['Amount'] = $amount;
        $values['SAFE_ID'] = $safeId;

        $client = @new \SoapClient("https://secure.transaxgateway.com/roxapi/rox.asmx?WSDL");
        $res = $client->ProcessTransaction(array("objparameters",objparameters => $values));

        if($res->ProcessTransactionResult->STATUS_CODE != self::TRANSAX_STATUS_SUCCESS)
            throw new \Exception('Gateway Error: '.$res->ProcessTransactionResult->FULLRESPONSE);

        return $res->ProcessTransactionResult->TRANS_ID;
    }

    public function credit($safeId,$amount)
    {
        $values['GatewayUserName'] = $this->_config['GatewayUserName'];
        $values['GatewayPassword'] = $this->_config['GatewayPassword'];
        $values['TransactionType'] = 'credit';
        $values['Amount'] = $amount;
        $values['SAFE_ID'] = $safeId;

        $client = @new \SoapClient("https://secure.transaxgateway.com/roxapi/rox.asmx?WSDL");
        $res = $client->ProcessTransaction(array("objparameters",objparameters => $values));

        if($res->ProcessTransactionResult->STATUS_CODE != self::TRANSAX_STATUS_SUCCESS)
            throw new \Exception('Gateway Error: '.$res->ProcessTransactionResult->FULLRESPONSE);

        return $res->ProcessTransactionResult->TRANS_ID;
    }    

}