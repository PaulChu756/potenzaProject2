<?php

class ControllerTestCase
    extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     *
     * @var \Bisna\Application\Container\DoctrineContainer
     */
    protected $doctrineContainer;

    public function setUp()
    {
        global $application;
        $application->bootstrap();
        $this->doctrineContainer = Zend_Registry::get('doctrine');
        $em = $this->doctrineContainer->getEntityManager();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $tool->dropDatabase();
        $allMetadata = $em->getMetadataFactory()->getAllMetadata();
        $tool->createSchema($allMetadata);
        parent::setUp();
    }
    
    public function tearDown()
    {
        $this->doctrineContainer->getConnection()->close();
        $em = $this->doctrineContainer->getEntityManager();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $tool->dropDatabase();        
        parent::tearDown();
    }

}