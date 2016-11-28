<?php

class Ia_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{

        /**
     * Create script HTML
     *
     * @param  string $type
     * @param  array $attributes
     * @param  string $content
     * @param  string|int $indent
     * @return string
     */
    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        if(isset($item->attributes['src']) && strlen($item->attributes['src'])>0){
            /* Deploy scripts should touch the document root folder to ensure timestamps are refreshed */
            $version = filemtime(realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..'));
            if(strpos($item->attributes['src'], '?')===false)
                $item->attributes['src'] = $item->attributes['src'].'?v='.$version;
            else
                $item->attributes['src'] = $item->attributes['src'].'&v='.$version;
        }
        return parent::itemToString($item, $indent, $escapeStart, $escapeEnd);
    }

}