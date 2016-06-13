<?php
namespace Ng\Phalcon\Model;


use Phalcon\Mvc\Model;

abstract class NgModel extends Model {

    public function getId()
    {
        return 0;
    }

    public static function getCreatedFields()
    {
        return array();
    }

    public static function getDeletedFields()
    {
        return array();
    }

    public static function getPrimaryKey()
    {
        return "id";
    }

    public static function getPublicFields()
    {
        return array();
    }

    public static function getRelations()
    {
        return array();
    }

    public static function getUpdatedFields()
    {
        return array();
    }
}