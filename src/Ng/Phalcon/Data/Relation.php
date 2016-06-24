<?php
namespace Ng\Phalcon\Data;


use Phalcon\Mvc\Model\Exception;

use Ng\Phalcon\Model\NgModel;
use Phalcon\Mvc\Model\MetaData;

trait Relation
{

    private $linked     = array();
    private $relations  = array();

    private function belongsTo(array &$data, NgModel $model)
    {

        if (!isset($this->relations["belongsTo"])) {
            return;
        }

        if (!is_array($this->relations["belongsTo"])) {
            return;
        }

        foreach ($this->relations["belongsTo"] as $relation) {

            /** @type \Phalcon\Mvc\Model\Relation $relation */
            $alias      = $relation->getOptions()["alias"];
            $related    = $relation->getFields();
            $ref        = $relation->getReferencedFields();
            $getter     = sprintf("get%s", ucfirst($ref));

            if (!isset($data[$related])) {
                continue;
            }

            try {
                /** @type NgModel $modelRelation */
                $modelRelation = $model->{$alias};
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            if (!$modelRelation instanceof NgModel) {
                continue;
            }

            $dataRelation = $modelRelation->toArray();

            if (!isset($data["links"][$ref])) {
                $data["links"][$ref]    = array();
            }

            if (method_exists($modelRelation, $getter)) {
                $data["links"][$ref][] = (int)$modelRelation->$getter();
            } else if (method_exists($modelRelation, "getId")) {
                $data["links"][$ref][] = (int) $modelRelation->getId();
            } else if (method_exists($modelRelation, "getPrimaryKey")) {
                $key = $modelRelation::getPrimarykey();
                $data["links"][$ref][] = (int) $modelRelation->{$key};
                unset($key);
            } else if (method_exists($this, "getDI")) {

                if ($this->getDI()->getModelsMetadata()) {
                    /** @type MetaData $metadata */
                    $metadata   = $this->getDI()->getModelsMetadata();
                    $key        = $metadata->getPrimaryKeyAttributes($modelRelation);
                    $data["links"][$ref][]  = (int) $modelRelation->{$key[0]};
                    unset($metadata);
                    unset($key);
                } else {
                    $data["links"][$ref][]  = 0;
                }

            } else {
                $data["links"][$ref][]      = 0;
            }

            $this->linked[$ref][]           = $dataRelation;

            unset($data[$ref]);
        }

    }

    private function hasMany(array &$data, NgModel $model)
    {

        if (!isset($this->relations["hasMany"])) {
            return;
        }

        if (!is_array($this->relations["hasMany"])) {
            return;
        }

        foreach ($this->relations["hasMany"] as $relation) {

            /** @type \Phalcon\Mvc\Model\Relation $relation */
            $alias      = $relation->getOptions()["alias"];
            $ref        = $relation->getReferencedFields();

            try {
                /** @type NgModel $modelRelation */
                $modelRelation = $model->{$alias};
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            foreach ($modelRelation as $_model) {

                /** @type NgModel $_model */
                $dataRelation = $_model->toArray();

                if (!isset($data["links"][$ref])) {
                    $data["links"][$ref]    = array();
                }

                if (method_exists($_model, "getId")) {
                    $data["links"][$ref][] = (int) $_model->getId();
                } else if (method_exists($_model, "getPrimaryKey")) {
                    $key = $_model::getPrimarykey();
                    $data["links"][$ref][] = (int) $_model->{$key};
                    unset($key);
                } else if (method_exists($this, "getDI")) {
                    if ($this->getDI()->getModelsMetadata()) {
                        /** @type MetaData $metadata */
                        $metadata   = $this->getDI()->getModelsMetadata();
                        $key        = $metadata->getPrimaryKeyAttributes($_model);
                        $data["links"][$ref][]  = (int) $_model->{$key[0]};
                        unset($metadata);
                        unset($key);
                    } else {
                        $data["links"][$ref][]  = 0;
                    }
                } else {
                    $data["links"][$ref][]      = 0;
                }

                if (!isset($this->linked[$ref]))
                    $this->linked[$ref]         = array();

                if (!in_array($dataRelation, $this->linked[$ref]))
                    $this->linked[$ref][]       = $dataRelation;

            }

            unset($data[$ref]);
        }

    }

    private function getRelations(array &$data, NgModel $model)
    {
        $this->getRelationsOptions($model);
        $this->belongsTo($data, $model);
        $this->hasMany($data, $model);
        return true;
    }

    private function getRelationsOptions(NgModel $model)
    {
        $modelsManager = $model->getModelsManager();

        $this->relations["belongsTo"]   = $modelsManager->getBelongsTo($model);
        $this->relations["hasMany"]     = $modelsManager->getHasMany($model);
    }

}