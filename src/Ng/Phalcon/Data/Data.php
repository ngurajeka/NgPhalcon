<?php
namespace Ng\Phalcon\Data;


use Ng\Phalcon\Data\JSON\JSON;

class Data
{
    const JSON = "json";

    const INVALIDTYPE = "Invalid Data Type";

    protected $type;
    protected $result;

    public function __construct($type=self::JSON)
    {
        $this->type = $type;
    }

    public function populate($src)
    {
        try {
            switch ($this->type) {
                case self::JSON:
                    $mod = new JSON();
                    $mod->setSource($src);
                    $mod->populate();
                    $this->result = $mod->getPopulated();
                    break;
                default:
                    throw new Exception(self::INVALIDTYPE);
                    break;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getResult()
    {
        return $this->result;
    }

}