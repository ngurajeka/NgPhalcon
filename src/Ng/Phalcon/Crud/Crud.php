<?php
namespace Ng\Phalcon\Crud;


use Ng\Phalcon\Model\NgModel;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;
use Phalcon\Mvc\Model\TransactionInterface as Tx;

trait Crud
{

    /** @type $model NgModel */
    protected $model;
    protected $result;

    protected $bindings;
    protected $conditions;
    protected $pageNumber;
    protected $pageOffset;
    protected $pageOrder;
    protected $pageSize;

    protected $hideDeleted = true;

    protected function create(array $data, Tx &$tx=null) {

        if (!is_null($tx)) {
            $this->model->setTransaction($tx);
        }

        if (!$this->model->create($data)) {

            $errors = array();
            foreach ($this->model->getMessages() as $i => $err) {

                /** @type Message $err */
                $errors[$i] = array(
                    "field"     => $err->getField(),
                    "message"   => $err->getMessage(),
                );

                if ($err->getType() == "PresenceOf") {
                    $errors[$i]["code"] = 404;
                }

            }

            if (method_exists($this, "setCode")) {
                $this->setCode(409);
            }

            if (method_exists($this, "setErrors")) {
                $this->setErrors($errors);
            }

            return false;
        }

        $this->setResult($this->getModel());
        return true;
    }

    protected function read($first=false, Tx &$tx=null) {

        $model      = $this->model;
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
            "limit" => $this->pageSize,
            "offset"=> $this->pageOffset,
            "order" => $this->pageOrder,
        );

        try {
            if ($first) {

                $data = $model::findFirst($param);
                if (method_exists($this, "setResult")) {
                    $this->setResult($data);
                }

                return true;
            }

            $data = $model::find($param);
            if (method_exists($this, "setResult")) {
                $this->setResult($data);
            }

            return true;
        } catch (Model\Exception $e) {

            if (method_exists($this, "setCode")) {
                $this->setCode(409);
            }

            if (method_exists($this, "setErrors")) {
                $errors = array(array("message" => $e->getMessage()));
                $this->setErrors($errors);
            }

            return false;
        }
    }

    protected function update(array $data, Tx &$tx=null) {

        if (!is_null($tx)) {
            $this->model->setTransaction($tx);
        }

        if (!$this->model->update($data)) {

            $errors = array();
            /** @type Message $err */
            foreach ($this->model->getMessages() as $i => $err) {

                $errors[$i] = array(
                    "field"     => $err->getField(),
                    "message"   => $err->getMessage(),
                );

                if ($err->getType() == "PresenceOf") {
                    $errors[$i]["code"] = 404;
                }

            }

            if (method_exists($this, "setCode")) {
                $this->setCode(409);
            }

            if (method_exists($this, "setErrors")) {
                $this->setErrors($errors);
            }

            return false;
        }

        $this->setResult($this->getModel());
        return true;
    }

    protected function delete(Tx &$tx=null) {

        if (!is_null($tx)) {
            $this->model->setTransaction($tx);
        }

        if (!$this->model->delete()) {

            $errors = array();
            /** @type Message $err */
            foreach ($this->model->getMessages() as $i => $err) {

                $errors[$i] = array(
                    "field"     => $err->getField(),
                    "message"   => $err->getMessage(),
                );

                if ($err->getType() == "PresenceOf") {
                    $errors[$i]["code"] = 404;
                }

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

    private function queryDeleted($model)
    {
        $conditions = "";
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
