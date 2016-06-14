<?php
namespace Ng\Phalcon\Model;


use Phalcon\Mvc\Model;

abstract class NgModel extends Model
{

    const DELETED   = 1;
    const ID        = "id";

    /**
     * Get Id from Model
     *
     * @return int
     */
    public function getId()
    {
        return 0;
    }

    /**
     * Get Created Fields
     *
     * @return array
     */
    public static function getCreatedFields()
    {
        return array();
    }

    /**
     * Get Deleted Fields
     *
     * @return array
     */
    public static function getDeletedFields()
    {
        return array();
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
        return self::ID;
    }

    /**
     * Get Public Fields
     *
     * @return array
     */
    public static function getPublicFields()
    {
        return array();
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
        return array();
    }

}