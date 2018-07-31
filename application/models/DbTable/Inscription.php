<?php
/**
 * Classe de base des Table
 * @author Chau.T
 * @package Admin
 * @subpackage Model/DbTable
 * @version 1.0
 *
 */

class Default_Model_DbTable_Inscription extends Default_Model_DbTable_Table{

	/**
	 * 
	 * @var string $_name nom de la table
	 */
 	protected $_name="licence";
 	
 	/**
 	 * 
 	 * @var string $_primary nom de la cle primaire
 	 */
    protected $_primary;
    /**
     * 
     * @var boolean $use_prefix true= ajouter DBPREFIX au nom de la table
     */
    protected $use_prefix=false;

    /**
     * 
     */
	

}