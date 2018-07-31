<?php
/**
 * Created by PhpStorm.
 * User: Tcho
 * Date: 07/06/2018
 * Time: 10:30
 */
//<include_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');
class TournoiController extends Zend_Controller_Action
{

    public function init()
    {

        $detect = new Mobile_Detect;

        $this->isMobile= $detect->isMobile() && !$detect->isTablet();
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function preDispatch()
    {
        $this->view->headMeta ()->appendHttpEquiv ( 'Content-Type', 'text/html; charset=UTF-8' )->appendHttpEquiv ( 'Content-Language', 'fr-FR' );
        $this->view->headLink ()->appendStylesheet ( '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css' );
        $this->view->headScript ()->appendFile ( '//code.jquery.com/jquery-3.2.1.slim.min.js' )
            ->appendFile ( '//cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js' )
            ->appendFile ( '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js' );

        $this->_helper->layout->setLayout('tournoi'.($this->isMobile?"-mobile":""));

        parent::preDispatch(); // TODO: Change the autogenerated stub
    }

    public function indexAction(){}

}