<?php
namespace Ng\Phalcon\Crud;


trait Envelope
{

    private function envelope($model, $schema)
    {
        $convert    = array("integer", "tinyint");
        $data       = array();
        foreach ($schema["public_fields"] as $field => $opt) {

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