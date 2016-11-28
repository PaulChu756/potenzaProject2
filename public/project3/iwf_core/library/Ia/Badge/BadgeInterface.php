<?php
namespace Ia\Badge;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
interface BadgeInterface
{

    /**
     * @return string
     */
    public function render(); 

    /**
     * @return mixed
     */
    public function getRawData();       

}