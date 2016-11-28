<?php

class ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            // check for any other exception
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:
                if ($errors->exception instanceof Ia_Exception_Forbidden) {
                    $this->getResponse()->setHttpResponseCode(403);
                    $this->view->message = $errors->exception->getMessage();
                    break;
                }
                // fall through if not of type Ia_Exception_Forbidden              
            default:
                $requestParams = $this->getRequest()->getParams();
                if(get_class($errors->exception)=='Ia_Exception_NotFound'){
                    $this->getResponse()->setHttpResponseCode(404);
                    $this->view->message = 'Page not found';
                    if(isset($requestParams['format']) && ($requestParams['format'] == 'json')){
                       return $this->_helper->json(array('exception_info'=>$errors->exception->getMessage()), true);
                    } else {
                        $this->_helper->layout()->disableLayout();
                        $this->_helper->viewRenderer('resource_not_found');
                    }
                } else {
                    // application error
                    $this->getResponse()->setHttpResponseCode(500);
                    $priority = Zend_Log::CRIT;
                    $this->view->message = 'An automated E-mail has been sent to your development team, who will be looking into this shortly.';
                    if(APPLICATION_ENV=='production'){
                        $options = \Ia\Config::get('resources/mail');
                        // Default email id for exceptions
                        $adminEmail = (isset($options['error']['email'])) ? $options['error']['email'] : "aaron@informationarchitech.com"; 
                        $mail = new \Zend_Mail();

                        unset($requestParams['error_handler']);
                        ob_start();
                        ?>
                            <h3>Exception information:</h3>
                            <p>
                              <b>Message:</b> <?php echo $errors->exception->getMessage() ?>
                            </p>

                            <h3>Stack trace:</h3>
                            <pre><?php echo $errors->exception->getTraceAsString() ?>
                            </pre>

                            <h3>Request Parameters:</h3>
                            <pre><?php echo print_r($requestParams,1);?></pre>

                            <h3>$_SERVER Dump:</h3>
                            <pre><?=print_r($_SERVER,1);?></pre>
                        <?php
                        $html = ob_get_contents();
                        ob_end_clean();
                        if(!isset($requestParams['emailExceptions']) || $requestParams['emailExceptions']){
                            $mail->setBodyHtml($html);
                            $mail->setFrom($options['defaultFrom']['email']);
                            $mail->addTo($adminEmail);
                            $mail->setSubject('['.$errors->request->getHttpHost().'] Application Error');
                            $mail->send();  
                        }			
                    }               
                    if(isset($requestParams['format']) && ($requestParams['format'] == 'json')){
                       return $this->_helper->json(array('exception_info'=>$errors->exception->getMessage()), true);
                    }
                }
                break;
        }

        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

