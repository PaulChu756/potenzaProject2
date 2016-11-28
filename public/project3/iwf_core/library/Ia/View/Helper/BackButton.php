<?php

class Ia_View_Helper_BackButton extends Zend_View_Helper_Abstract
{
    /**
     * @param  string  $message
     * @param  string $type (default=info)
     * @param  string  $heading
     * @return string
     */
    public function backButton(){
        return '<a class="btn btn-default" href="?returnHome"><i class="glyphicon glyphicon-arrow-left"></i> Go Back</a>';
    }

}