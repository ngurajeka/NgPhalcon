<?php
namespace Ng\Phalcon\Crud;


use Ng\Modules\Base;
use Ng\Modules\Errors\Error\Error;
use Ng\Phalcon\Model\NgModel;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;
use Phalcon\Mvc\Model\TransactionInterface as Tx;

class Crud extends Base
{

    /** @type $model NgModel */
    protected $model;
    protected $result;

    protected $bindings;
    protected $conditions;
    protected $groupBy;
    protected $pageNumber   = 1;
    protected $pageOffset   = 0;
    protected $pageOrder;
    protected $pageSize     = 10;

    protected $hideDeleted = true;

    public function __construct(NgModel $model)
    {
        parent::__construct();
        $this->model    = $model;
    }

    public function create(array $data, Tx &$tx=null) {

        if (!is_null($tx)) {
            $this->model->setTransaction($tx);
        }

        if (!$this->model->create($data)) {

            foreach ($this->model->getMessages() as $i => $err) {

                /** @type Message $err */
                $field  = $err->getField();
                $msg    = $err->getMessage();
                $src    = $err->getModel();

                if ($err->getType() == "PresenceOf") {
                    $error = Error::notFound($msg, $field, $src);
                } else {
                    $error = Error::populate("409", $msg, $field, null, $src);
                }

                $this->errors->append($error);
                unset($error);

            }

            return false;
        }

        $this->setResult($this->getModel());
        return true;
    }

    public function read($first=false, Tx &$tx=null) {

        $model      = $this->getModel();
        if (empty($model) OR is_null($model)) {
            return false;
        }

        if (!is_null($tx)) {
            $model->setTransaction($tx);
        }

        if ($this->hideDeleted) {
            $this->queryDeleted($model);
        }

        if (!isset($this->pageOrder) or is_null($this->pageOrder)) {
            if (method_exists($model, "getPrimaryKey")) {
                $this->pageOrder = sprintf("%s DESC", $model::getPrimaryKey());
            }
        }

        $param = array(
            $this->conditions,
            "limit" => $this->getPageSize(),
            "offset"=> $this->getPageOffset(),
            "order" => $this->getPageOrder(),
        );

        try {
            if ($first) {

                $data = $model::findFirst($param);
                $this->setResult($data);

                return true;
            }

            $data = $model::find($param);
            $this->setResult($data);

            return true;
        } catch (Model\Exception $e) {

            $msg    = $e->getMessage();
            $src    = $e->getTraceAsString();
            $error = Error::populate("409", $msg, null, null, $src);
            $this->errors->append($error);

            unset($error);
            unset($msg);
            unset($src);

            return false;
        }
    }

    public function update(array $data, Tx &$tx=null) {

        if (!is_null($tx)) {
            $this->model->setTransaction($tx);
        }

        if (!$this->model->update($data)) {

            /** @type Message $err */
            foreach ($this->model->getMessages() as $i => $err) {

                /** @type Message $err */
                $field  = $err->getField();
                $msg    = $err->getMessage();
                $src    = $err->getModel();

                if ($err->getType() == "PresenceOf") {
                    $error = Error::notFound($msg, $field, $src);
                } else {
                    $error = Error::populate("409", $msg, $field, null, $src);
                }

                $this->errors->append($error);
                unset($error);

            }

            return false;
        }

        $this->setResult($this->getModel());
        return true;
    }

    public function delete(Tx &$tx=null) {

        if (!is_null($tx)) {
            $this->model->setTransaction($tx);
        }

        if (!$this->model->delete()) {

            $errors = array();
            /** @type Message $err */
            foreach ($this->model->getMessages() as $i => $err) {

                /** @type Message $err */
                $field  = $err->getField();
                $msg    = $err->getMessage();
                $src    = $err->getModel();

                if ($err->getType() == "PresenceOf") {
                    $error = Error::notFound($msg, $field, $src);
                } else {
                    $error = Error::populate("409", $msg, $field, null, $src);
                }

                $this->errors->append($error);
                unset($error);

            }

            if (method_exists($this, "setCode")) {
                $this->setCode(409);
            }

            if (method_exists($this, "setErrors")) {
                $this->setErrors($errors);
            }

            return false;
        }

        return true;
    }

    public function groupCount(Tx &$tx=null)
    {
        $model = $this->model;
        if (!is_null($tx)) {
            $model->setTransaction($tx);
        }

        if ($this->hideDeleted) {
            $this->queryDeleted($model);
        }

        $param = array(
            "conditions"    => $this->conditions,
            "group"         => $this->groupBy,
        );

        return $model::count($param);
    }

    private function queryDeleted($model)
    {
        if (!method_exists($model, "getDeletedFields")) {
            return;
        }

        $field      = $model::getDeletedFields();
        if (!isset($field["deleted"])) {
            return;
        }

        $deleted    = $field["deleted"];
        if (!is_string($deleted)) {
            return;
        }

        $opt        = 0;
        if (method_exists($model, "getDeletedOptions")) {
            $opt    = $model::getDeletedOptions();
        }

        $q = sprintf('(%1$s = \'%2$s\' OR %1$s IS NULL)', $deleted, $opt);
        if (!empty($this->conditions)) {
            $this->conditions = sprintf("%s AND %s", $this->conditions, $q);
            return;
        }

        $this->conditions = $q;
    }

    /**
     * @return NgModel
     */
    public function getModel() {

        return $this->model;
    }

    /**
     * @param NgModel $model
     *
     * @return Crud
     */
    public function setModel(NgModel $model) {

        $this->model = $model;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isHideDeleted() {

        return $this->hideDeleted;
    }

    /**
     * @param boolean $hideDeleted
     *
     * @return Crud
     */
    public function setHideDeleted($hideDeleted) {

        $this->hideDeleted = $hideDeleted;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult() {

        return $this->result;
    }

    /**
     * @param mixed $result
     *
     * @return Crud
     */
    public function setResult($result) {

        $this->result = $result;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBindings() {

        return $this->bindings;
    }

    /**
     * @param mixed $bindings
     *
     * @return Crud
     */
    public function setBindings($bindings) {

        $this->bindings = $bindings;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConditions() {

        return $this->conditions;
    }

    /**
     * @param mixed $conditions
     *
     * @return Crud
     */
    public function setConditions($conditions) {

        $this->conditions = $conditions;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroupBy() {

        return $this->groupBy;
    }

    /**
     * @param mixed $groupBy
     *
     * @return Crud
     */
    public function setGroupBy($groupBy) {

        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageNumber() {

        return $this->pageNumber;
    }

    /**
     * @param mixed $pageNumber
     *
     * @return Crud
     */
    public function setPageNumber($pageNumber) {

        $this->pageNumber = $pageNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageOffset() {

        return $this->pageOffset;
    }

    /**
     * @param mixed $pageOffset
     *
     * @return Crud
     */
    public function setPageOffset($pageOffset) {

        $this->pageOffset = $pageOffset;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageOrder() {

        return $this->pageOrder;
    }

    /**
     * @param mixed $pageOrder
     *
     * @return Crud
     */
    public function setPageOrder($pageOrder) {

        $this->pageOrder = $pageOrder;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageSize() {

        return $this->pageSize;
    }

    /**
     * @param mixed $pageSize
     *
     * @return Crud
     */
    public function setPageSize($pageSize) {

        $this->pageSize = $pageSize;
        return $this;
    }

}
