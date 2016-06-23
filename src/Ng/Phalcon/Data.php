<?php
namespace Ng\Phalcon;


interface Data
{
    public function envelope();

    public function populate();

    public function getPopulated();

    public function setSource($src);

}