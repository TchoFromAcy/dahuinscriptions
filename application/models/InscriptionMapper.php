<?php

class Default_Model_InscriptionMapper extends Default_Model_Mapper {
	public $_tableName="Inscription";
	
	public function getInscriptionFromHash($hash){
		
		
		$query = "select id_licence from ".$this->getDbTable()->__get('_name')." where md5(id_licence) = ?";
		
		$res=$this->execQuery($query,array($hash), 1);
		
		$id=$res->id_licence;
		
		return $this->find($id);
		
		
		
	}
	
	
	public function checkEmail($email, $season){
		
		$sql="select id_licence from ".$this->getDbTable()->__get('_name')." where email=? and saison=?";
		
		$res = $this->execQuery($sql, array($email, $season),1);
		
		$id=$res->id_licence;
		return (int)$id;
		
		
	}
	
	
}

?>