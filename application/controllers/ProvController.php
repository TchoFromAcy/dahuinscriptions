<?php

include_once("IndexController.php");

class ProvController extends IndexController {
		

	protected $dev=true;
	
	public function preDispatch() {

				
		$this->context=preg_match('/tournoi/',$_SERVER['HTTP_HOST'])?'tournoi':'inscription';
		
	
		$this->view->headMeta ()->appendHttpEquiv ( 'Content-Type', 'text/html; charset=UTF-8' )->appendHttpEquiv ( 'Content-Language', 'fr-FR' );
		if($this->context!=="tournoi")$this->view->headLink ()->appendStylesheet ( '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css' );
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
	
	
	
	public function confirmTournoiAction(){
		$this->view->headLink ()->prependStylesheet ( '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css' );
		$this->view->headScript ()->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.15.0/jquery.validate.js' )->appendFile ( '//ajax.aspnetcdn.com/ajax/jquery.validate/1.15.0/additional-methods.min.js' )->appendFile ( '/_js/lib/jquery.validate.popover.js' )->appendFile ( '/_js/lib/jquery.validation/messages_fr.js' )->appendFile ( '/_js/lib/jquery.validation/jquery.validate.override.js')->appendFile ( '/_js/lib/ajaxForm.js' )->appendFile('//cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js')
		->appendFile('//cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/locale/fr.js');
		
		if($this->isMobile):
		$this->_helper->layout->setLayout('tournoi-mobile');
		$this->_helper->viewRenderer('confirm-mobile');
		else :
		$this->_helper->layout->setLayout('tournoiconfirm');
		
		endif;
		
		
		
		$this->view->maintenance=false;
		
		$this->view->setScriptPath ( array (
				APPLICATION_PATH . '/views/scripts/updaters/',
				APPLICATION_PATH . '/views/scripts/',
				APPLICATION_PATH . '/views/scripts/index/'
		) );
		
		
		$token=$this->_request->token;
		
		$this->view->authenticated=!empty($token);
		
		if(!(empty($token))):
		$this->view->joueur=$this->checkToken($token);
		
		
		
		endif;
		
		
	}
	
	
	
	
	public function resendEmail(){
		$email= $this->_request->email;
		$ln=$this->_request->ln;
		
		$arrayLang=(object)["fr"=>
		(object)[
		"EMAIL_ERROR"=>"D&eacute;sol&eacute;, cette adresse email n'est pas reconnue. Peut-&ecirc;tre as tu utilis&eacute; une autre adresse ?"
						],
				"sav"=>
				(object)[
				"EMAIL_ERROR"=>"D&eacute;sol&eacute;, cette adresse email n'est pas reconnue. Peut-&ecirc;tre as tu utilis&eacute; une autre adresse ?"
						],
						"uk"=>
						(object)[
						"EMAIL_ERROR"=>"Sorry, this e-mail address is unknown. Maybe you used another address ?"
								]
									
								];
		
		$tradf=$arrayLang->{$ln};
		
		
		
		$mapper = new Default_Model_TournoiMapper();
		
		$check=$mapper->checkEmail($email);
		
		$ln=$this->_request->ln;
		
		if(!$check)return ["success"=>false,"error"=>$tradf->EMAIL_ERROR];
		
		return $this->sendConfirmMail($check, $ln);
		
	}
	
	
	
	function sendConfirmMail($id=0, $ln="sav"){

		$arrayLang=(object)["fr"=>
		(object)[
		"EMAIL_ERROR"=>"D&eacute;sol&eacute;, cette adresse email n'est pas reconnue.",
		"EMAIL_MSG"=>"Salut %s, <br />plus que quelques semaines avant le Bornes 2 be 3, nous sommes tr&egrave;s heureux de te compter parmi nous.<br />".
		"Tout est presque pr&ecirc;t pour te faire passer un super week-end !<br />",
		"EMAIL_NOPOSTE"=>"Lors de ton inscription, un coup de vent a fait bugger le syst&egrave;me et les postes que tu avais indiqu&eacute;s se sont perdus en route :( !",
		"EMAIL_MSG2"=>"Merci de confimer ton inscription ainsi que les informations que tu nous as fournies (certaines inscriptions ont bugg&eacute; &agrave; cause d'un bug qui a fait bugger au niveau de la base de donn&eacute;es, bref je te raconte ma vie), donc si tu peux cliquer sur ce <a href=\"%s\" style=\"color:black\">lien</a> ou en le copiant/collant ( <em>%s</em> - je sais, c'est long), nous avons en outre quelques questions suppl&eacute;mentaires &agrave; te poser.",
		"EMAIL_BYE"=>"&Agrave; tr&egrave;s vite !",
		"EMAIL_SUBJECT"=>"He %s, confirme vite ton inscription au Bornes to Catch !",
		"CONFIRM"=>"Merci %s, un email t'as &eacute;t&eacute; adress&eacute; &agrave; cette adresse",
		"SIGNATURE"=>"Les Dahultimates",
		"PS"=>"Si tu as des questions, n'h&eacute;site pas &agrave; nous contacter sur <a href=\"mailto:tournoi@dahultimate.com\" style=\"color:black\">tournoi@dahultimate.com</a>",
		
				],
				"sav"=>
				(object)[
				"EMAIL_ERROR"=>"D&eacute;sol&eacute;, cette adresse email n'est pas reconnue.",
				"EMAIL_MSG"=>"Salut %s, <br />plus que quelques semaines avant le Bornes 2 be 3,<br />nous sommes tr&egrave;s heureux de te compter parmi nous.<br />".
				"Tout est presque pr&ecirc;t pour te faire passer un super week-end !<br />",
				"EMAIL_NOPOSTE"=>"Lors de ton inscription, un coup de vent a fait bugger le syst&egrave;me et les postes que tu avait indiqu&eacute;s se sont perdus en route :( !",
				"EMAIL_MSG2"=>"Merci de confimer ton inscription ainsi que les informations que tu nous as fournies (certaines inscriptions ont bugg&eacute; &agrave; cause d'un bug qui a fait bugger au niveau de la base de donn&eacute;es, bref je te raconte ma vie), donc si tu peux cliquer sur ce <a href=\"%s\">lien</a> ou en le copiant/collant %s, nous avons quelques questions suppl&eacute;mentaires &agrave; te poser.",
				"EMAIL_BYE"=>"&Agrave; tr&egrave;s vite !",
				"EMAIL_SUBJECT"=>"He %s, confirme vite ton inscription au Bornes to Catch !",
				"CONFIRM"=>"Merci %s, un email t'as &eacute;t&eacute; adress&eacute; &agrave; cette adresse"
						],
						"uk"=>
						(object)[
						"EMAIL_ERROR"=>"Sorry, this e-mail address is unknown.",
						"EMAIL_MSG"=>"Hi %s, <br />the Bornes 2 be 3 will take place in a few weeks from now,<br />we are so pleased you are in.<br />".
						"Everything is almost ready to make this week-end very special for you !<br />",
						"EMAIL_NOPOSTE"=>"Unfortunatelly, we had a bug during your subscription and we lost your positions :( !",
						"EMAIL_MSG2"=>"Please, confirm your subscription following this <a href=\"%s\" style=\"color:black\">link</a> (or copy/paste it <em>%s</em> - I know, it's long), we have a couple of questions to ask you",
						"EMAIL_BYE"=>"See you soon !",
						"EMAIL_SUBJECT"=>"Hey %s, we need you to confirm your subscription to the Bornes to Catch !",
						"CONFIRM"=>"Thanks %s, we've just sent an email at this address",
						"SIGNATURE"=>"The Dahultimate Team",
						"PS"=>"Feel free to contact us at <a href=\"mailto:tournoi@dahultimate.com\" style=\"color:black\">tournoi@dahultimate.com</a>",
								]
									
								];
		
		$tradf=$arrayLang->{$ln};
		
		$mapper = new Default_Model_TournoiMapper();
		
		$joueur=$mapper->find((int)$id);
		
		if(!$joueur) return ["success"=>false,"error"=>$tradf->EMAIL_ERROR];
		
		$poste=$joueur->__get('poste');
		
		$noposte=empty($poste);
		
		$token = sha1($joueur->__get('email').$id);
		
		$emailMsg=sprintf($tradf->EMAIL_MSG,utf8_decode($joueur->__get('prenom')));
		$emailMsg.=sprintf($tradf->EMAIL_MSG2, "http://bornestocatch.dahultimate.com/confirm?token=$token&ln=$ln", "http://bornestocatch.dahultimate.com/confirm?token=$token&ln=$ln");
		
		$emailMsg.="<br /><br />".$tradf->EMAIL_BYE.'<br /><br /><strong>'.$tradf->SIGNATURE."</strong><br /><br />PS : ".$tradf->PS;
		
		$mail=new Default_Model_SendMail();
		$style = 'font-family:Arial, Verdana; position: relative; display: block; font-size:14px; color:#000000; ';
		$emailMsg='<p style="'.$style.'">'.$emailMsg.'</p>';	
	
		
		$mail->setLogo("http://".$_SERVER['HTTP_HOST']."/medias/logo-mail-tournoi.png");
		
		
		$mail->sendMail($joueur->__get('email'), $emailMsg, sprintf($tradf->EMAIL_SUBJECT, utf8_decode($joueur->__get('prenom'))), ["address"=>"tournoi@dahultimate.com","name"=>"Bornes to catch"]);
		$mail->sendMail("chau@plusdeclic.com;", $emailMsg, sprintf($tradf->EMAIL_SUBJECT, utf8_decode($joueur->__get('prenom'))), ["address"=>"tournoi@dahultimate.com","name"=>"Bornes to catch"]);
		
	
		
		return ["success"=>true, "msg"=>sprintf($tradf->CONFIRM,($joueur->__get('prenom')))];
		
		
	}
	

	function validateTournament(){
		
		
	return	$this->subscribeTournament();
	}

	
	public function sendAction(){
		
		echo $_SERVER['REMOTE_ADDR'];
		die();
		if($_SERVER['REMOTE_ADDR']!=="90.27.59.135") die();
		
		$mapper=new Default_Model_TournoiMapper();
		$query=$mapper->fetchAll("confirm is null or confirm=0");

		
		
		
		foreach($query as $joueur):
		echo $joueur['email'];
		$this->sendConfirmMail($joueur['id_inscription'],"fr");
	
		endforeach;
		
		echo count($query);
		
		die();
		
		
	}
	
	
	public function sendpayementAction(){
	

	
		if($_SERVER['REMOTE_ADDR']!=="37.71.122.170") die("nope");
	
		$mapper=new Default_Model_TournoiMapper();
		$query=$mapper->fetchAll("confirm=1 and (payed is null or payed=0)");
	
		$playlistURL="http://www.deezer.com/playlist/4472473084?utm_source=deezer&utm_content=playlist-4472473084&utm_term=14147506_1526321087&utm_medium=web";
		$style = 'font-family:Arial, Verdana; position: relative; display: block; font-size:12px; color:#000000; ';

        $addrFanch="Fran&ccedil;ois TEXIER<br />80 route de Montfalcon 
<br />73410 LA BIOLLE";
		
		$mail=new Default_Model_SendMail();
		$mail->setLogo("http://".$_SERVER['HTTP_HOST']."/medias/logo-mail-tournoi.png");
		
		foreach($query as $joueur):
		//echo $joueur['email'];
		
		
		
		$joueur=(object)$joueur;
		$prix=((int)$joueur->type==1?PRIX_JOUEUR:PRIX_NONJOUEUR)+(int)$joueur->adulte*PRIX_NONJOUEUR+(int)$joueur->enfant*PRIX_ENFANT;
		$message="<p style=\"$style\">Salut ".utf8_decode($joueur->prenom).",<br />apparemment nous n'avons toujours pas reçu ton paiement pour le Bornes 2  be 3.<br />";
		$message.="Certes, l'argent ne fait pas le bonheur, mais nous avons besoin de ta participation pour que tout soit fin pr&ecirc;t le jour J lors de ton arriv&eacute;e, parce que on a encore plein de trucs et de machins &agrave; acheter.<br />";
		$message.=sprintf("Ce serait cool que tu puisses payer rapidement (avant le 31 juillet si possible) la somme de %s &euro;: </p><ul style=\"$style\">".
		"<li style=\"$style\">par ch&egrave;que, &agrave; l'ordre de l'association Dahultimate, &agrave; envoyer &agrave; :<br />$addrFanch</li>
		<li style=\"$style\">par virement (<a href=\"https://{$_SERVER['HTTP_HOST']}/medias/rib.pdf\" style=\"color:black\">Le rib du club est ici</a>)</li></ul>",$prix);

		$message.="<p style=\"$style\">On compte sur toi !";
		$message.="<br /><strong>Si tu as d&eacute;j&agrave; effectu&eacute; le paiement, ne tiens pas compte de ce mail !</strong>";
		$message.="<br />&Agrave; tr&egrave;s vite, la Dahuteam";
		$message.=sprintf("<br />PS : N'oublie pas que tu peux collaborer &agrave; <a href=\"%s\" style=\"color:black\">la playlist officielle</a>.</p>", $playlistURL);		
		//echo $joueur->id_inscription."=>";
		
		
		
		//  <$mail->sendMail($joueur->email, $message, "Ton paiement au Bornes to be 3",["address"=>"tournoi@dahultimate.com","name"=>"[Bornes to be 3]"]);
		
		$to="chau@plusdeclic.com";
		
		$mail->sendMail($to, $message, "Ton paiement au Bornes to Catch".$joueur->email,["address"=>"tournoi@dahultimate.com","name"=>"[Bornes to be 3]"]);
		
		echo $joueur->email."<br />";
		
		
		//$this->sendConfirmMail($joueur['id_inscription'],"fr");
	
		endforeach;
	
		echo count($query);
	
		die();
	
	
	}
	
	public function listeAction(){
		
		$this->_helper->layout ()->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ( true );
		
		$mapper = new Default_Model_TournoiMapper();
		
		$liste=$mapper->execQuery(" select nom, prenom, email, club from tournoi where confirm=1 order by nom ASC");
		
		$csv="";
		
		$name = tempnam('/tmp', 'csv');
		$handle = fopen($name, 'w');
		
		$header=["Nom", utf8_decode("Prénom"),"Email","Club"];
		fputcsv($handle, $header,';');
		foreach($liste as $joueur):
		
		$joueur=array_map("utf8_decode", (array)$joueur);
		
				
		fputcsv($handle, $joueur,';');
		
		endforeach;
		fclose($handle);
		
		
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=btc-liste-des-joueurs.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile($name);
		
		unlink($name);
		
		
	}
	
	
	public function getfbAction(){
		$fb = new \Facebook\Facebook([
				'app_id' => '1641608522790532',
				'app_secret' => '547517fe5880458a0e8813a9f5211905',
				'default_graph_version' => 'v2.5',
				'default_access_token' => '1641608522790532|547517fe5880458a0e8813a9f5211905', // optional
				]);
		

		
		$this->_helper->layout ()->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ( true );

		$request=$fb->request('GET','/584146118429137/photos?fields=created_time,from,images,likes&limit=1000');
		
		
		try {
			$response = $fb->getClient()->sendRequest($request);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		
		$photos=$response->getGraphEdge()->asArray();
		
		$mapper=new Default_Model_BtcalbumMapper();
		
		$dev=false;
		$arrayZip=[];
		try{
		foreach($photos as $photo):
		
		
		$fbid=$photo['id'];
		
		
		$photo['created_time']->setTimezone(new DateTimeZone('Europe/Paris'));
		
		$date=$photo['created_time']->format('Y-m-d H:i:s');
		$from=$photo['from']['name'];
		$likes=$photo['likes']?count($photo['likes']):0;
		
		$url="http://graph.facebook.com/$fbid/picture?type=normal";
		$url=$photo["images"][0]["source"];
		
		try{
		$test=$mapper->execQuery("select * from BTCalbum where fbID=?", [$fbid], 1);
		}
		
		catch(Exception $e){
			
			var_dump($e);
			
		};
		
		
		
		if($test){
			
			
			$mapper->execQuery("update BTCalbum set likes=? where id_photo=?",[$likes, $test->id_photo],-1);
			
			$id_photo=$test->id_photo;
			
		}
		
		if(!$test||$dev):
		
		
		
		$data=["fbID"=>$fbid, "created_time"=>$date,"from"=>$from,"likes"=>$likes];
		
		
		if(!$test)$id_photo=$mapper->addData($data);
		
		array_push($arrayZip,$id_photo);
		
		
		if(!file_exists(APPLICATION_PATH.'/uploads/btc_'.$fbid.'.jpg')):
		$data = file_get_contents($url);
		
		$fileName = APPLICATION_PATH.'/uploads/btc_'.$fbid.'.jpg';
		$file = fopen($fileName, 'w+');
		fputs($file, $data);
		fclose($file);
		
		
		
		endif;

		endif;
		//var_dump($photo);
		
//		$date = new DateTime($photo['created_time'], new DateTimeZone('Europe/Rome'));
		
		
		
		endforeach;
		$dateZip=new DateTime("now", new DateTimeZone('Europe/Paris'));
		$zipName="btc_".$dateZip->format('Y-m-d-H-i').".zip";
		$zip = new ZipArchive();
		$zip->open(APPLICATION_PATH."/../public/zip/".$zipName, ZipArchive::OVERWRITE);
		
		if(count($arrayZip)>0):
		
		
		
		


		
		foreach($arrayZip as $id):
		
		$data=$mapper->find($id);
		
		$zip->addFile(APPLICATION_PATH."/uploads/btc_".$data->__get('fbID').".jpg",'btc_'.$data->__get('fbID').'.jpg');
			
		$mapper->execQuery("update BTCalbum set zipName=? where id_photo=?",[$zipName,$id], -1);
		
		endforeach;
		
		endif;
		
		$file = tempnam ( "tmp", "json" );
		$jsonName="btc.json";
		
		$jsonFile=fopen($file,"w+");

		
		
		
		
		$jsonTxt=[];
		
		$photosData=$mapper->fetchAll();
		
		
		
		foreach($photosData as $data):
		$data=(object)$data;
		
		array_push($jsonTxt,["fbID"=>$data->fbID,"from"=>$data->from,"date"=>$data->created_time,"likes"=>$data->likes]);
		
		endforeach;
		
		fputs($jsonFile, json_encode($jsonTxt));
		
		$zip->addFile($file,$jsonName);
		fclose($jsonFile);
		
		
		$zip->close();
		
		
		} catch(Exception $e){
			
			var_dump($e->getMessage());
			
		};
		
	}
	
	
	public function uploadbtcAction(){
		
		$this->_helper->layout ()->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ( true );
		
		$mapper=new Default_Model_BtcalbumMapper();
		
		//$zipQuery=$mapper->execQuery("select distinct(zipName) as zipname from BTCalbum");
		
		$dir_handle=opendir(APPLICATION_PATH."/../public/zip/");
		
		while($zip=readdir($dir_handle)) :
		
		if(preg_match('#\.zip#', $zip)):
		
		$q=$mapper->execQuery("select count(id_photo) as num, (select count(uploaded) from BTCalbum where uploaded=1 and zipName=?)  as uploaded from BTCalbum where zipName=?",[$zip, $zip],1);
		
		
				
		
		$size=filesize(APPLICATION_PATH."/../public/zip/".$zip);
		
		$displaySize=$this->getfilesize($size);

		echo '<a href="/prov/getzip?file='.$zip.'">'.$zip." - ".strtolower($displaySize)." - {$q-> num} photos - ".($q->uploaded>0?" t&eacute;l&eacute;charg&eacute;":"")."</a><br />";
		
		
		
		
		endif;
		
		endwhile;
		
		
	}
	
	public function getzipAction(){
		
		$this->_helper->layout ()->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ( true );
		
		$zip = $this->_request->file;
		
		$mapper=new Default_Model_BtcalbumMapper();
		
		$file=APPLICATION_PATH."/../public/zip/$zip";
		
		if(!file_exists($file)) die('fichier pas l&agrave;');
		
		
		header('Content-Type: application/zip');
		header('Content-Length: ' . filesize($file));
		header('Content-Disposition: attachment; filename="'.$zip.'"');
		readfile($file);
		
		
		$mapper->execQuery("update BTCalbum set uploaded=1 where zipName=?",[$zip],-1);
		
		
		
	}
	
	public function getfilesize($bytes, $decimals = 2) {
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}
	
	
}

