<?php
namespace Ng\Phalcon\Crud;


use Ng\Phalcon\Model\NgModel;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Mvc\Model;

trait Relation
{

    private $linked     = array();
    private $relations  = array();

    private function belongsTo(array &$data, Model $model)
    {

        if (!isset($this->relations["belongsTo"])) {
            return;
        }

        if (!is_array($this->relations["belongsTo"])) {
            return;
        }

        foreach ($this->relations["belongsTo"] as $relation) {

            if (!isset($relation["model"])
                or !isset($relation["alias"])
                or !isset($relation["related"])
                or !isset($relation["related_class_field"])) {

                continue;
            }

            $alias      = $relation["alias"];
            $related    = $relation["related"];
            $ref        = $relation["related_class_field"];
            $getter     = sprintf("get%s", ucfirst($ref));

            if (!isset($data[$related])) {
                continue;
            }

            try {
                /** @type Model $modelRelation */
                $modelRelation = $model->{$alias};
            } catch (Model\Exception $e) {
                continue;
            }

            if (method_exists($this, "envelope")) {
                $dataRelation = $this->envelope($modelRelation);
            } else {
                $dataRelation = $modelRelation->toArray();
            }

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
                    /** @type Model\MetaData $metadata */
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

            $this->linked[$ref] = $dataRelation;

            unset($data[$ref]);
        }

    }

    private function hasMany(array &$data, Model $model)
    {

        if (!isset($this->relations["hasMany"])) {
            return;
        }

        if (!is_array($this->relations["hasMany"])) {
            return;
        }

        foreach ($this->relations["hasMany"] as $relation) {

            if (!isset($relation["model"])
                or !isset($relation["alias"])
                or !isset($relation["related_class_field"])) {

                continue;
            }

            $alias      = $relation["alias"];
            $ref        = $relation["related_class_field"];

            try {
                /** @type Model $modelRelation */
                $modelRelation = $model->{$alias};
            } catch (Model\Exception $e) {
                continue;
            }

            foreach ($modelRelation as $_model) {

                /** @type Model $_model */
                if (method_exists($this, "envelope")) {
                    $dataRelation = $this->envelope($_model);
                } else {
                    $dataRelation = $_model->toArray();
                }

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
                        /** @type Model\MetaData $metadata */
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

    private function getRelations(array &$data, Model $model)
    {
        if (!isset($this->relations)) {
            if (!$this->getRelationsOptions($model)) {
                return false;
            }
        }

        $this->belongsTo($data, $model);
        $this->hasMany($data, $model);
        return true;
    }

    private function getRelationsOptions(Model $model)
    {

        if (!method_exists($model, "getRelations")) {
            return false;
        }

        /** @type NgModel $model */
        /** @type Relation $this */
        $this->relations = $model::getRelations();

        return true;
    }

}