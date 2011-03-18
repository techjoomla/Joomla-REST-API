<?php

require_once "HTTP/Request.php";

class RESTClient {

    private $root_url = "";
    private $curr_url = "";
    private $user_name= "";
    private $password = "";
    private $response = "";
    private $responseBody = "";
    private $req = null;

    public function __construct($root_url = "", $user_name = "", $password = "") {
        $this->root_url = $this->curr_url = $root_url;
        $this->user_name = $user_name;
        $this->password = $password;
        if ($root_url != "") {
            $this->createRequest("GET");
            $this->sendRequest();
        }
        return true;
    }

    public function createRequest($url, $method, $arr = null) {
        $this->curr_url = $url;
        $this->req =& new HTTP_Request($url);
        if ($this->user_name != "" && $this->password != "") {
           $this->req->setBasicAuth($this->user_name, $this->password);
        }        

        switch($method) {
            case "GET":
                $this->req->setMethod(HTTP_REQUEST_METHOD_GET);
                break;
            case "POST":
                $this->req->setMethod(HTTP_REQUEST_METHOD_POST);
                $this->addPostData($arr);
                break;
            case "PUT":
                $this->req->setMethod(HTTP_REQUEST_METHOD_PUT);
                // to-do
                break;
            case "DELETE":
                $this->req->setMethod(HTTP_REQUEST_METHOD_DELETE);
                // to-do
                break;
        }
    }

    private function addPostData($arr) {
   
        if ($arr != null) {
            foreach ($arr as $key => $value) {
                $this->req->addPostData($key, $value);
            }
        }
    }

    public function sendRequest() {
       $this->response = $this->req->sendRequest();

        if (PEAR::isError($this->response)) {
            echo $this->response->getMessage();
            //die();
        } else {
            $this->responseBody = $this->req->getResponseBody();
        }
    }

    public function getResponse() {
        
        return $this->responseBody;
    }

}
?>
