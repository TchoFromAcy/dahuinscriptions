<?php

/**
 * Classe de base des mappers
*
* instancie automatiquement la table de la base de donn&eacute;es et effectue des op&eacute;rations dessus
*
* @version 2.0.0
* @package Admin
* @subpackage Model/Mapper
* @author Chau.T chau.tranquy@agencetexto.com
*
*
*
*/

class Default_Model_Mapper
{


	/**
	 *
	 * @var Zend_Db_Table_Abstract $_dbTable instance de Zend_Db_table de la table
	 */
	protected $_dbTable;

	/**
	 *
	 * @var string $_tableName nom de la classe DbTable
	 */
	public $_tableName;

	
	/**
	 *
	 * @var Zend_Config $config configuration de l'application
	 */
	protected $config;

	/**
	 *
	 * @var string $tablePrefix prefix de la classe DBTable &agrave; instancier
	 */
	protected $tablePrefix="Default_Model";

	/**
	 * r&eacute;cup&egrave;re les act, l'instance Zend_Auth et l'objet config
	 */

	public function __construct(){
		
		$this->config = Zend_Registry::get('config');

	}

	/**
	 * instancie la table
	 *
	 * @param mixed $dbTable
	 * @throws Exception
	 * @return Admin_Model_Mapper
	 */
	public function setDbTable($dbTable)
	{
		if (is_string($dbTable)) {
			$dbTable = new $dbTable();
		}
		if (!$dbTable instanceof Zend_Db_Table_Abstract) {
			throw new Exception('Invalid table data gateway provided');
		}
		$this->_dbTable = $dbTable;
		return $this;
	}


	/**
	 * r&eacute;cup&egrave;re la table active ou la cr&eacute;e
	 * @return Zend_Db_Table_Abstract
	 */
	public function getDbTable()
	{
		if (null === $this->_dbTable) {
			
			$this->setDbTable($this->getTableName());
		}
		return $this->_dbTable;
	}

	public function getTableName(){
		
		
		return $this->tablePrefix.'_DbTable_'.ucfirst($this->_tableName);
		
	}
	
	
	/**
	 * r&eacute;cup&egrave;re tous les enregistrements de la table
	 * @param string $where condition de la requ&ecirc;te
	 * @return array $result liste des enregistrements
	 */
	public function fetchAll($where=""){

		$select=$this->getDbTable()->select();//

		if(!empty($where))$select->where($where);
		$result= $this->getDbTable()->getAdapter()->fetchAll($select);
		return $result;

	}

	/**
	 * retrouve en enregistrement &agrave; partir de sa cl&eacute;
	 * @param int $id id de l'enregistrement
	 * @return boolean|Admin_Model_Model retourne faux si aucun enregistrement trouv&eacute; ou le model associ&eacute;
	 */
	public function find($id):Admin_Model_Model{


		$result=$this->getDbTable()->find($id);

		//echo "id=>$id";

		if(0 == count($result)) return false;

		$row = $result->current();
		$object = $this->getModel($row->toArray ());

		return $object;

	}

	public function getModel(array $row=array()):Default_Model_Model{

		$model_className = $this->tablePrefix .'_'. ucfirst($this->_tableName);
		
		
		$object = new $model_className ($row);

		return $object;


	}

	/**
	 * ex&eacute;cute une requ&ecirc;te sur la base de donn&eacute;es
	 *
	 * @param string $sql requ&ecirc;te sql
	 * @param array $args tableau des arguments pour le traitement PDO
	 * @param number $return type de retour ==> 0 (tous les enregistrements), 1 (uniquement le premier), -1 -ou autre- ne retourne rien
	 * @param number $type type du retour (Constante PDO)
	 * @return boolean
	 */
	public function execQuery($sql, array $args=array(), $return=0, $type=PDO::FETCH_OBJ){

		$stmt=$this->getDbTable()->getAdapter()->query($sql, $args);


		$result=true;

		if($return==0)$result=$stmt->fetchAll($type);
		elseif($return==1) $result=$stmt->fetch($type);
		unset($stmt);

		return $result;

	}

	/**
	 * cr&eacute;e ou modifie un enregistrement dans la table
	 *
	 * @param array $data tableau cl&eacute;/valeur de l'enregistrement avec cl&eacute; le nom de la colonne et valeur sa valeur. si cl&eacute; primaire de la table est inclus dans les cl&eacute; de $data (et que l'enregistrement associ&eacute; existe) l'enregistrement sera mis &agrave; jour. Sinon, une nouvell entr&eacute;e sera cr&eacute;&eacute;e.
	 * @return int $id id de l'enregistrement
	 */
	public function addData(array $data){
		setlocale(LC_TIME, "fr_FR");

		$info=$this->getDbTable()->info();


		if(empty($data["creation_date"])&&in_array('creation_date', $info['cols']))$data["creation_date"]=strftime('%Y-%m-%d %H:%M',time());
		if(in_array('update_date', $info['cols']))$data["update_date"]=strftime('%Y-%m-%d %H:%M',time());
		
		$primary = $this->_dbTable->__get('_primary');
		if(array_key_exists($primary, $data)&&$data[$primary]>0):
		$id=$data[$primary];
		unset($data[$primary]);
		$this->getDbTable()->update($data, $this->getDbTable()->getAdapter()->quoteInto($primary.'=?', $id));
		else :


		$data[$primary]=null;
		$id = $this->getDbTable()->insert($data);

		endif;
		return $id;
	}

	

	/**
	 * getter
	 *
	 * @param string $name
	 */
	public function __get($name)
	{

		return $this->{$name};
	}

	/**
	 * supprime un enregistrement
	 *
	 * @param number $id id de l'enregistrement
	 */
	public function delete($id){

		$where = $this->getDbTable()->getAdapter()->quoteInto($this->_dbTable->__get('_primary').'=?', $id);
		return $this->getDbTable()->delete($where);

	}



}

