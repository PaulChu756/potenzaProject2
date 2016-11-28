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
 *
 * @copyright  Copyright (c) 2014 Information ArchiTECH, LLC (http://www.informationarchitech.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Information ArchiTECH <contact@informationarchitech.com>
 */

class Ia_Controller_Plugin_RequireSsl extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if(\Ia\Config::get('require_ssl')){
            $server = $request->getServer();
            $hostname = $server['HTTP_HOST'];
            if (!$request->isSecure()) {
                $url = Zend_Controller_Request_Http::SCHEME_HTTPS . "://" . $hostname . $request->getPathInfo();
                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $redirector->setGoToUrl($url);
                $redirector->redirectAndExit();
            }
        }                      
    }
        
}