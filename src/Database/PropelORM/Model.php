<?php

namespace Kerisy\Database\PropelORM;

class Model {

    protected $connection ;   

    public function __construct(){
    
        $serviceContainer   = \Propel\Runtime\Propel::getServiceContainer();   
        
        $serviceContainer->checkVersion('2.0.0-dev');
        
        $serviceContainer->setAdapterClass($this->connection, 'mysql');
        
        $manager            = new \Propel\Runtime\Connection\ConnectionManagerSingle();
        
        $manager->setConfiguration(config('database')->get($this->connection));
        
        $manager->setName($this->connection);
        
        $serviceContainer->setConnectionManager($this->connection, $manager);
        
        $serviceContainer->setDefaultDatasource($this->connection);
    }

}
