<?php
namespace Ng\Phalcon\Data;


use Ng\Phalcon\Data;
use Ng\Phalcon\Model\NgModel;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\User\Component;

class Json extends Component implements Data
{
    use Envelope {envelope as private _envelope;}
    use Relation;

    protected $data = array();
    protected $src;

    public function populate()
    {
        if (is_null($this->src)) {
            return false;
        }

        try {

            $this->envelope();

        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function envelope()
    {
        if ($this->src instanceof Resultset) {
            foreach ($this->src as $src) {
                /** @type NgModel $model */
                $model          = $src;
                $data           = $this->_envelope($model);
                $this->getRelations($data, $model);
                $this->data[]   = $data;
            }
            return;
        }

        /** @type NgModel $model */
        $model      = $this->src;
        $data       = $this->_envelope($model);
        $this->getRelations($data, $model);
        $this->data = $data;
    }

    public function setSource($src)
    {
        if ($src instanceof Resultset OR $src instanceof NgModel) {
            $this->src = $src;
        }

        return $this;
    }

    public function getPopulated()
    {
        $populated = array(
            "data"      => $this->data,
            "linked"    => $this->linked,
        );

        return $populated;
    }

}