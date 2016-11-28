<?php

class Ia_View_Helper_Alert extends Zend_View_Helper_Abstract
{
    /**
     * @param  string  $message
     * @param  string $type (default=info)
     * @param  string  $heading
     * @return string
     */
    public function alert($message, $type = 'info', $heading=null){ 
        self::addAlert($message,$type,$heading);
        return self::showAlerts();
    }
    
    public static function addAlert($message, $type = 'info', $heading=null, $additional=array()){
        $type = ($type=='error') ? 'danger' : $type;
        $type = ($type=='information') ? 'info' : $type;
        $session = new Zend_Session_Namespace('Ia_View_Helper_Alert');
        if(!isset($session->alerts))
            $session->alerts = array();
        foreach($session->alerts as $alert)
            if($message == $alert['message'] && $type==$alert['type'] && $heading==$alert['heading'] && $additional==$alert['additional'])
                $alreadyAdded = true;
        if(!$alreadyAdded)
            $session->alerts[] = array('message'=>$message,'type'=>$type,'heading'=>$heading,'additional'=>$additional);            
    }

    public static function clearAlerts()
    {
        $session = new Zend_Session_Namespace('Ia_View_Helper_Alert');
        $session->alerts = array();
    }
    
    public static function showAlerts(){
        $session = new Zend_Session_Namespace('Ia_View_Helper_Alert');
        if(!isset($session->alerts))
            $session->alerts = array();
        if(sizeof($session->alerts)==0)
            return '';
        $xhtml = '';
        foreach($session->alerts as $key=>$alert){
            $xhtml .= '<div class="alert alert-block alert-'.$alert['type'].' alert-dismissable">'.
                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.
                (($alert['heading']==null) ? '' : '<h4 class="alert-heading">'.$alert['heading'].'</h4>').
                $alert['message'] .
                '</div>';
            unset($session->alerts[$key]);        
        }
        return $xhtml;
    }

    public static function toJson(){
        $session = new Zend_Session_Namespace('Ia_View_Helper_Alert');
        if(!isset($session->alerts))
            $session->alerts = array();
        $json = json_encode($session->alerts);
        foreach($session->alerts as $key=>$alert){
            unset($session->alerts[$key]);        
        }
        return $json;   
    }
    
    public static function exitToJson(){
        header('Content-Type: application/json');
        echo self::toJson();
        exit;
    }

}
