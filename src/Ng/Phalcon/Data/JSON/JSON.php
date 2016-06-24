<?php
namespace Ng\Phalcon\Data\JSON;


use Ng\Phalcon\Data\DataInterface;

class JSON implements DataInterface
{
    protected $data;
    protected $linked;
    protected $src;

    public function populate()
    {
        if (is_null($this->src) OR empty($this->src)) {
            return;
        }

    }

    public function getPopulated()
    {
        return array(
            "linked"    => $this->linked,
            "data"      => $this->data,
        );
    }

    public function setSource($src)
    {
        $this->src = $src;
    }

}