<?php
namespace Ng\Phalcon\Model;


use Ng\Modules\Constants\Errors\Errors;
use Phalcon\Mvc\Model;

abstract class NgModel extends Model
{

    const DELETED       = 1;
    const NOTDELETED    = 0;
    const ID            = "id";

    public function __call($method, $arguments) {

        $key    = substr($method, 0, 3);
        $field  = lcfirst(substr($method, 3));

        $return = $this;

        if (!in_array($field, get_object_vars($this))) {
            throw new Model\Exception(Errors::notFound("Property"));
        }

        switch ($key) {
            case "get":
                $return         = $this->{$field};
                break;
            case "set":
                $this->{$field} = $arguments[0];
                break;
        }

        return $return;
    }

    protected function implementSoftDelete()
    {
        return true;
    }

    protected static function usePrefix()
    {
        return "";
    }

    protected function ifExist($key, $default, $translate=true)
    {
        if ($translate) {
            $key = self::translateKey($key);
        }

        $model      = get_called_class();
        $properties = get_object_vars(new $model());
        if (in_array($key, $properties)) {
            return $this->{$key};
        }

        return $default;
    }

    protected static function translateKey($key)
    {
        /** @type NgModel $called */
        $called = get_called_class();
        if (!empty($called::usePrefix()) AND is_string($called::usePrefix())) {
            $key = sprintf("%s%s", $called::usePrefix(), ucfirst($key));
        }

        return $key;
    }

    protected function updateExist($key, $value, $force=true)
    {
        $model      = get_called_class();
        $properties = get_object_vars(new $model());
        if (in_array($key, $properties)) {
            if ($force) {
                $this->{$key} = $value;
            } else if (!isset($this->{$key})) {
                $this->{$key} = $value;
            }
        }
    }

    public function beforeCreate()
    {
        // created fields
        if (isset(self::getCreatedFields()["at"])) {
            $field = self::getCreatedFields()["at"];
            $this->updateExist($field, date("Y-m-d H:i:s"), false);
        }

        // deleted fields
        if (isset(self::getDeletedFields()["deleted"])) {
            $field = self::getDeletedFields()["deleted"];
            $this->updateExist($field, self::NOTDELETED, false);
        }

    }

    public function beforeValidationOnCreate()
    {
        $this->beforeCreate();
    }

    public function beforeSave()
    {
        $this->beforeCreate();

        // updated fields
        if (isset(self::getUpdatedFields()["deleted"])) {
            $field = self::getUpdatedFields()["deleted"];
            $this->updateExist($field, date("Y-m-d H:i:s"), false);
        }
    }

    public function beforeUpdate()
    {
        $this->beforeCreate();

        // updated fields
        if (isset(self::getUpdatedFields()["deleted"])) {
            $field = self::getUpdatedFields()["deleted"];
            $this->updateExist($field, date("Y-m-d H:i:s"), false);
        }
    }

    public function beforeValidationOnUpdate()
    {
        $this->beforeCreate();
    }

    /**
     * Get Id from Model
     *
     * @return int
     */
    public function getId()
    {
        return $this->ifExist(self::ID, 0);
    }

    /**
     * Get Created Fields
     *
     * @return array
     */
    public static function getCreatedFields()
    {
        return array("at" => self::translateKey("createdTime"));
    }

    /**
     * Get Deleted Fields
     *
     * @return array
     */
    public static function getDeletedFields()
    {
        return array(
            "deleted"   => self::translateKey("deleted"),
            "at"        => self::translateKey("deletedTime"),
        );
    }

    /**
     * Get Deleted Options. ( 'D' / 1 )
     *
     * @return string
     */
    public static function getDeletedOptions()
    {
        return self::NOTDELETED;
    }

    /**
     * Get Primary Key
     *
     * @return string
     */
    public static function getPrimaryKey()
    {
        return self::translateKey(self::ID);
    }

    /**
     * Get Public Fields
     *
     * @return array
     */
    public static function getPublicFields()
    {
        return array(self::translateKey(self::ID));
    }

    /**
     * Get Relations. Return key=>value. Key is relation type
     *
     * @return array
     */
    public static function getRelations()
    {
        return array();
    }

    /**
     * Get Updated Fields
     *
     * @return array
     */
    public static function getUpdatedFields()
    {
        return array("at" => self::translateKey("updatedTime"));
    }

}
