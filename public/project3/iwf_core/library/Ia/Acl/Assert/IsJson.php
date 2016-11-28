<?php
/**
 * This simple assertion class allows us to only allow access to a json view of an action
 */
class Ia_Acl_Assert_IsJson implements Zend_Acl_Assert_Interface
{
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {
        $request = $acl->getRequest();
        $format = (isset($request['format'])) ? $request['format'] : false;
	return ($format=='json');
    }
}
