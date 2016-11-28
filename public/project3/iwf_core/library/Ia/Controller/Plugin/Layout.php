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
 
class Ia_Controller_Plugin_Layout extends Zend_Controller_Plugin_Abstract
{
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if($layouts = \Ia\Config::get('layouts')){
            if(isset($layouts[$request->getHttpHost()])){
                $layout = Zend_Layout::getMvcInstance();
                $layout->setLayout($layouts[$request->getHttpHost()]);
            }
        }
        /*echo $request->getHttpHost();
        echo '<pre>'.print_r(\Ia\Config::get('layouts'),1).'</pre>';
        exit;*/
    }

}