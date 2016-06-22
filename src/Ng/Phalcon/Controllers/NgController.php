<?php
namespace Ng\Phalcon\Controllers;


use Phalcon\Mvc\Controller;

use Ng\Modules\Constants\Http\Header;
use Ng\Modules\Constants\Http\Methods;
use Ng\Modules\Constants\Http\Status;

abstract class NgController extends Controller
{

    protected $hasSetup = false;

    protected function setup()
    {
        if ($this->hasSetup) {
            return;
        }

        $this->response->setHeader(Header::CONTENT_TYPE, Header::APPJSON);
        $this->response->setStatusCode(Status::OK, Status::OK_MSG);
        $content = array("status" => Status::OK, "message" => Status::OK_MSG);
        $this->response->setJsonContent($content);

        $this->response->setHeader(Header::ACAO, Header::ORIGIN);
        $this->response->setHeader(Header::ACAM, Methods::getMethods(true));
        $this->response->setHeader(Header::ACAH, Header::ACAHV);
        $this->response->setHeader(Header::ACAC, 'true');

        $this->hasSetup = true;
    }

    public function isOptions()
    {
        $this->setup();
        $this->response->send();
        return false;
    }

    public function beforeExecuteRoute()
    {
        if ($this->request->isOptions()) {
            $this->isOptions();
            return false;
        }

        return true;
    }

    public function initialize()
    {
        $this->setup();
    }

    public function json(array $content)
    {
        $this->response->setHeader(Header::CONTENT_TYPE, Header::APPJSON);
        $this->response->setJsonContent($content);
        return $this->response;
    }

    public function jsonCode($code, $msg, array $content)
    {
        $this->response->setHeader(Header::CONTENT_TYPE, Header::APPJSON);
        $this->response->setStatusCode($code, $msg);
        $this->response->setJsonContent($content);
        return $this->response;
    }

    public function jsonConflict($msg)
    {
        $content = array("status" => array("error" => array("message" => $msg)));
        $this->response->setHeader(Header::CONTENT_TYPE, Header::APPJSON);
        $this->response->setStatusCode(Status::CONFLICT, Status::CONFLICT_MSG);
        $this->response->setJsonContent($content);
        return $this->response;
    }

    public function jsonError($code, $msg)
    {
        $content = array("status" => array("error" => array("message" => $msg)));
        $this->response->setHeader(Header::CONTENT_TYPE, Header::APPJSON);
        $this->response->setStatusCode($code, $msg);
        $this->response->setJsonContent($content);
        return $this->response;
    }

    public function jsonErrors($code, $msg, $errors)
    {
        $content = array("status" => array("error" => array("message" => $msg)));
        $content["status"]["error"]["errors"] = $errors;
        $this->response->setHeader(Header::CONTENT_TYPE, Header::APPJSON);
        $this->response->setStatusCode($code, $msg);
        $this->response->setJsonContent($content);
        return $this->response;
    }

    protected function isMethodAllowed(array $methods=null)
    {
        if (is_null($methods)) {
            return true;
        }

        $method = $this->request->getMethod();
        if (in_array($method, $methods)) {
            return true;
        }

        return false;
    }

}
