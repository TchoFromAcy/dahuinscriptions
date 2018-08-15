<?php

class RestController extends Zend_Controller_Action
{

    protected $mapper;
    protected $type = null;
    protected $item_id;


    public function preDispatch()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        parent::preDispatch(); // TODO: Change the autogenerated stub
	    if($_SERVER['REMOTE_ADDR']!=='77.150.250.93'&&$_COOKIE['admin']!==sha1("dadahu".strftime('%Y%m%d', time())))return $this->sendJSONResponse(false, ['error'=>'vous devez être connecté']);

    }


    public function indexAction()
    {

    }

    /**
     * @return \Zend\Diactoros\Response\SapiEmitter
     */
    public function getTeams()
    {


        $mapper = new Default_Model_B2cTeamsMapper();

        $results = $mapper->fetchAll();



        return $this->sendJSONResponse(true, $results);

    }

    /**
     * @return \Zend\Diactoros\Response\SapiEmitter
     */
    public function getAllUsers()
    {


        $usersMapper = new Default_Model_TournoiMapper();

//        $sql = "select * from " . $usersMapper->getDbTable()->__get('_name') . " where update_date like '%" . strftime('%Y') . "%'"; //
        $results = $usersMapper->fetchAll("update_date like '%" . strftime('%Y') . "%' and confirm>0");


	/*    $results=array_map(function($a){

		    $a->nom=utf8_encode($a->nom);
		    $a->prenom=utf8_decode($a->prenom);
		    $a->club=utf8_decode($a->club);

		    return $a;


	    }, $results);
	*/




        return $this->sendJSONResponse(true, $results);


    }

    public function dorequestAction()
    {


        $this->setType();
        $this->setItemId();


        if ($this->getRequest()->isPut()) {


            $mapper = $this->getMapper($this->getType());

            $id = preg_replace('#\.json$#', '', $this->getItemId());

            $model = $mapper->find($id);


            if (!$model) $this->sendJSONResponse(false, ['error' => 'Impossible de récupérer l\'enregistrement avec l\'id ' . $id]);

            $this->addData($model);

            $this->sendJSONResponse(true);

        } // add Action
        else if ($this->getRequest()->isPost() && $this->getItemId() === 0) {

            $mapper = $this->getMapper($this->getType());
            $model = $mapper->getModel();
           $id= $this->addData($model);

            $this->sendJSONResponse(true,[$this->getMapper()->getDbTable()->__get('_primary')=>$id]);

        }
        else if ($this->getRequest()->isDelete()) {

            $mapper = $this->getMapper($this->getType());

            $id = preg_replace('#\.json$#', '', $this->getItemId());

             if($mapper->find($id))$mapper->delete($id);

            $this->sendJSONResponse(true);

        }
        else if(isset($_GET['APICall'])){

            $APICall = $this->getRequest()->getParam('APICall');

            if (!method_exists($this, $APICall)) return $this->sendJSONResponse(false, ['error' => 'Appel inconnu']);


            $this->{$APICall}(); $APICall = $this->getRequest()->getParam('APICall');

            if (!method_exists($this, $APICall)) return $this->sendJSONResponse(false, ['error' => 'Appel inconnu']);


            $this->{$APICall}();


        }

    }

    public function getPoules()
    {

        $mapper = new Default_Model_B2cPouleMapper();

        $results = $mapper->fetchAll();
        return $this->sendJSONResponse(true, $results);


    }

    /**
     * @param bool $success
     * @param array $results
     */
    private function sendJSONResponse($success = true, array $results = []):Zend\Diactoros\Response\SapiEmitter
    {

        $data = ['success' => $success, 'data' => $results];

        $response = new \Zend\Diactoros\Response\JsonResponse($data);

        $emitter = new Zend\Diactoros\Response\SapiEmitter();
        $emitter->emit($response);
        die();


    }

    /**
     * returns json decoded rawBody
     * @return mixed|void
     * @throws Zend_Json_Exception
     */
    protected function getRawBody()
    {


        $body = $this->getRequest()->getRawBody();
        if (!$body) return $this->sendJSONResponse(false, ['error' => 'Erreur d\'entête']);

        $postData = Zend_Json::decode($body);

        return $postData;


    }

    /**
     * @param Default_Model_Model $model
     * @return int
     * @throws Zend_Json_Exception
     */
    protected function addData(Default_Model_Model $model){

        $primary = $this->getMapper()->getDbTable()->__get('_primary');


        $cols = $model->getFields();
        $data = $model->dataToArray();
        $postData = $this->getRawBody();

        foreach ($postData as $key => $value) {

            if ($key == $primary || !in_array($key, $cols)) continue;
            $data[$key] = $value;

        }

       $id= $this->getMapper()->addData($data);

        return $id;


    }

    /**
     * @param string $type
     * @return Default_Model_Mapper
     */
    public function getMapper(): Default_Model_Mapper
    {


        if (!$this->mapper) return $this->setMapper();
        return $this->mapper;


    }

    /**
     * @return Default_Model_Mapper
     */
    public function setMapper(): Default_Model_Mapper
    {

        $mapperName = 'Default_Model_' . ucfirst($this->getType()) . 'Mapper';
        if (!class_exists($mapperName)) return $this->sendJSONResponse(false, ['error' => 'classe Inconnue ' . $mapperName]);

        $this->mapper = new $mapperName();
        return $this->getMapper();

    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(): void
    {
        $this->type = $this->_request->getParam('type');
    }

    /**
     * @return int
     */
    public function getItemId():int
    {
        return $this->item_id;
    }

    /**
     * @param mixed $item_id
     */
    public function setItemId(): void
    {
        $this->item_id = (int)$this->_request->getParam('item_id');
    }


}

?>
