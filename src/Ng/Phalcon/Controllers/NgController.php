<?php
namespace Ng\Phalcon\Controller;


use Phalcon\Mvc\Controller;

use Ng\Module\Constants\Http\Header;
use Ng\Module\Constants\Http\Methods;
use Ng\Module\Constants\Http\Status;

abstract class NgController extends Controller
{

    protected $hasSetup = false;

    protected function setup()
    {
        if ($hasSetup) {
            return;
        }

        $this->response->setHeader(Header::CONTENT_TYPE, Header::APPJSON);
        $this->response->setStatusCode(Status::OK, Status::OK_MSG);
        $content = array("status" => Status::OK, "message" => Status::OK_MSG);
        $this->response->setJsonContent($content);

        $this->response->setHeader(Header::ACAO, Header::ORIGIN);
        $this->response->setHeader(Header::ACAM, Methods::METHODS);
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
        $this->response->setJsonContent($content);
        return $this->response;
    }

    public function jsoncode($code, $msg, array $content)
    {
        $this->setStatusCode($code, $msg);
        $this->setJsonContent($content);
        return $this->response;
    }

    public function jsonerror($msg)
    {
        $content = array("status" => array("error" => array("message" => $msg)));
        $this->setStatusCode(Status::CONFLICT, Status::CONFLICT_MSG);
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
