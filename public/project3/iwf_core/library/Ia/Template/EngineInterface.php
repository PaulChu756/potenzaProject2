<?php
namespace Ia\Template;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
interface EngineInterface
{

    public function render($content='',$vars=array());       

}