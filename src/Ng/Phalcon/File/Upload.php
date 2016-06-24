<?php
namespace Ng\Phalcon\File;


use Phalcon\Http\Request\File as HttpFile;
use Phalcon\Mvc\Model\TransactionInterface as Tx;

use Ng\Modules\Base;
use Ng\Modules\Query\Query;
use Ng\Phalcon\Crud\Crud;

trait Upload
{
    use Base, Crud, Query;

    private function saveRequest(HttpFile $file, Tx &$tx=null)
    {
        return true;
    }

    private function updateRequest($path, Tx &$tx=null)
    {
        return true;
    }

    protected function isChecked()
    {
        return true;
    }

}
