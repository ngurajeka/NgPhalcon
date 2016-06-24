<?php
namespace Ng\Phalcon\Data;


interface DataInterface
{
    public function populate();
    public function getPopulated();
    public function setSource($src);
}