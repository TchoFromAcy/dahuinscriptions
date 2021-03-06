<?php


class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initDoctype()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('HTML5');
	
	}
	protected function _initAuloLoader(){
		
		$resourceLoader3 = new Zend_Loader_Autoloader_Resource(array(
				'basePath'  =>APPLICATION_PATH."/",
				'namespace' => 'Default'
		));
		$resourceLoader3->addResourceTypes(array(
				'controller'=>array('namespace' => 'Controller',
						'path'      => 'controllers'),
				'model' => array(
						'namespace' => 'Model',
						'path'      => 'models'
				),
				'table' => array(
						'namespace' => 'Model_DbTable',
						'path'      => 'models/DbTable'
				),
				'plugin' => array(
						'namespace' => 'Plugin',
						'path'      => 'plugins'
				),
				'view' => array(
						'namespace' => 'View',
						'path'      => 'views'
				)
		));
		
		
	}
	
	
	
	public function _initRoutes()
	{
		 
		$frontController = Zend_Controller_Front::getInstance();
		$config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR.'configs'.DIRECTORY_SEPARATOR.'application.ini', APPLICATION_ENV);
	
		$frontController->getRouter()->addConfig($config, 'routes');

        $restAPIOperation = new Zend_Controller_Router_Route(
            'restAPI/:type/:item_id',
            array(
                'controller' => 'rest',
                'action' => 'doRequest',
                'module' => 'default'
            )
        );

        $frontController->getRouter()->addRoute('RestApiOperation', $restAPIOperation);

        $restAPList = new Zend_Controller_Router_Route(
            'restAPI/:type',
            array(
                'controller' => 'rest',
                'action' => 'doRequest',
                'module' => 'default'
            )
        );
        $frontController->getRouter()->addRoute('restAPList', $restAPList);

        $restAPList = new Zend_Controller_Router_Route(
            'equipe/:id_team',
            array(
                'controller' => 'tournoi',
                'action' => 'showTeam',
                'module' => 'default'
            )
        );
        $frontController->getRouter()->addRoute('showTeam', $restAPList);

        $restAPList = new Zend_Controller_Router_Route(
            'matchsList',
            array(
                'controller' => 'tournoi',
                'action' => 'showMatchs',
                'module' => 'default'
            )
        );
        $frontController->getRouter()->addRoute('showMatchs', $restAPList);


	}
	
	
	protected function _initDb()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR.'configs'.DIRECTORY_SEPARATOR.'application.ini', APPLICATION_ENV);
		Zend_Registry::set('config', $config);
	
	
		try{
			$db = Zend_Db::factory($config->database);
			 
			$db->getConnection();
			$db->setFetchMode(Zend_Db::FETCH_ASSOC);
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			 
		}catch ( Exception $e ) {
			$db=null;
			die($e->getMessage());
	
	
		}
		// on stock notre dbAdapter dans le registre
	
		Zend_Registry::set( 'db', $db );
	
		return $db;
	}
	
	
}

