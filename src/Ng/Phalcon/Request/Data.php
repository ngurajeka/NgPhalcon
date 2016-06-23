<?php
namespace Ng\Phalcon\Request;


use Phalcon\Http\Request;

use Ng\Modules\Constants\Http\Header;

trait Data
{
    protected $requestedData = array();

    protected function parseRequestedData(Request $request)
    {
        switch ($request->getContentType()) {
        case Header::JSON:
            $this->parseJson($request);
            break;
        default:
            $this->parseForm($request);
            break;
        }
    }

    private function parseJson(Request $request)
    {
        $post = $request->getJsonRawBody();
        if (!isset($post->data)) {
            return;
        }

        $this->requestedData = json_decode(json_encode($post), true);
    }

    private function parseForm(Request $request)
    {
        $post = $request->getPost();
        $this->requestedData = array("data" => $post);
    }

    public function getRequestedData()
    {
        return $this->requestedData;
    }
}
