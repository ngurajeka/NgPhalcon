<?php
namespace Ng\Phalcon\Data;


use Ng\Phalcon\Model\NgModel;

class Envelope
{

    public function envelope(NgModel $model)
    {
        $data       = array();
        $data["id"] = (int) $model->getId();

        $publicFields   = $model::getPublicFields();
        $modelsMetadata = $model->getModelsMetaData();
        $fields         = $modelsMetadata->getDataTypes($model);

        foreach ($publicFields as $field) {

            if (!isset($fields[$field])) {
                continue;
            }
            
            $func   = sprintf("get%s", ucfirst($field));

            switch ($fields[$field]) {
                case 0:
                    $data[$field] = (int) $model->$func();
                    break;
                default:
                    $data[$field] = $model->$func();
            }

        }

        return $data;
    }

}