<?php

class Ia_View_Helper_HeadLink extends Zend_View_Helper_HeadLink
{

    /**
     * Create HTML link element from data item
     *
     * @param  stdClass $item
     * @return string
     */
    public function itemToString(stdClass $item)
    {
        if(isset($item->href) && strlen($item->href)>0){
            /* Deploy scripts should touch the document root folder to ensure timestamps are refreshed */
            $version = filemtime(realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..'));
            if(strpos($item->href, '?')===false)
                $item->href = $item->href.'?v='.$version;
            else
                $item->href = $item->href.'&v='.$version;
            if(strpos($item->href,'//')===false && \Ia\Config::get('cloudfront_domain'))
                $item->href = '//'.\Ia\Config::get('cloudfront_domain').$item->href;
        }
        return parent::itemToString($item);
    }

}