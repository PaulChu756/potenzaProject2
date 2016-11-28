<?php
/**
 * Information ArchiTECH, LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@informationarchitech.com so we can send you a copy immediately.
 *
 * This plugin allows you to select a different layout based on http host
 * e.g. (in application.ini)
 * layouts[domain.com] = domain-layout.phtml
 *
 * @copyright  Copyright (c) 2014 Information ArchiTECH, LLC (http://www.informationarchitech.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Information ArchiTECH <contact@informationarchitech.com>
 */
 
class Ia_Controller_Plugin_CloudFront extends Zend_Controller_Plugin_Abstract
{
    
    public function dispatchLoopShutdown()
    {
        if(!\Ia\Config::get('cloudfront_domain'))
            return;
        
        $response = $this->getResponse();
        $body = $response->getBody();

        preg_match_all('/<img[^>]+>/i',$body, $results); 
        $body = $this->_processCloudfrontLinks($body,$results[0],'src');
        
        preg_match_all('/<script[^>]+>/i',$body, $results); 
        $body = $this->_processCloudfrontLinks($body,$results[0],'src');
        
        preg_match_all('/<link[^>]+>/i',$body, $results); 
        $body = $this->_processCloudfrontLinks($body,$results[0],'href');
        
        if($this->_changed){
            $response->setBody($body);
        }
        
        return;
        
    }
    
    protected $_changed = false;
    
    protected function _processCloudfrontLinks($body,$results,$attr='src')
    {
        foreach($results as $result){
            //echo $result.chr(10);
            if(strpos($result,$attr.'="/')!==false){
                $newResult = str_replace($attr.'="/',$attr.'="'.\Ia\Config::get('cloudfront_domain').'/', $result);
                if(strpos($newResult,'//')==false){
                    $this->_changed = true;
                    $newResult = str_replace($attr.'="',$attr.'="//', $newResult);
                    $body = str_replace($result,$newResult,$body);
                }
            }
        }
        return $body;
    }

}

