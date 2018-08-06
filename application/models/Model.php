<?php
class Default_Model_Model{
		/**
		 *
		 * @var string $_tableName nom de la classe DBtable associ&eacute;e
		 */
		public $_tableName;
	
		/**
		 *
		 * @var array $_colsArray tableau associatif des donn&eacute;es du mod&egrave;le
		 */
		protected $_colsArray;
	
		/**
		 *
		 * @var array $infoCols tableau des colonnes de la table
		 */
		protected $infoCols;
	
		/**
		 * instancie la classe
		 *
		 * r&eacute;cup&egrave;re les informations de la structure de la table et &eacute;ventuellement popule les champs avec les valeurs donn&eacute;es par $options
		 *
		 * @param array $options tableau des valeurs de l'utilisateur avec cl&eacute; lenom des colonnes et valeur la valeur associ&eacute;e
		 */
		public function __construct(array $options=null){
	
	
			$mapper = new Default_Model_Mapper();
			$mapper->_tableName = $this->_tableName;
	
			$info = $mapper->getDbTable()->info();
	
			$this->_colsArray=new stdClass();
	
			$this->infoCols=$info['cols'];
	
			/*foreach($info['cols'] as $col):
	
	
			$this->_colsArray->{$col} = $cols;
	
			endforeach;
			*/
	
			if(null!=$options):
				
			foreach($options as $key=>$value){
					
				$this->__set($key, $value);
					
			}
	
			endif;
	
	
	
		}
		/**
		 * setter
		 *
		 * @param string $name
		 * @param string $value
		 */
		public function __set($name, $value)
		{
			 
			 
			if ('mapper' !== $name&&in_array($name,$this->infoCols)){
				 
				$this->_colsArray->{$name}=$value;
	
	
			}
	
	
		}
		/**
		 * getter
		 * @param string $name
		 * @return mixed $name la valeur de la colonne
		 */
		public function __get($name)
		{
	
			if(!in_array($name,$this->infoCols))return false;
			return $this->_colsArray->{$name};
		}
		/**
		 * transforme l'objet des donn&eacute;es du model en tableau associatif
		 *
		 * @return array
		 */
		public function dataToArray(){
	
			return (array) $this->getData();
	
		}
		/**
		 * retourne les donn&eacute;es du model
		 * @return object:
		 */
		public function getData(){
	
			return $this->_colsArray;
	
		}
		
		public function getFields(){
			
			return $this->infoCols;
			
		}
	
	
	
}