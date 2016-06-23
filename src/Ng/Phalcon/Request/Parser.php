<?php
namespace Ng\Phalcon\Request;


use Ng\Modules\Constants\Http\Methods;
use Phalcon\Http\Request;

trait Parser
{
    use Data, QueryString;

    protected $data;

    protected function parse(Request $request)
    {
        switch ($request->getMethod()) {
            case Methods::GET:
                $this->parseQueryString($request->getQuery());
                break;
            case Methods::POST:
            case Methods::PATCH:
            case Methods::PUT:
                $this->parseRequestedData($request);
                break;
        }
    }

}