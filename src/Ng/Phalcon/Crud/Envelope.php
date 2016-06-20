<?php
namespace Ng\Phalcon\Crud;


trait Envelope
{

    private function envelope($model, $fields, $publicFields)
    {
        $convert    = array("integer", "tinyint");
        $data       = array();
        foreach ($publicFields as $field) {

            if (!isset($fields[$field])) {
                $data[$field] = null;
                continue;
            }

            $opt = $fields[$field];

            if (!isset($model->{$field})) {
                $data[$field] = null;
                continue;
            }

            if (in_array($opt["dataType"], $convert)) {
                $data[$field] = (int) $model->{$field};
                continue;
            }

            $data[$field] = $model->{$field};

        }

        return $data;
    }

}