<?php

define('PRIX_JOUEUR', 55);
define('PRIX_NONJOUEUR', 40);
define('PRIX_ENFANT', 25);


use Default_Model\InscriptionMapper;
require_once("Mobile_Detect/Mobile_Detect.php");

class AdminController extends Zend_Controller_Action {
	protected $prix = [ 
			2 => [ 
					"prix" => 50.5,
					"label" => "Comp&eacute;tition" 
			],
			1 => [ 
					"prix" => 25.5,
					"label" => "Loisir" 
			],
			"club" => 35,
			'etudiant' => 20 
	];
	protected $_arrayPoste=["1"=>"Handler","Middle","Deep"];
	
	protected $user_path;
	public function init() {
		$this->user_path = APPLICATION_PATH . "/users_uploads/uploads/";
		$contextSwitch = $this->_helper->getHelper ( 'contextSwitch' );
		$contextSwitch->addActionContext ( 'ajax', 'json' )->initContext ();
		
		$detect = new Mobile_Detect;
		$this->isDev=false;//$_SERVER['REMOTE_ADDR']!=="90.27.36.171";
		$this->view->maintenance=false;
		
		// Exclude tablets.
		$this->isMobile= $detect->isMobile() && !$detect->isTablet();
		
	}
	public function preDispatch() {
        if($_COOKIE['admin']!==sha1("dadahu".strftime('%Y%m%d', time()))&&$this->_request->getActionName()!=="Login")return $this->_forward("Login");

		$this->context=!preg_match('/inscription/',$_SERVER['HTTP_HOST'])?'tournoi':'inscription';
		if($this->context=="tournoi"&&$this->_request->getActionName()!=="tournoi"&&$this->_request->getActionName()!=="ajax"&&$this->_request->getActionName()!=="updater"&&$this->_request->getActionName()!=="Admin"&&$this->_request->getActionName()!=="getPhoto"&&$this->_request->getActionName()!=="Login"):
				
	
		
		endif;

		
		$this->view->headMeta ()->appendHttpEquiv ( 'Content-Type', 'text/html; charset=UTF-8' )->appendHttpEquiv ( 'Content-Language', 'fr-FR' );
		if($this->context!=="tournoi" || $this->_request->getActionName()=="b2c")$this->view->headLink ()->appendStylesheet ( '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css' );
		$this->view->headScript ()->appendFile ( '//code.jquery.com/jquery-2.1.1.min.js' )->appendFile ( '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js' );
		$date = new Zend_Date ();
		$currentMonth = ( int ) $date->get ( Zend_Date::MONTH );
		$currentYear = ( int ) $date->get ( Zend_Date::YEAR );
		if (in_array ( $currentMonth, [ 
				8,
				9,
				10,
				11,
				12 
		] )) :
			$currentSeason = $currentYear . '/' . ($currentYear + 1);
		 else :
			$currentSeason = ($currentYear - 1) . '/' . ($currentYear);
		endif;
		$this->currentSeason = $this->view->currentSeason = $currentSeason;
	}
	public function ajaxAction() {
		$call = $this->getRequest ()->apiCall;
		
		$r = new ReflectionClass ( $this );
		
		$isJsonP = ! empty ( $this->_request->callback );
		
		if (! $r->hasMethod ( $call )) {
			if (! $isJsonP) :
				$this->view->error = "M&eacute;thode inconnue";
				$this->view->success = false;
			 else :
				
				$result = array (
						"success" => false,
						"error" => "M&eacute;thode inconnue" 
				);
				
				header ( 'Content-Type: text/javascript' );
				echo $this->_request->callback . '(' . json_encode ( $result ) . ');';
				die ();
			

			endif;
			
			return;
		}
		
		$result = $this->{$call} ();
		
		if ($result && ! $isJsonP) :
			foreach ( $result as $key => $value ) :
				
				$this->view->{$key} = $value;
			endforeach
			;
		 

		else :
			
			header ( 'Content-Type: text/javascript' );
			echo $this->_request->callback . '(' . json_encode ( $result ) . ');';
			die ();
		endif;
	}
	
	
	
	
	public function getTournoiInscriptions(){

	    die('ok');

		$mapper=new Default_Model_TournoiMapper();
		
		$pageNum = $this->getRequest ()->page_num;
		$perpage = $this->getRequest ()->rows_per_page;
		$sorting = $this->getRequest ()->sorting;
		
		
		$sql = "select count(".$mapper->getDbTable ()->__get ( '_primary' ).") as num from " . $mapper->getDbTable ()->__get ( '_name' ) . " where update_date like '%".strftime('%Y')."%'";
		echo $sql;
		die();
		$count = $mapper->execQuery ( $sql, [], 1 );
		
		$sort = [ ];
		
		foreach ( $sorting as $sortItem ) :
			
		if ($sortItem [order] == "ascending" || $sortItem [order] == "descending") :
		
		array_push ( $sort, $sortItem ['field'] . " " . ($sortItem [order] == "descending" ? "DESC" : "ASC") );
			
		 
		endif;
		endforeach
		;
		
	
		
		$sql = "select * from " . $mapper->getDbTable ()->__get ( '_name' )." where update_date like '%".strftime('%Y')."%'"; //
		
		if (count ( $sort ) > 0)
			$sql .= " order by " . implode ( ',', $sort );
		$sql .= " limit " . (($pageNum - 1) * $perpage) . "," . ($perpage) . "";
		
	
		$rows = $mapper->execQuery ( $sql, array (), 0 );
		
		unset ( $this->view->currentSeason );
		
		return array (
				"total_rows" => $count->num,
				"page_data" => $rows,
				'page' => $pageNum,
            "sql"=>$sql
		);
		
		return $result;
		
	}
	
	public function exportLicences() {
		$licences = $this->_request->inscr;
		
		$mapper = new Default_Model_InscriptionMapper ();
		$headers = [ 
				'Nom',
				'Prénom',
				'Email',
				'Sexe',
				'Tél.',
				'Adresse',
				'CP',
				'Ville',
				'Date de Naissance',
				'Lieu de Naissance',
				'Nationalité',
				'Profession',
				'Etudiant',
				'IBAN',
				'Type de licence' 
		];
		
		$file = tempnam ( "tmp", "zip" );
		
		 $zip = new ZipArchive(); 
		 $zip->open($file, ZipArchive::OVERWRITE);
		
		
		$fp = fopen ( APPLICATION_PATH . '/export/export.csv', 'w+' );
		
		fputcsv ( $fp, $headers, ";" );
		
		foreach ( $licences as $licence ) :
			
			$licenceData = $mapper->find ( $licence );
		
		$licence=$this->user_path.'/'.$licenceData->__get('certif');
		$photo=$this->user_path.'/'.$licenceData->__get('photo');
		$zip->addFile($licence, $licenceData->__get('certif'));
		$zip->addFile($photo, $licenceData->__get('photo'));
			
			$joueur = [ 
					$licenceData->__get ( 'nom' ),
					$licenceData->__get ( 'prenom' ),
					$licenceData->__get ( 'email' ),
					( int ) $licenceData->__get ( 'genre' ) == 1 ? 'M' : 'F',
					$licenceData->__get ( 'tel' ),
					$licenceData->__get ( 'adresse' ),
					$licenceData->__get('cp'),
					$licenceData->__get('ville'),
					strftime('%d/%m/%Y',strtotime($licenceData->__get('dob'))),
					$licenceData->__get('lieudob'),
					$licenceData->__get('nationalite'),
					$licenceData->__get('profession'),
					(int)$licenceData->__get('etudiant')==1?'X':'',
					$licenceData->__get('iban'),
					(int)$licenceData->__get('licence')==1?'Loisir':'Comp�tition'
					
			];
			
			$joueur=array_map('utf8_decode',$joueur);
			 fputcsv($fp, $joueur,";");
		endforeach
		;
		
		
		fclose($fp);
		
		$zip->addFile(APPLICATION_PATH . '/export/export.csv','liste.csv');
		
		$zip->close();
		
		header('Content-Type: application/zip');
		header('Content-Length: ' . filesize($file));
		header('Content-Disposition: attachment; filename="export_licence_dahuts.zip"');
		readfile($file);
		unlink($file);
		unlink(APPLICATION_PATH . '/export/export.csv');
		
	}
	public function indexAction() {
		$this->view->headScript ()->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/jquery.validate.min.js' )->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/additional-methods.min.js' )->appendFile ( '/_js/lib/jquery.validate.popover.js' )->appendFile ( '/_js/lib/jquery.validation/messages_fr.js' )->appendFile ( '/_js/lib/dropzone/dropzone.js' );
		$this->view->headLink ()->appendStylesheet ( '/_js/pnotify.custom.min.css' );
		$this->view->headScript ()->appendFile ( '/_js/pnotify.custom.min.js' );
				
		if($_COOKIE['admin']!==sha1("dadahu".strftime('%Y%m%d', time())))return $this->_forward("Login");
		
		if((int)$this->_request->logout==1):

		setcookie("dadahu",null,time()-24000);
		
		$this->_redirect("/login");
		
		endif;
		
		
		$this->view->headLink ()->prependStylesheet ( '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css' );
		
		$this->view->headLink ()->appendStylesheet ( 'https://code.jquery.com/ui/1.11.4/themes/black-tie/jquery-ui.css' )->appendStylesheet ( '/_js/lib/jquery.bs_grid/jquery.bs_grid.css' )->appendStylesheet ( '/_js/lib/jquery.bs_pagination/jquery.bs_pagination.min.css' )->appendStylesheet ( '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
		
		$this->view->headScript ()->appendFile ( '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js' )->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/jquery.validate.min.js' )->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/additional-methods.min.js' )->appendFile ( '/_js/lib/jquery.validate.popover.js' )->appendFile ( '/_js/lib/jquery.validation/messages_fr.js' )->appendFile ( 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js' )->appendFile ( '/_js/lib/jquery.bs_pagination/jquery.bs_pagination.min.js' )->appendFile ( '/_js/lib/jquery.bs_grid/jquery.bs_grid.js' )->appendFile ( '/_js/lib/jquery.bs_pagination/localization/fr.js' )->appendFile ( '/_js/lib/jquery.bs_grid/localization/fr.js' );
		
		
		$this->view->isConnected=true;
		
		

		if($this->context=="tournoi"):
		$this->render('tournoi-admin');
		
		else:
		
		$mapper = new Default_Model_InscriptionMapper ();
		
		$seasonSql = "select distinct(saison) from " . $mapper->getDbTable ()->__get ( '_name' ) . " order by saison asc";
		$saisonReq = $mapper->execQuery ( $seasonSql, array () );
		
		if (count ( $saisonReq ) > 0) :
			
			$this->view->saisonList = [ ];
			
			foreach ( $saisonReq as $saison ) :
				
				if ($saison->saison !== $this->currentSeason)
					array_push ( $this->view->saisonList, $saison->saison );
			endforeach
			;
			
			array_push ( $this->view->saisonList, $this->currentSeason );
		
    	
    	endif;
		
		sort ( $this->view->saisonList );
		
		endif;
	}
	public function checkMail() {
		$email = $this->getRequest ()->email;
		$hash = $this->getRequest ()->hash;
		
		$mapper = new Default_Model_InscriptionMapper ();
		
		$id = $mapper->checkEmail ( $email, $this->currentSeason );
		
		if ($id == 0) :
			echo "true";
		 

		else :
			
			if (empty ( $hash ))
				echo "false";
			else {
				
				echo $hash == md5 ( $id ) ? "true" : "false";
			}
			;
		

		endif;
		die ();
	}
	
	public function loginAction(){
		
		$post=(object)$this->_request->getPost();
		
				
		if(!empty($post->mdp)):
		
		if($post->mdp=="dadahu"):
			
		setcookie("admin",sha1("dadahu".strftime('%Y%m%d', time())));
		$this->_redirect("/admin");
		
		else :
		
		$this->view->error="Mot de passe incorrect";
		
		endif;
		
		endif;
		

		
		if($this->context=="tournoi")$this->view->headLink ()->prependStylesheet ( '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css' );
		
		$this->view->headLink ()->appendStylesheet ( 'https://code.jquery.com/ui/1.11.4/themes/black-tie/jquery-ui.css' )->appendStylesheet ( '/_js/lib/jquery.bs_grid/jquery.bs_grid.css' )->appendStylesheet ( '/_js/lib/jquery.bs_pagination/jquery.bs_pagination.min.css' )->appendStylesheet ( '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
		
		$this->view->headScript ()->appendFile ( '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js' )->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/jquery.validate.min.js' )->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/additional-methods.min.js' )->appendFile ( '/_js/lib/jquery.validate.popover.js' )->appendFile ( '/_js/lib/jquery.validation/messages_fr.js' )->appendFile ( 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js' )->appendFile ( '/_js/lib/jquery.bs_pagination/jquery.bs_pagination.min.js' )->appendFile ( '/_js/lib/jquery.bs_grid/jquery.bs_grid.js' )->appendFile ( '/_js/lib/jquery.bs_pagination/localization/fr.js' )->appendFile ( '/_js/lib/jquery.bs_grid/localization/fr.js' );
		
		$this->render("login");
		
	}
	
	public function adminAction() {
		
		
		//echo "dadahu".strftime('%Y%m%d', time());
		

				
		if($_COOKIE['admin']!==sha1("dadahu".strftime('%Y%m%d', time())))return $this->_forward("Login");
		
		if((int)$this->_request->logout==1):

		setcookie("dadahu",null,time()-24000);
		
		$this->_redirect("/login");
		
		endif;
		
		
		if($this->context=="tournoi")$this->view->headLink ()->prependStylesheet ( '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css' );
		
		$this->view->headLink ()->appendStylesheet ( 'https://code.jquery.com/ui/1.11.4/themes/black-tie/jquery-ui.css' )->appendStylesheet ( '/_js/lib/jquery.bs_grid/jquery.bs_grid.css' )->appendStylesheet ( '/_js/lib/jquery.bs_pagination/jquery.bs_pagination.min.css' )->appendStylesheet ( '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
		
		$this->view->headScript ()->appendFile ( '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js' )->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/jquery.validate.min.js' )->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/additional-methods.min.js' )->appendFile ( '/_js/lib/jquery.validate.popover.js' )->appendFile ( '/_js/lib/jquery.validation/messages_fr.js' )->appendFile ( 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js' )->appendFile ( '/_js/lib/jquery.bs_pagination/jquery.bs_pagination.min.js' )->appendFile ( '/_js/lib/jquery.bs_grid/jquery.bs_grid.js' )->appendFile ( '/_js/lib/jquery.bs_pagination/localization/fr.js' )->appendFile ( '/_js/lib/jquery.bs_grid/localization/fr.js' );
		
		
		$this->view->isConnected=true;
		
		var_dump($this->context);

		if($this->context=="tournoi"):
		$this->render('tournoi-admin');
		
		else:
		
		$mapper = new Default_Model_InscriptionMapper ();
		
		$seasonSql = "select distinct(saison) from " . $mapper->getDbTable ()->__get ( '_name' ) . " order by saison asc";
		$saisonReq = $mapper->execQuery ( $seasonSql, array () );
		
		if (count ( $saisonReq ) > 0) :
			
			$this->view->saisonList = [ ];
			
			foreach ( $saisonReq as $saison ) :
				
				if ($saison->saison !== $this->currentSeason)
					array_push ( $this->view->saisonList, $saison->saison );
			endforeach
			;
			
			array_push ( $this->view->saisonList, $this->currentSeason );
		
    	
    	endif;
		
		sort ( $this->view->saisonList );
		
		endif;
	}
	public function processInscription() {
		$post = $this->_request->getPost ();
		
		if ($post ['authKey']) :
			
			$inscription = new Default_Model_InscriptionMapper ();
			
			$inscriptionModel = $inscription->getInscriptionFromHash ( $post ['authKey'] );
			
			if ($inscriptionModel && $inscriptionModel->__get ( "saison" ) == $this->currentSeason) :
				$post ['id_licence'] = $inscriptionModel->__get ( 'id_licence' );
			
		
		endif;
			unset ( $post ['authKey'] );
		
    	endif;
		unset ( $post ['authKey'] );
		unset ( $post ['apiCall'] );
		
		if (empty ( $post ['email'] ) || empty ( $post ['nom'] ) || empty ( $post ['prenom'] ) || empty ( $post ['genre'] ) || empty ( $post ['adresse'] ) || empty ( $post ['cp'] ) || empty ( $post ['ville'] ) || empty ( $post ['dob'] ) || empty ( $post ['licence'] ) || empty ( $post ['iban'] ) || empty ( $post ['captcha'] ['input'] ))
			return array (
					"success" => false,
					"error" => "Veuillez remplir tous les champs obligatoires" 
			);
			
			// validation
		
		$id = $post ['captcha'] ['id'];
		$value = $post ['captcha'] ['input'];
		
		unset ( $post ['captcha'] );
		// unset($post['captcha']['input']);
		
		$captchaSession = new Zend_Session_Namespace ( 'Zend_Form_Captcha_' . $id );
		$captchaIterator = $captchaSession->getIterator ();
		$captchaWord = $captchaIterator ['word'];
		
		if ($captchaWord !== $value)
			return array (
					"success" => false,
					"error" => "Le code est incorrect" 
			);
		
		$validator = new Zend_Validate_EmailAddress ();
		if (! $validator->isValid ( $post ['email'] ))
			return array (
					"success" => false,
					"error" => "Ton email est incorrect" 
			);
		
		$validator = new Zend_Validate_Date ( array (
				'format' => 'dd/mm/yyyy' 
		) );
		if (! $validator->isValid ( $post ['dob'] ))
			return array (
					"success" => false,
					"error" => "Ta date de naissance est incorrecte" 
			);
		
		$date = new Zend_Date ( $post ['dob'], 'dd/MM/yyyy' );
		$post ['dob'] = $date->get ( 'yyyy-MM-dd' );
		$post ['saison'] = $this->currentSeason;
		
		$mapper = new Default_Model_InscriptionMapper ();
		
		$id = $mapper->addData ( $post );
		
		return array (
				"success" => true,
				"result" => md5 ( $id ) 
		);
	}
	public function copyPhoto() {
		$hash = $this->getRequest ()->hash;
		$authKey = $this->getRequest ()->oldKey;
		
		$inscription = new Default_Model_InscriptionMapper ();
		
		$newInscriptionModel = $inscription->getInscriptionFromHash ( $hash );
		$oldInscriptionModel = $inscription->getInscriptionFromHash ( $authKey );
		
		$path = $this->user_path . $oldInscriptionModel->__get ( 'photo' );
		
		$extension = pathinfo ( $path, PATHINFO_EXTENSION );
		
		include_once 'My/Action/Helpers/Utils.php';
		
		$utils = new My_Action_Helpers_Utils ();
		
		$fname = $utils->stripAccents ( $newInscriptionModel->__get ( 'prenom' ) . '_' . $newInscriptionModel->__get ( 'nom' ) ) . '_photo_' . $utils->stripAccents ( $this->currentSeason ) . "." . $extension;
		
		copy ( $path, $this->user_path . $fname );
		
		$newInscriptionModel->__set ( 'photo', $fname );
		
		$inscription->addData ( $newInscriptionModel->dataToArray () );
		return array (
				"success" => true 
		);
	}
	public function finalize() {
		$hash = $this->getRequest ()->hash;
		$update = ( int ) $this->getRequest ()->update;
		
		$inscription = new Default_Model_InscriptionMapper ();
		
		$inscriptionModel = $inscription->getInscriptionFromHash ( $hash );
		if ($inscriptionModel && $inscriptionModel->__get ( "saison" ) !== $this->currentSeason) :
			
			$sql = "select id_licence from " . $inscription->getDbTable ()->__get ( '_name' ) . " where email=? and saison=?";
			$res = $inscription->execQuery ( $sql, array (
					$inscriptionModel->__get ( 'email' ),
					$this->currentSeason 
			), 1 );
			
			$inscriptionModel = $inscription->find ( $res->id_licence );
		
    	
    	endif;
		
		if (! $inscriptionModel)
			return false;
		
		$mail = new Default_Model_SendMail ();
		
		$prix = $this->prix ['club'] + $this->prix [$inscriptionModel->__get ( 'licence' )] ['prix'];
		if ($inscriptionModel->__get ( 'etudiant' ) == 1)
			$prix -= $this->prix ['etudiant'];
		
		$style = 'font-family:Arial, Verdana; position: relative; display: block; font-size:12px; color:#000000; ';
		
		$prenom = utf8_decode ( $inscriptionModel->__get ( 'prenom' ) );
		
		$content = "<p style=\"$style\">Bonjour $prenom,<br />Ton inscription pour la saison {$this->currentSeason} a bien &eacute;t&eacute; " . ($update == 1 ? "modifi&eacute;e" : "prise en compte") . ".<br />Pour finaliser ton inscription, il nous manque :</p><ul style=\"$style\">";
		
		if (! ($inscriptionModel->__get ( 'photo' )))
			$content .= "<li>Une photo d'identit&eacute;";
		if (! ($inscriptionModel->__get ( 'certif' )))
			$content .= "<li>Ton certificat m&eacute;dical (<a href=\"http://{$_SERVER['HTTP_HOST']}/medias/modele_certif.pdf\">Tu peux trouver un mod&egrave;le de certificat ici</a>)</li>";
		
		$content .= "<li>Le montant de ta cotisation pour la licence " . ($inscriptionModel->__get ( 'licence' ) == 2 ? " comp&eacute;tition" : 'loisir') . " soit " . $prix . " &euro; par ch&egrave;que (&agrave; l'ordre de l'association Dahultimate) ou par virement (<a href=\"{$_SERVER['HTTP_HOST']}/medias/rib.pdf\">Le rib du club est ici</a>)</li>";
		
		if ($inscriptionModel->__get ( 'etudiant' ) == 1)
			$content .= "<li> Une copie de ta carte d'&eacute;tudiant</li>";
		
		$content .= '</ul>';
		
		$content .= "<p style=\"$style\">Tu peux modifier tes informations tant que ton inscription n'a pas &eacute;t&eacute; valid&eacute;e par le bureau en utilisant ce lien <a href=\"http://{$_SERVER['HTTP_HOST']}?authKey=$hash\">http://{$_SERVER['HTTP_HOST']}?authKey=$hash</a></p>";
		$content .= "<p style=\"$style\">A tr&egrave;s vite, le bureau des Dahuts</p>";
		
		$mail->sendMail ( $inscriptionModel->__get ( 'email' ), $content, ($update == 1 ? "Modification de ton" : "Ton") . " inscription chez les Dahultimates pour la saison " . $this->currentSeason );
		
		$mail->sendMail ( "contact@dahultimate.com;chau@plusdeclic.com;", $content, ($update == 1 ? "Modification de l'i" : "I") . "nscription chez les Dahultimates pour la saison " . $this->currentSeason . " de $prenom " . utf8_decode ( $inscriptionModel->__get ( 'nom' ) ) );
		
		return array (
				"success" => true,
				"prix" => $prix 
		);
	}
	public function uploadFile() {
		$fileName = $this->getRequest ()->fileName;
		$saison = $this->currentSeason;
		$hash = $this->getRequest ()->hash;
		
		$inscription = new Default_Model_InscriptionMapper ();
		
		$inscriptionModel = $inscription->getInscriptionFromHash ( $hash );
		
		include_once 'My/Action/Helpers/Utils.php';
		
		$utils = new My_Action_Helpers_Utils ();
		
		$fname = $utils->stripAccents ( $inscriptionModel->__get ( 'prenom' ) . '_' . $inscriptionModel->__get ( 'nom' ) ) . '_' . $fileName . '_' . $utils->stripAccents ( $saison );
		
		$adapter = new Zend_File_Transfer_Adapter_Http ();
		$user_path = $this->user_path;
		
		$adapter->setDestination ( $user_path );
		$adapter->addValidator ( 'Extension', false, 'png,jpg,jpeg,pdf' );
		$adapter->addValidator ( 'FilesSize', false, array (
				'min' => '10kB',
				'max' => '4MB' 
		) );
		$files = $adapter->getFileInfo ();
		$msg = null;
		$error = null;
		
		foreach ( $files as $file => $info ) :
			$name = $adapter->getFileName ( $file );
			// $fileclass->info = $info;
			
			if (! $adapter->isValid ( $file )) :
				$error = - 1;
				$errors = $adapter->getErrors ();
				
				switch ($errors [0]) :
					
					case "fileExtensionFalse" :
						
						$msg = "L'image doit &ecirc;tre au format jpg ou png";
						
						break;
					
					case "fileUploadErrorIniSize" :
					
					case 'fileFilesSizeTooBig' :
						$msg = "L'image doit faire moins de 4Mo";
						
						break;
					
					default :
						
						$msg = "Une erreur est intervenue lors du chargement de ton image";
						
						break;
				endswitch
				;
			 

			elseif (! $adapter->isUploaded ( $file )) :
				$msg = "Une erreur est intervenue lors du chargement de ton image !";
				$error = - 1;
			

			endif;
			
			$extension = pathinfo ( $info ['name'], PATHINFO_EXTENSION );
			
			$fname .= '.' . strtolower ( $extension );
			$adapter->addFilter ( 'rename', $fname );
			if (! $adapter->receive ( $file )) :
				$msg = "Une erreur est intervenue lors du chargement de ton fichier !";
				$success = false;
			 else :
				$success = true;
				$inscriptionModel->__set ( $fileName, $fname );
				$inscription->addData ( $inscriptionModel->dataToArray () );
			

			endif;
		endforeach
		;
		
		return array (
				"success" => $success,
				"msg" => $msg,
				"name" => $name,
				"fname" => $fname,
				"files" => $files,
				"user_path" => $user_path 
		);
	}
	public function getInscriptions() {
		$mapper = new Default_Model_InscriptionMapper ();
		
		$pageNum = $this->getRequest ()->page_num;
		$perpage = $this->getRequest ()->rows_per_page;
		$sorting = $this->getRequest ()->sorting;
		$season = $this->getRequest ()->currentSaison;
		
		$sql = "select count(id_licence) as num from " . $mapper->getDbTable ()->__get ( '_name' ) . " where saison =?";
		
		$count = $mapper->execQuery ( $sql, array (
				$season 
		), 1 );
		
		$sort = [ ];
		
		foreach ( $sorting as $sortItem ) :
			
			if ($sortItem [order] == "ascending" || $sortItem [order] == "descending") :
				
				array_push ( $sort, $sortItem ['field'] . " " . ($sortItem [order] == "descending" ? "DESC" : "ASC") );
			
    	
    	endif;
		endforeach
		;
		
		$sql = "select * from " . $mapper->getDbTable ()->__get ( '_name' ) . " where saison ='" . $season . "'"; //
		
		if (count ( $sort ) > 0)
			$sql .= " order by " . implode ( ',', $sort );
		$sql .= " limit " . (($pageNum - 1) * $perpage) . "," . ($perpage) . "";
		
		$rows = $mapper->execQuery ( $sql, array (), 0 );
		
		unset ( $this->view->currentSeason );
		
		return array (
				"total_rows" => $count->num,
				"page_data" => $rows,
				'page' => $pageNum 
		);
		
		return $result;
	}
	function getphotoAction() {
		include_once (("Image/Image.php"));
		include_once ("My/Action/Helpers/Utils.php");
		
		$this->_helper->layout ()->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ( true );
		
		$saison = $this->getRequest ()->saison;
		$id_licence = $this->getRequest ()->id_licence;
		$typeImg = $this->getRequest ()->type;
		$mapper = new Default_Model_InscriptionMapper ();
		$user = $mapper->find ( $id_licence );
		
		$utils = new My_Action_Helpers_Utils ();
		$photo = $user->__get ( 'photo' );
		
		$user_path = $this->user_path;
		
		if (! $photo || ! file_exists ( $user_path . $photo )) :
			
			$path = APPLICATION_PATH . '/../public/medias/anonyme.jpg';
		 

		else :
			
			$path = $user_path . $photo;
		

		endif;
		$finfo = finfo_open ( FILEINFO_MIME_TYPE );
		$type = finfo_file ( $finfo, $path );
		$this->_image = new Imagick ( $path );
		
		if ($typeImg == 'thumb')
			$this->_image->resizeImage ( 100, 100, imagick::FILTER_LANCZOS, 1, TRUE );
		
		header ( "Content-Type: $type" );
		
		echo $this->_image->getimageblob ();
		
		$this->_image->destroy ();
	}
	private function getFormDoc($hash) {
		$inscription = new Default_Model_InscriptionMapper ();
		
		$inscriptionModel = $inscription->getInscriptionFromHash ( $hash );
		
		$arrayReturn = array ();
		
		if ($inscriptionModel->__get ( 'certif' )) :
			
			$arrayReturn ["certif"] = array (
					"name" => "certificat.pdf",
					"size" => filesize ( APPLICATION_PATH . "/users_uploads/uploads/" . $inscriptionModel->__get ( 'certif' ) ) 
			);
		//$this->_userPath;
    	
    	endif;
		
		if ($inscriptionModel->__get ( 'photo' )) :
			
			$arrayReturn ["photo"] = array (
					"name" => "certificat.pdf",
					"size" => filesize ( APPLICATION_PATH . "/users_uploads/uploads/" . $inscriptionModel->__get ( 'photo' ) ),
					"url" => "/photo-identite/thumb/" . $inscriptionModel->__get ( 'saison' ) . "/" . $inscriptionModel->__get ( 'id_licence' ) 
			);
		//$this->_userPath;
    	 
    	endif;
		
		return $arrayReturn;
	}
	public function sendNewEmail() {
		$oldKey = $this->getRequest ()->oldKey;
		
		$mapper = new Default_Model_InscriptionMapper ();
		
		$user = $mapper->getInscriptionFromHash ( $oldKey );
		
		if (! $user)
			return array (
					"success" => false,
					"error" => "email inconnu" 
			);
		
		$sql = "select id_licence from " . $mapper->getDbTable ()->__get ( '_name' ) . " where email=? and saison=?";
		$res = $mapper->execQuery ( $sql, array (
				$user->__get ( 'email' ),
				$this->currentSeason 
		), 1 );
		
		$inscriptionModel = $mapper->find ( $res->id_licence );
		$hash = md5 ( $res->id_licence );
		
		$mail = new Default_Model_SendMail ();
		
		$style = 'font-family:Arial, Verdana; position: relative; display: block; font-size:12px; color:#000000; ';
		$prenom = utf8_decode ( $inscriptionModel->__get ( 'prenom' ) );
		$content = "<p style=\"$style\">Bonjour $prenom,<br />Voici le lien pour pouvoir modifier ton inscription au club :<br /> <a href=\"http://{$_SERVER['HTTP_HOST']}?authKey=$hash\">http://{$_SERVER['HTTP_HOST']}?authKey=$hash</a></p>";
		$content .= "<p style=\"$style\">A tr&egrave;s vite, le bureau des Dahuts</p>";
		$mail->sendMail ( $inscriptionModel->__get ( 'email' ), $content, "Lien pour modifier ton inscription chez les Dahultimates pour la saison " . $this->currentSeason );
		
		return array (
				"success" => true 
		);
	}
	public function sendEmailAgain() {
		$email = $this->getRequest ()->email;
		
		$mapper = new Default_Model_InscriptionMapper ();
		$id = $mapper->checkEmail ( $email, $this->currentSeason );
		
		if ($id == 0)
			return array (
					"success" => false,
					"error" => "email inconnu" 
			);
		
		$inscriptionModel = $mapper->find ( $id );
		
		$hash = md5 ( $id );
		
		$mail = new Default_Model_SendMail ();
		
		$style = 'font-family:Arial, Verdana; position: relative; display: block; font-size:12px; color:#000000; ';
		$prenom = utf8_decode ( $inscriptionModel->__get ( 'prenom' ) );
		$content = "<p style=\"$style\">Bonjour $prenom,<br />Voici le lien pour pouvoir modifier ton inscription au club :<br /> <a href=\"http://{$_SERVER['HTTP_HOST']}?authKey=$hash\">http://{$_SERVER['HTTP_HOST']}?authKey=$hash</a></p>";
		$content .= "<p style=\"$style\">A tr&egrave;s vite, le bureau des Dahuts</p>";
		$mail->sendMail ( $inscriptionModel->__get ( 'email' ), $content, "Lien pour modifier ton inscription chez les Dahultimates pour la saison " . $this->currentSeason );
		
		return array (
				"success" => true 
		);
	}
	public function displaycertifAction() {
		include_once ("My/Action/Helpers/Utils.php");
		
		$this->_helper->layout ()->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ( true );
		
		$saison = $this->getRequest ()->saison;
		$id_licence = $this->getRequest ()->id_licence;
		
		$mapper = new Default_Model_InscriptionMapper ();
		$user = $mapper->find ( $id_licence );
		
		$certif = $user->__get ( 'certif' );
		
		$path = $this->user_path . $certif;
		
		if (! file_exists ( $path ))
			die ( 'Erreur, le fichier est introuvable ou corrompu' );
		
		$finfo = finfo_open ( FILEINFO_MIME_TYPE );
		$type = finfo_file ( $finfo, $path );
		
		header ( "Content-type: " . $type );
		header ( "Content-Disposition: inline; filename=$certif" );
		@readfile ( $path );
	}
	public function updaterAction() {
		$this->_helper->layout ()->disableLayout ();
		
		$this->_helper->viewRenderer->setNoRender ( true );
		
		$apiCall = $this->_request->apiCall;
		
		if (method_exists ( $this, $apiCall )) {
			
			$this->{$apiCall} ();
		}
		
		$view = $this->view;
		
		$view->setScriptPath ( array (
				APPLICATION_PATH . '/views/scripts/updaters/',
				APPLICATION_PATH . '/views/scripts/' 
		) );
		
		$tpl = null == $this->getRequest ()->tpl ? $this->_request->getParam ( "tpl" ) : $this->getRequest ()->tpl;
		
		if (! file_exists ( APPLICATION_PATH . '/views/scripts/updaters/' . $tpl . '.phtml' ) && ! file_exists ( APPLICATION_PATH . '/views/scripts/' . $tpl . '.phtml' ))
			die ( "template introuvable" );
		echo $view->render ( $tpl . '.phtml' );
		die ();
	}
	public function importlicencesAction() {
		$this->_helper->layout ()->disableLayout ();
		
		$this->_helper->viewRenderer->setNoRender ( true );
		return;
		$fp = fopen ( APPLICATION_PATH . '/../public/licenciesDahuts.csv', 'r' );
		
		$model = new Default_Model_InscriptionMapper ();
		
		while ( $ligne = fgetcsv ( $fp, 1024, ';' ) ) :
			
			$ligne = array_map ( "utf8_encode", $ligne );
			$arrayDOB = explode ( "/", $ligne [3] );
			
			$data = array (
					"prenom" => $ligne [0],
					"nom" => $ligne [1],
					"dob" => $arrayDOB [2] . "/" . $arrayDOB [1] . '/' . $arrayDOB [0],
					"tel" => $ligne [4],
					"email" => $ligne [5],
					'saison' => $this->currentSeason,
					'adresse' => $ligne [6],
					"adresse2" => $ligne [7],
					"cp" => $ligne [8],
					"ville" => $ligne [9],
					"nationalite" => $ligne ['10'] 
			);
			
			$model->addData ( $data );
		endwhile
		;
	}
	public function mailinginscritionAction() {
		$this->_helper->layout ()->disableLayout ();
		
		$this->_helper->viewRenderer->setNoRender ( true );
		return;
		$model = new Default_Model_InscriptionMapper ();
		
		$licencies = $model->fetchAll ( "saison='2015/2016'" );
		
		$style = 'font-family:Arial, Verdana; position: relative; display: block; font-size:12px; color:#000000; ';
		
		foreach ( $licencies as $player ) :
			
			$prenom = utf8_decode ( $player ['prenom'] );
			$hash = md5 ( $player ['id_licence'] );
			
			$content = "<p style=\"$style\">Salut $prenom !<br />Les inscriptions pour la saison {$this->currentSeason} ont commenc&eacute; ! <br />Comme les Dahuts est un club &agrave; la pointe du progr&egrave;s et pour t'&eacute;viter de perdre du temps, nous avons pr&eacute;rempli ton formulaire d'inscription avec les informations que tu nous avais fourni l'ann&eacute;e derni&egrave;re<br />Pour finaliser ton inscription, il nous manque quelques informations</p>.";
			$content .= "<p style=\"$style\">Tu peux compl&eacute;ter tes informations en utilisant ce lien <a href=\"http://{$_SERVER['HTTP_HOST']}?authKey=$hash\">http://{$_SERVER['HTTP_HOST']}?authKey=$hash</a></p>";
			$content .= "<p><strong style=\"$style\">Reprise des entra&icirc;nements le mercredi 2 septembre !</strong></p>";
			$content .= "<p style=\"$style\">A tr&egrave;s vite, le bureau des Dahuts</p>";
			
			$mail = new Default_Model_SendMail ();
			$mail->sendMail ( $player ['email'], $content, "Hey $prenom ! Les inscriptions pour la saison " . $this->currentSeason . " sont ouvertes !" );
			
			echo "envoyer a " . $player ['email'];
		endforeach
		;
	}
	
	
	
	public function tournoiAction(){
		if($this->isMobile):
		$this->_helper->viewRenderer('tournoi-mobile');
		
		
		endif;

		if($_SERVER['REMOTE_ADDR']=="90.27.62.431")$this->render("tournoi-dev");
		if($this->isDev)$this->_helper->viewRenderer("maintenance");
		
		$this->view->headScript ()->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.15.0/jquery.validate.js' )->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.15.0/additional-methods.min.js' )->appendFile ( '/_js/lib/jquery.validate.popover.js' )->appendFile ( '/_js/lib/jquery.validation/messages_fr.js' )->appendFile ( '/_js/lib/jquery.validation/jquery.validate.override.js')->appendFile ( '/_js/lib/ajaxForm.js' )->appendFile('//cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js')
		->appendFile('//cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/locale/fr.js');
		
	}
	

	public function subscribeTournament(){
		
		$post =$this->isDev?$this->_request->getPost():$this->_request->getParams();
		if(count($post)==0)return ["success"=>false, "error"=>"Data non reconnus"];
	
		$post=(object)$post;
		$ln = $post->ln;
		$token=$post->token;
		
		
		$id = $post ->captcha['id'];
		$value = $post->captcha['input'];
		
		unset ( $post->captcha );
		$poste=$post->poste;
		unset ( $post->poste );
		// unset($post['captcha']['input']);

        $addrFanch="Fran&ccedil;ois TEXIER<br />80 route de Montfalcon 
<br />73410 LA BIOLLE";
		
		$post->adulte=(int)$post->adulte;
		$post->enfant=(int)$post->enfant;
		$arrayLang=(object)["fr"=>
		(object)[
		"TOKEN_ERROR"=>"D&eacute;sol&eacute;, il y a eu une erreur inattendue.<br />Essaye encore ou contact nous sur <a href=\"mailto:tournoi@dahultimate.com?subject=erreur lors de la validation de mon inscription\">tournoi@dahultimate.com</a>",
		"DOB_ERROR"=>"Ta date de naissance est incorrecte",
		"AGE_ERROR"=>"Tu dois avoir 16 ans le jour du tournoi",
		"RECAP"=>"<br />Tu seras accompagn&eacute; par :<br /><ul>".($post->adulte>0?"<li>{$post->adulte} adulte".($post->adulte>1?"s":""):"")."</li>".($post->enfant>0?"<li>{$post->enfant} enfant".($post->enfant>1?"s":"")."</li>":"")."</ul>",
		"CAPTCHA_ERROR"=>"Le code saisi est incorrecte",
		"EMAIL_HELLO"=>"Bonjour %s,",
		"EMAIL_USED"=>"Cette adresse mail a d&eacute;j&agrave; &eacute;t&eacute; utilis&eacute;e !",
		"MSG_NOCONFIRMATION"=>"nous avons bien re&ccedil;u ton d&eacute;sistement au Bornes to Catch, &agrave; notre plus grand d&eacute;sarroi !<br />%s<br />Mais comme nous ne sommes pas rancuniers, saches que si jamais tu changes d'avis, nous serons heureux de t'accueillir.",
		"MSG_CONFIRMATION"=>"nous sommes tr&egrave;s heureux que tu aies confirm&eacute; ta pr&eacute;sence au Bornes to Catch.",
		"MSG_COMPLEMENT_CONFIRMATION"=>"Tu ne sais pas ce que tu vas louper!",
		"MSG_SAISPAS"=>"comment &ccedil;a, tu ne sais pas si tu vas venir au Bornes to Catch ?.<br />%s<br />Allez, confirme nous vite ton inscription !",
		"MSG_MODIF_LINK"=>"Tu peux toujours modifier tes informations via ce <a href=\"%s\" style=\"color:black\">lien</a> ou en le copiant/collant %s",
		"MSG_PAYMENT"=>"Ce serait cool que tu puisses payer rapidement (avant le 31 juillet si possible) la somme de %s : <ul>".
		"<li>par ch&egrave;que, &agrave; l'ordre de l'association Dahultimate, &agrave; envoyer &agrave; :<br />$addrFanch</li>
		<li>par virement (<a href=\"{$_SERVER['HTTP_HOST']}/medias/rib.pdf\" style=\"color:black\">Le rib du club est ici</a>)</li></ul>",
		"MSG_NOTICE"=>"Tu recevras tr&egrave;s rapidement les derni&egrave;res nouvelles.",
				"MSG_DEEZER"=>"PS : N'oublie pas que tu peux collaborer &agrave; <a href=\"%s\" style=\"color:black\">la playlist officielle</a>.",
						"MSG_BYE"=>"A tr&egrave;s vite, Arvi pa !",
						"EMAIL_SUBJECT"=>"Ton inscription au Bornes to Catch",
						"CONFIRM"=>"Merci %s !"
								],
								"sav"=>
								(object)[
				"TOKEN_ERROR"=>"D&eacute;sol&eacute;, il y a eu une erreur inattendue.<br />Essaye encore ou contact nous sur <a href=\"mailto:tournoi@dahultimate.com?subject=erreur lors de la validation de mon inscription\">tournoi@dahultimate.com</a>",
				"CAPTCHA_ERROR"=>"Le code saisi est incorrecte",
				"EMAIL_USED"=>"Cette adresse mail a d&eacute;j&agrave; &eacute;t&eacute; utilis&eacute;e !",
				"DOB_ERROR"=>"Ta date de naissance est incorrecte",
				"AGE_ERROR"=>"Tu dois avoir 16 ans le jour du tournoi",
				"EMAIL_HELLO"=>"Adio don' %s,",
				"MSG_NOCONFIRMATION"=>"nous avons bien re&ccedil;u ton d&eacute;sistement au Bornes to Catch, &agrave; notre plus grand d&eacute;sarroi !<br />%s<br />Mais comme nous ne sommes pas rancuniers, saches que si jamais tu changes d'avis, nous serons heureux de t'accueillir.",
				"MSG_CONFIRMATION"=>"nous sommes tr&egrave;s heureux que tu aies confirm&eacute; ta pr&eacute;sence au Bornes to Catch.",
				"MSG_COMPLEMENT_CONFIRMATION"=>"Tu ne sais pas ce que tu vas louper!",
				"MSG_SAISPAS"=>"comment &ccedil;a, tu ne sais pas si tu vas venir au Bornes to Catch ?.<br />%s<br />Allez, confirme nous vite ton inscription !",
						],
						"uk"=>
						(object)[
						"TOKEN_ERROR"=>"Sorry, an unexpected error occured.<br />Please try again or contact us on <a href=\"mailto:tournoi@dahultimate.com?subject=erreur lors de la validation de mon inscription\">tournoi@dahultimate.com</a>",
						"CAPTCHA_ERROR"=>"The code is not correct",
						"EMAIL_USED"=>"This email address has already been registered",
						"DOB_ERROR"=>"Your date of birth is not correct",
								"AGE_ERROR"=>"You must be over 16 the day of the tournament",
										"EMAIL_HELLO"=>"Dear %s,",
										"MSG_NOCONFIRMATION"=>"we are very disappointed to learn you won't come to the Bornes to Catch !<br />%s<br />But if your change your mind, we'll be glad to welcome you.",
										"MSG_CONFIRMATION"=>"we are very happy that you confirmed you'll be at the Bornes to Catch.",
										"MSG_COMPLEMENT_CONFIRMATION"=>"You can't imagine what you will miss !",
										"MSG_SAISPAS"=>"What the f... ? You don't know if you are coming to the Bornes to Catch ?.<br />%s<br />Confirm as soon as possible your attendance !",
										"MSG_MODIF_LINK"=>"You can modify your information by clicking on this <a href=\"%s\" style=\"color:black\">link</a> or copy/paste %s",
						"MSG_PAYMENT"=>"It would be nice if you could paye the fee of %s : <ul><li>by bank check (payable to Association Dahultimate) to the following address : <br />$addrFanch</li>
								<li>or by bank transfer(<a href=\"{$_SERVER['HTTP_HOST']}/medias/rib.pdf\" style=\"color:black\">Our RIB is here</a>)</li></ul>",
						"MSG_NOTICE"=>"You'll be noticed very soon of the last news.",
						"MSG_BYE"=>"See you soon, Arvi pa !",
						"EMAIL_SUBJECT"=>"Your subscription to the Bornes to Catch",
						"CONFIRM"=>"Thank You %s !<br />You will receive an email soon.",
						"RECAP"=>"<br />You'll be accompanied by :<ul>".($post->adulte>0?"<li>{$post->adulte} adult".($post->adulte>1?"s":"")."</li>":"").($post->enfant>0?"<li>{$post->enfant} ".($post->enfant>1?"children":"child")."</li>":"")."</ul>",
						"MSG_DEEZER"=>"PS : Don't forget, you can add your favorites tracks to <a href=\"%s\" style=\"color:black\">the official playlist</a>.",
										]
											
										];
		
										$tradf=$arrayLang->{$ln};
		
										$captchaSession = new Zend_Session_Namespace ( 'Zend_Form_Captcha_' . $id );
										$captchaIterator = $captchaSession->getIterator ();
										$captchaWord = $captchaIterator ['word'];
										$ok=$this->isDev?true:false;
		
										if ($captchaWord !== $value&&$ok)
			return array (
							"success" => false,
							"error" => $tradf->CAPTCHA_ERROR
										);
		
										if((int)$post->confirm!==2):
		
										$validator = new Zend_Validate_EmailAddress ();
										if (! $validator->isValid ( $post ->email )&&$ok)
											return array (
													"success" => false,
													"error" => $tradf->EMAIL_ERROR
										);
		
										$validator = new Zend_Validate_Date ( array (
												'format' => 'dd/mm/yyyy'
										) );
												if (! $validator->isValid ( $post ->dob ))
													return array (
												"success" => false,
												"error" => $tradf->DOB_ERROR
										);
		
		
										// test age
												$date = new Zend_Date ( $post ->dob, 'dd/MM/yyyy' );
		
												$today = new Zend_Date('2016-08-20');
												$dateOfBirth = new Zend_Date($date);
		
													/*** Considering the leap years if the birth date is on 1st march or later. ***/
													if ($dateOfBirth->get('M') > 2) {
													/*** Checking if the birth's year is a leap year. ***/
													if ($dateOfBirth->get(Zend_Date::LEAPYEAR)) {
													$birthLeap = 1;
		}
		
													/*** Checking if today's year is a leap year. ***/
													if ($today->get(Zend_Date::LEAPYEAR)) {
													$todayLeap = 1;
		}
		}
		
		$age = $today->get('Y') - $dateOfBirth->get('Y');
		
		if ($today->get('D') - $todayLeap < $dateOfBirth->get('D') - $birthLeap) {
		$age--;
		}
		
		if($age<16)	return array (
				"success" => false,
				"error" => $tradf->AGE_ERROR
		);
		
		//$post=array_map("utf8_decode", $post);
		$post->accompagne=(int)$post->accompagne==1&&((int)$post->adulte>0||(int)$post->enfant>0);
		
		endif;

		$mapper=new Default_Model_TournoiMapper();
		
		if(!empty($token)):
		
		$check=$this->checkToken($token);
	
		
		if(!$check){return ["success"=>false,"error"=>$tradf->TOKEN_ERROR];};
		
		
		$joueur=$mapper->find($check->id_inscription);
		
		else:
		
		$check=$mapper->checkEmail($post->email);
		$post->confirm=1;
		
	
		if($check>0&&$ok) return ["success"=>false, "error"=>$tradf->EMAIL_USED];
		
		$joueur=$mapper->getModel();
		
		endif;
		
		
		
		
		
		
		if((int)$post->confirm!==2):
		foreach($post as $key=>$value)$joueur->__set($key, $value);
		$joueur->__set('poste', implode(",",$poste));
		
		$joueur->__set('dob', $date->get ( 'yyyy-MM-dd' ));
		else:
		
		$joueur->__set('confirm',$post->confirm);
		$joueur->__set('reasons',$post->reasons);
		
		endif;
		
		
		$accompagne=(int)$post->accompagne==1&&((int)$post->adulte>0||(int)$post->enfant>0);
		
		if(!$accompagne):
		$joueur->__set('enfant',0);
		$joueur->__set('adulte',0);
		endif;
		
		$newID=$mapper->addData($joueur->dataToArray());
		
		if(empty($token))$token=sha1($joueur->__get('email').$newID);
		
		if((int)$joueur->__get("confirm")==1)unset($post->reasons);
		
		//calcul du montant
		
		
		/*
												* define('PRIX_JOUEUR', 55);
												define('PRIX_NONJOUEUR', 40);
												define('PRIX_ENFANT', 25);
		 */
		
		
												$recap=	sprintf($tradf->EMAIL_HELLO, utf8_decode($joueur->__get('prenom')));
		$recap.="<br />";
		
		
		
		$playlistURL="http://www.deezer.com/playlist/4472473084?utm_source=deezer&utm_content=playlist-4472473084&utm_term=14147506_1526321087&utm_medium=web";
		
				if($ln!=="uk")$tradf=$arrayLang->fr;
		
				switch((int)$joueur->__get('confirm')):
		
				case 1:
		
				$recap.=$tradf->MSG_CONFIRMATION;
		
				break;
				case 2 :
					
				$recap.=sprintf($tradf->MSG_NOCONFIRMATION,$tradf->MSG_COMPLEMENT_CONFIRMATION);
				break;
		
				case 3:
				$recap.=sprintf($tradf->MSG_SAISPAS,$tradf->MSG_COMPLEMENT_CONFIRMATION);
				break;
					
				endswitch;
		
				$recap.="<br />".$emailMsg.=sprintf($tradf->MSG_MODIF_LINK, "http://tournoi.dahultimate.com/confirm?token=$token&ln=$ln", "http://tournoi.dahultimate.com/confirm?token=$token&ln=$ln");;
		
				if($accompagne)$recap.=$tradf->RECAP;
				
				if((int)$joueur->__get('confirm')==1):
		
				$prix=((int)$joueur->__get('type')==1?PRIX_JOUEUR:PRIX_NONJOUEUR)+(int)$joueur->__get('adulte')*PRIX_NONJOUEUR+(int)$joueur->__get('enfant')*PRIX_ENFANT;
		
				$recap.="<br />".sprintf($tradf->MSG_PAYMENT,number_format($prix,2)." &euro;");
		
				$recap.="<br />".$tradf->MSG_NOTICE;
		
				$recap.="<br />".sprintf($tradf->MSG_DEEZER, $playlistURL);
		
				endif;
		
				$recap.="<br /><br />".$tradf->MSG_BYE;
		
				$emailMsg=$recap;
				
		$mail = new Default_Model_SendMail();
		$style = 'font-family:Arial, Verdana; position: relative; display: block; font-size:14px; color:#000000; ';
		$emailMsg='<p style="'.$style.'">'.$emailMsg.'</p>';
			
			
		$mail->setLogo("http://".$_SERVER['HTTP_HOST']."/medias/logo-mail-tournoi.png");
		
				$mail->sendMail($this->dev?"chau@plusdeclic.com":$joueur->__get('email'), $emailMsg, $tradf->EMAIL_SUBJECT, ["address"=>"tournoi@dahultimate.com","name"=>"Bornes to catch"]);
		
		
				$recap="Io, ".utf8_decode($joueur->__get('prenom'))." ".utf8_decode($joueur->__get('nom'));
		
				unset ( $post ->token );
				unset ( $post ->apiCall );
				unset ( $post ->controller );
				unset ( $post ->action );
				unset ( $post ->format );
				unset ( $post ->confirm );
				unset ( $post ->ln );
						unset ( $post ->format );
		
		
						switch((int)$joueur->__get('confirm')):
		
						case 1:
						$subject=utf8_decode($joueur->__get('prenom'))." ".utf8_decode($joueur->__get('nom'))." confirme son inscription !";
						$recap.=" a confirm&eacute; son inscription. Le montant de sa participation est de $prix &euro;";
		
							
		
						break;
						case 2 :
		
						$subject=utf8_decode($joueur->__get('prenom'))." ".utf8_decode($joueur->__get('nom'))." annule son inscription :( !";
		
			$recap.=" a annul&eacute; son inscription.";
					$recap.="<br />Ses raisons :".nl2br(utf8_decode($joueur->__get('reasons')));
								
					break;
		
					case 3:
					$subject=utf8_decode($joueur->__get('prenom'))." ".utf8_decode($joueur->__get('nom'))." ne sait toujours pas si il viendra !";
		
			$recap.=" ne sait toujours pas si il viendra.";
					$recap.="<br />Ses raisons :".nl2br(utf8_decode($joueur->__get('reasons')));
			break;
		
							endswitch;
							
							$emailMsg=$recap."";
							
						
							
							
		
			$mail->sendMail($this->isDev?"chau@plusdeclic.com":"tournoi@dahultimate.com", $emailMsg, $subject);
		
		
				return ["success"=>true, "msg"=>sprintf($tradf->CONFIRM,($joueur->__get('prenom')))];
		
		
		
			
		
		
		
		
		
		
		
		
		$id = $post ['captcha'] ['id'];
		$value = $post ['captcha'] ['input'];
		
		unset ( $post ['captcha'] );
		$poste=$post['poste'];
		unset ( $post ['poste'] );
		// unset($post['captcha']['input']);
		
		
		//$post=array_map("utf8_decode", $post);
		$post['accompagne']=(int)$post['accompagne']==1&&((int)$post['adulte']>0||(int)$post['enfant']>0);
		$post['adulte']=(int)$post['adulte'];
		$post['enfant']=(int)$post['enfant'];
		
		
		$arrayLang=(object)["fr"=>
		(object)[
		"EMAIL_ERROR"=>"Ton email est incorrect",
		"EMAIL_USED"=>"Cette adresse mail a d&eacute;j&agrave; &eacute;t&eacute; utilis&eacute;e !",
		"DOB_ERROR"=>"Ta date de naissance est incorrecte",
		"AGE_ERROR"=>"Tu dois avoir 16 ans le jour du tournoi",
		"CAPTCHA_ERROR"=>"Le code saisi est incorrecte",
		"SUCCESS"=>"Merci ".$post['prenom']." ton inscription a bien &eacute;t&eacute; prise en compte.",
		"MAIL_SUBJECT"=>"Ton inscription au Bornes to Catch",
		"MAIL"=>"Bonjour ".utf8_decode($post['prenom']).",<br />ton inscription pour le Bornes to catch a bien &eacute;t&eacute; prise en compte !<br />Nous t'enverrons la confirmation d&eacute;but juillet avec tous les derniers d&eacute;tails.",
		"RECAP"=>"<br />Tu seras accompagn&eacute; par :<br />".($post["adulte"]>0?"{$post["adulte"]} adulte".($post["adulte"]>1?"s":""):"").($post["enfant"]>0?"<br />{$post["enfant"]} enfant".($post["enfant"]>1?"s":""):""),
		"MERCI"=>"<br />A tr&egrave;s vite, on a h&acirc;te de te voir !<br />Les DahultimatesPS : n'h&eacute;site pas &agrave; nous contacter sur tournoi@dahultimate.com si tu souhaites plus d'informations ou si tu veux modifier ton inscription"
				],
				"sav"=>
				(object)[
				"EMAIL_ERROR"=>"Ton email est pas bon",
				"EMAIL_USED"=>"Cette adresse mail a d&eacute;j&agrave; &eacute;t&eacute; utilis&eacute;e !",
				"CAPTCHA_ERROR"=>"Le code saisi est incorrecte",
				"DOB_ERROR"=>"Ta date de naissance est incorrecte",
				"AGE_ERROR"=>"Tu dois avoir 16 ans le jour du tournoi",
				"SUCCESS"=>"Merci ".$post['prenom']." ton inscription a bien &eacute;t&eacute; prise en compte.",
				"MAIL_SUBJECT"=>"Ton inscription au Bornes to Catch",
				"MAIL"=>"Bonjour ".utf8_decode($post['prenom']).",<br />ton inscription pour le Bornes to catch a bien &eacute;t&eacute; prise en compte !<br />Nous t'enverrons la confirmation d&eacute;but juillet avec tous les derniers d&eacute;tails.",
				"RECAP"=>"<br />Tu seras accompagn&eacute; par :<br />".($post["adulte"]>0?"{$post["adulte"]} adulte".($post["adulte"]>1?"s":""):"").($post["enfant"]>0?"<br />{$post["enfant"]} croet".($post["enfant"]>1?"s":""):""),
				"MERCI"=>"<br />Arvi pa, on a h&acirc;te de te voir !<br />Les Dahultimates<br />PS : n'h&eacute;site pas &agrave; nous contacter sur tournoi@dahultimate.com si tu souhaites plus d'informations ou si tu veux modifier ton inscription"
						],
						"uk"=>
						(object)[
						"EMAIL_ERROR"=>"Your email address is not correct",
						"EMAIL_USED"=>"This email address has already been registered",
						"CAPTCHA_ERROR"=>"The code is not correct",
						"DOB_ERROR"=>"Your date of birth is not correct",
						"AGE_ERROR"=>"You must be over 16 the day of the tournament",
						"SUCCESS"=>"Great ".$post['prenom']." !<br /> you've just been registered.",
						"MAIL_SUBJECT"=>"Your subscription to the Bornes to Catch",
						"MAIL"=>"Hi ".utf8_decode($post['prenom']).",<br />you subscription to the Bornes to catch has been registered !<br />You will receive the latest news by the 1st of july.",
						"RECAP"=>"<br />You'll be accompanied by :".($post["adulte"]>0?"{$post["adulte"]} adult".($post["adulte"]>1?"s":""):"").($post["enfant"]>0?" {$post["enfant"]} ".($post["enfant"]>1?"children":"child"):""),
						"MERCI"=>"<br />See you soon, we are looking forward to seeing you !<br />The Dahultimate team<br />PS : feel free to contact us at tournoi@dahultimate.com us for further information or if you wish to modify your subscription"
								]
									
								];
		
		$tradf=$arrayLang->{$post['ln']};
		
		$captchaSession = new Zend_Session_Namespace ( 'Zend_Form_Captcha_' . $id );
		$captchaIterator = $captchaSession->getIterator ();
		$captchaWord = $captchaIterator ['word'];
		
		$ok=$this->isDev?true:false;
		
		if ($captchaWord !== $value&&$ok)
			return array (
					"success" => false,
					"error" => $tradf->CAPTCHA_ERROR
			);
		
		$validator = new Zend_Validate_EmailAddress ();
		if (! $validator->isValid ( $post ['email'] )&&$ok)
			return array (
					"success" => false,
					"error" => $tradf->EMAIL_ERROR
			);
		
		$validator = new Zend_Validate_Date ( array (
				'format' => 'dd/mm/yyyy'
		) );
		if (! $validator->isValid ( $post ['dob'] ))
			return array (
					"success" => false,
					"error" => $tradf->DOB_ERROR
			);
		
		
		// test age
		$date = new Zend_Date ( $post ['dob'], 'dd/MM/yyyy' );
		
		$today = new Zend_Date('2016-08-20');
		$dateOfBirth = new Zend_Date($date);
		
		/*** Considering the leap years if the birth date is on 1st march or later. ***/
		if ($dateOfBirth->get('M') > 2) {
			/*** Checking if the birth's year is a leap year. ***/
			if ($dateOfBirth->get(Zend_Date::LEAPYEAR)) {
				$birthLeap = 1;
			}
		
			/*** Checking if today's year is a leap year. ***/
			if ($today->get(Zend_Date::LEAPYEAR)) {
				$todayLeap = 1;
			}
		}
		
		$age = $today->get('Y') - $dateOfBirth->get('Y');
		
		if ($today->get('D') - $todayLeap < $dateOfBirth->get('D') - $birthLeap) {
			$age--;
		}

		if($age<16)	return array (
					"success" => false,
					"error" => $tradf->AGE_ERROR
			);
		
		
		$model= new Default_Model_TournoiMapper();
				
		$check=$model->checkEmail($post['email']);
		if($check>0&&$ok) return ["success"=>false, "error"=>$tradf->EMAIL_USED];
		
		if((int)$post['accompagne']==0):
		$post['adulte']=$post['enfant']=0;
		
		endif;
		$post['poste']=implode(',',$poste);
		
		$model2=$model->getModel();
		
		
		$post ['dob'] = $date->get ( 'yyyy-MM-dd' );
		$data=[];
		
		foreach($model2->getFields() as $field):
		
		$data[$field]=$post[$field];
		
		
		endforeach;
		$style="style=\"font-family:Arial\"";
		
		$data['confirm']=1;
		
		var_dump($data);
		
		die();
		
		$model->addData($data);

		$mail= new Default_Model_SendMail();
		
		$msg="<p $style>".$tradf->MAIL.($post['accompagne']?$tradf->RECAP:"")."<br />".$tradf->MERCI."</p>";
			$mail->sendMail($post['email'], $msg, $tradf->MAIL_SUBJECT, ["address"=>"tournoi@dahultimate.com","name"=>"Bornes to catch"]);
		$post=array_map("utf8_decode", $post);
		
		$subject="Nouvelle inscription pour le tournoi";
		
		$arrayPoste=$this->_arrayPoste;
		
		$msg="<p>Io,<br />{$post["prenom"]} {$post["nom"]} vient de s'incrire pour le tournoi.</p>";
				$msg.="<ul>".
				"<li>Participe en tant que  : ".((int)$post["type"]==1?"joueur":"accompagnant")."</li>".
				"<li>Email : ".$post["email"]."</li>".
				"<li>Date de naissance : ".$post["dob"]."</li>".
				"<li>Sexe : ".((int)$post["sexe"]==1?"Femme":"Homme")."</li>".
				($post["accompagne"]?"<li>Accompagn&eacute; par :".($post["adulte"]>0?"{$post["adulte"]} adulte".($post["adulte"]>1?"s":""):"").($post["enfant"]>0?" {$post["enfant"]} enfant".($post["enfant"]>1?"s":"")."</li>":""):"");
		

		
		if((int)$post["type"]==1):
		
		$msg.="<li>Club :".((int)$post['noclub']==1?"":$post["club"])."</li>".
			"<li>Poste :";
		foreach($poste as $poste2)$msg.=$arrayPoste[$poste2].", ";
		$msg=substr($msg,0,-2);
		
		$msg.="</li>".
		"<li>Technique : ".$post["technique"]."</li>".
		"<li>Physique : ".$post["physique"]."</li>";
		
		
		endif;
				
				$msg.="</ul>";
				
	if(!empty($post["comment"]))$msg.="<p $style>Commentaire :<br />&laquo; {$post["comment"]} &raquo;</p>";
				
		//$mail->sendMail("tournoi@dahultimate.com",$msg, $subject);
				
		
		return ["success"=>true,"msg"=>$tradf->SUCCESS];
		

	}

	
	public function checkToken($token){
	
	
		$mapper=new Default_Model_TournoiMapper();
	
	
		$joueur=$mapper->execQuery("select * from ".$mapper->getDbTable()->__get("_name")." where sha1(concat(email,id_inscription))=?", array($token),1);
	
		return $joueur;
	
	
	}

	
	public function updateInscription(){
		
		
		
		if($_COOKIE['admin']!==sha1("dadahu".strftime('%Y%m%d', time()))) return ["success"=>false,"msg"=>"Erreur de connexion"];
		
		$data=$this->_request->getParams();
		$id=$data['id_inscription'];
		
		
		$mapper = new Default_Model_TournoiMapper();
		
		$joueur=$mapper->find($id);
		
		
		if(!$joueur)return ["success"=>false,"msg"=>"Joueur introuvable"];
		
		
		
		
		foreach($data as $key=>$value)$joueur->__set($key, $value);
		
		
		$mapper->addData($joueur->dataToArray());
		
		return ["success"=>true,"msg"=>"{$joueur->__get("prenom")} {$joueur->__get("nom")} mis &agrave; jour"];
		
		
		
	}

	
	public function exportInscriptions() {
		$licences = $this->_request->inscr;
		
		
		$mapper = new Default_Model_TournoiMapper();
		$headers = [
		
				];
		
		$file = tempnam ( "tmp", "zip" );

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$spreadsheet->setActiveSheetIndex(0);
		$sheet=$spreadsheet->getActiveSheet();


		$zip = new ZipArchive();
		$zip->open($file, ZipArchive::OVERWRITE);
		$fp = fopen ( APPLICATION_PATH . '/export/export.csv', 'w+' );
	//	fputcsv ( $fp, $headers, ";" );

		$row=0;

		foreach ( $licences as $licence ) :
		$licenceData = $mapper->find ( $licence );


		$sheet->fromArray($licenceData->dataToArray(),null, 'A'.$row);
		$row++;


		/*$joueur = [
		$licenceData->__get ( 'nom' ),
		$licenceData->__get ( 'prenom' ),
		$licenceData->__get ( 'email' ),
		( int ) $licenceData->__get ( 'sexe' ) == 2 ? 'M' : 'F',
		( int ) $licenceData->__get ( 'type' ) == 1 ? 'Joueur' : 'Spectateur',
		$licenceData->__get ( 'club' ),
		strftime('%d/%m/%Y',strtotime($licenceData->__get('dob'))),
		$licenceData->__get ( 'poste' )
				];
			
		//$joueur=array_map('utf8_decode',$joueur);
		fputcsv($fp, $joueur,";");
		*/
		endforeach
		;
		
		
		fclose($fp);

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->setOffice2003Compatibility(true);
		$writer->save(APPLICATION_PATH . '/export/export_tournoi.xlsx');

		$spreadsheet->disconnectWorksheets();
		$zip->addFile(APPLICATION_PATH . '/export/export_tournoi.xlsx','liste.xlsx');
		
		$zip->close();
		
		header('Content-Type: application/zip');
		header('Content-Length: ' . filesize($file));
		header('Content-Disposition: attachment; filename="export_licence_dahuts.zip"');
		readfile($file);
		unlink($file);
		unlink(APPLICATION_PATH . '/export/export.csv');
		
		
		
	}

	public function b2cAction()
    {

    }


}

