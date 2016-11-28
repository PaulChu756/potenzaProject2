<?php

namespace Ia\Entity;

class UserTest
    extends \ModelTestCase
{
        
    public function testCanSaveFirstAndLastNameAndRetrieveThem()
    {        
        $u = $this->getTestUser();
        $em = $this->doctrineContainer->getEntityManager();
        $em->persist($u);
        $em->flush();
        
        $users = $em->createQuery('select u from Ia\Entity\User u')->execute();
        $this->assertEquals(1,count($users));
        
        $this->assertEquals('Aaron',$users[0]->firstname);
        $this->assertEquals('Lozier',$users[0]->lastname);
    }
    
    private function getTestUser()
    {
        $u = new User();
        $u->firstname = 'Aaron';
        $u->lastname = 'Lozier';
        return $u;
    }
    
    


}