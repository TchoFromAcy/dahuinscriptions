<?php
/**
 * Classe de base des Table
 * @author Chau.T
 * @package Admin
 * @subpackage Model/DbTable
 * @version 1.0
 *
 */

class Default_Model_DbTable_Table extends Zend_Db_Table_Abstract{

	/**
	 * 
	 * @var string $_name nom de la table
	 */
 	protected $_name;
 	/**
 	 * 
 	 * @var string $_primary nom de la cle primaire
 	 */
    protected $_primary;
    /**
     * 
     * @var boolean $use_prefix true= ajouter DBPREFIX au nom de la table
     */
    protected $use_prefix=true;

    /**
     * 
     */
  public function __construct(){
    
    $this->_name=$this->_name;
    $this->_name=($this->use_prefix?DBPREFIX:'').$this->_name;
     parent::__construct();
     
     if(!empty($this->_primary)) return;
     
     $infos=$this->info();
    $this->_primary=$infos['primary'];
       if(is_array($this->_primary))$this->_primary=$this->_primary[1];
     
     
    
    }
    
    /**
     * getter 
     * @param string $name nom de la propriete a recuperer
     */
    
public function __get($name)
    {
        if($this->{$name})return is_array($this->{$name})?$this->{$name}[1]:$this->{$name};
    }
	

}