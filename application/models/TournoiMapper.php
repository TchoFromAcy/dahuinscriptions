<?php

class Default_Model_TournoiMapper extends Default_Model_Mapper {
	public $_tableName="Tournoi";
	
	public function getInscriptionFromHash($hash){
		
		
		$query = "select id_licence from ".$this->getDbTable()->__get('_name')." where md5(id_licence) = ?";
		
		$res=$this->execQuery($query,array($hash), 1);
		
		$id=$res->id_licence;
		
		return $this->find($id);
		
		
		
	}
	
	
	public function checkEmail($email){
		
		$sql="select ".$this->getDbTable()->__get('_primary')." from ".$this->getDbTable()->__get('_name')." where email=?";
		
		$res = $this->execQuery($sql, array($email),1);
		
		$id=$res->{$this->getDbTable()->__get('_primary')};
		return (int)$id;
		
		
	}
	
	
}

?>