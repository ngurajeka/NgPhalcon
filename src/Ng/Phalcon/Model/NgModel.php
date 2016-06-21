<?php
namespace Ng\Phalcon\Model;


use Ng\Module\Constants\Error\Error;
use Phalcon\Mvc\Model;

abstract class NgModel extends Model
{

    const DELETED   = 1;
    const ID        = "id";

    public function __call($method, $arguments) {

        $key    = substr($method, 0, 3);
        $field  = lcfirst(substr($method, 3));

        $return = $this;

        if (!in_array($field, get_object_vars($this))) {
            throw new Model\Exception(Error::notFound("Property"));
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

        $properties = get_object_vars($this);
        if (in_array($key, $properties)) {
            return $this->{$key};
        }

        return $default;
    }

    protected static function translateKey($key)
    {
        if (!empty(self::usePrefix()) AND is_string(self::usePrefix())) {
            $key = sprintf("%s%s", self::usePrefix(), ucfirst($key));
        }

        return $key;
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
        return self::DELETED;
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
