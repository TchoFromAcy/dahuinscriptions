<?php

ini_set('display_errors',1);

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

if(preg_match('#localhost#', $_SERVER['HTTP_HOST']))define('APPLICATION_ENV','development');
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
  
     (APPLICATION_PATH . '/../library'),
    get_include_path(),
)));


/** Zend_Application */
try {
    require_once '../vendor/autoload.php';
    require_once 'Zend/Application.php';


// Create application, bootstrap, and run
    $application = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'application.ini'
    );

    $application->bootstrap()
        ->run();
}catch(Exception $e){

    die("ereur");
    xdebug_var_dump($e);


}