<?php

/**
 * Classe d'envoi des mails.
 * 
 * Les mails sont generes d'apres un layout commun
 * 
 * @package Admin
 * @subpackage Model
 * @author Chau.T
 * @version 2.0
 */
class Default_Model_SendMail extends Zend_Mail
{

    /**
     *
     * @var Zend_Config $_config configuration de l'application
     */
    protected $_config;
    protected $logo;

    /**
     * initialisation du transport Zend_Mail
     */
    public function __construct()
    {
        $this->_config = Zend_Registry::get("config");
        
        $this->logo="http://{$_SERVER['HTTP_HOST']}/medias/logo-mail.png";
        
        //logo-mail-tournoi.png
        
        $mt = new Zend_Mail_Transport_Sendmail();
        Zend_Mail::setDefaultTransport($mt);
    }

    /**
     * generation du layout html et envoi du mail
     * 
     * @param string $to
     *            liste des destinataires separes par un ";"
     * @param string $message
     *            message
     * @param string $subject
     *            sujet du mail
     */
    public function sendMail($to, $message, $subject,$from=null, $tpl="mail")
    {
        $layout = new Zend_Layout();
        $layout->setLayoutPath(APPLICATION_PATH . "/layouts/scripts");
        $layout->setLayout($tpl);
        
        $layout->content = $message;
       
        $layout->assign("logo",$this->logo);
        
        $html = $layout->render();

        $mail = new Zend_Mail();


        
        try {
            if($from==null||!is_array($from))$mail->setFrom($this->_config->mail->from->address, $this->_config->mail->from->name);
            else $mail->setFrom($from["address"], $from["name"]);
            $mail->addTo($to);
            $mail->setSubject($subject);
            $mail->setBodyHTML($html);


            if(!preg_match('#localhost#', $_SERVER['HTTP_HOST'])):

            $mail->send();
            endif;
            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();exit;
            return false;
        }
    }
    
    
    public function setLogo($logo){
    	
    	$this->logo=$logo;
    	
    }
    
}