<?php
namespace Ng\Phalcon\Request;


trait QueryString
{

    protected function parseQueryString($query)
    {
        file_put_contents("/tmp/query.txt", $query);
    }

}