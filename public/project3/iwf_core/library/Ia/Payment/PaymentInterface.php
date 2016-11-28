<?php
namespace Ia\Payment;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */

interface PaymentInterface {

    /**
     * Credit card
     */
    const PAYMENT_TYPE_CC = 'cc';

    /**
     * ACH
     */    
    const PAYMENT_TYPE_ACH = 'ach';

    /**
     * ACH : business
     */    
    const PAYMENT_TYPE_ACH_BUSINESS = 'business';

    /**
     * ACH : personal
     */    
    const PAYMENT_TYPE_ACH_PERSONAL = 'personal';

    /**
     * ACH : checking
     */    
    const PAYMENT_TYPE_ACH_CHECKING = 'checking';

    /**
     * ACH : savings
     */    
    const PAYMENT_TYPE_ACH_SAVINGS = 'savings';

    /** 
     * return @string
     * Unique identifier for payment class
     */
    public static function getKey();

}