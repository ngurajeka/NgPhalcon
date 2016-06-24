<?php
namespace Ng\Phalcon\Controllers;


use Ng\Phalcon\Data\JSON\JSON;
use Phalcon\Mvc\Model\Transaction\Exception as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager;
use Phalcon\Mvc\Model\TransactionInterface as Tx;

use Titu\Modules\Modules;

use Ng\Modules\Constants\Http\Methods;
use Ng\Modules\Constants\Http\Status;
use Ng\Phalcon\Request\Parser;

abstract class CrudController extends NgController
{
    use Parser;

    protected $modelNamespace;
    protected $registry;
    protected $useTx = true;

    public function crudAction($model, $id=null)
    {
        // check model exist / not
        $model = $this->translateModel($model);
        if (!class_exists($model)) {
            $msg = sprintf("Resource Model Was %s", Status::NOTFOUND_MSG);
            return $this->jsonError(Status::NOTFOUND, $msg);
        }

        // call module registry (could be schema / constant)
        $this->loadRegistry();

        if (!isset($this->registry["models"])) {
            return $this->jsonError(Status::NOTFOUND, "Registry Was Not Found");
        }

        if (!isset($this->registry["models"][$model])) {
            $msg = sprintf("Resouce %s Was %s", $model, Status::NOTFOUND_MSG);
            return $this->jsonError(Status::NOTFOUND, $msg);
        }

        $module = $this->registry["models"][$model]["module"];
        if (!class_exists($module)) {
            $msg = sprintf("Module %s Was Not Found", $module);
            return $this->jsonError(Status::NOTFOUND, $msg);
        }

        // switch http method
        switch ($this->request->getMethod()) {
            case Methods::GET:
                return $this->get($module, $id);
                break;
            case Methods::POST:
                return $this->post($module);
                break;
            case Methods::PUT:
                return $this->put($module, $model, $id);
                break;
            case Methods::PATCH:
                return $this->patch($module, $model, $id);
                break;
            case Methods::DEL:
                return $this->del($module, $model, $id);
                break;
            default:
                return $this->jsonError(
                    Status::METHODNOTALLOWED, Status::METHODNOTALLOWED_MSG
                );
                break;
        }
    }

    private function get($module, $id=null)
    {
        /** @type Modules $module */
        $module = new $module();
        try {

            if (!$module->read($id)) {
                $msg = Status::CONFLICT_MSG;
                throw new \Exception($msg);
            }

        } catch (\Exception $e) {
            return $this->jsonErrors(
                Status::CONFLICT, $e->getMessage(), $module->getErrors()->toArray()
            );
        }

        // parse request (query string)
        $json = new JSON();
        $json->setSource($module->getResult());
        $json->populate();
        return $this->jsonCode(Status::OK, Status::OK_MSG, $json->getPopulated());
    }

    private function post($module)
    {
        /** @type Modules $module */
        $module = new $module();

        // parse request (raw/json/post)
        $this->parse($this->request);
        $data   = $this->getRequestedData();

        // check transaction
        $tx = null;
        if ($this->useTx) {
            $manager    = new Manager();
            /** @type Tx $tx */
            $tx         = $manager->get();
        }

        try {

            if (!$module->create($data, $tx)) {
                if (!is_null($tx)) {
                    $tx->rollback(Status::CONFLICT_MSG);
                } else {
                    throw new TxFailed(Status::CONFLICT_MSG);
                }
            }

            if (!is_null($tx)) {
                $tx->commit();
            }

        } catch (TxFailed $e) {
            return $this->jsonErrors(
                Status::CONFLICT, $e->getMessage(), $module->getErrors()
            );
        } catch (\Exception $e) {
            return $this->jsonErrors(
                Status::CONFLICT, $e->getMessage(), $module->getErrors()
            );
        }

        $json = new JSON();
        $json->setSource($module->getResult());
        $json->populate();
        return $this->jsonCode(
            Status::CREATED, Status::CREATED_MSG, $json->getPopulated()
        );
    }

    private function put($module, $model, $id=null)
    {
        // parse request (raw/json/post)
        return $this->jsonError(
            Status::NOTIMPLEMENTED, Status::NOTIMPLEMENTED_MSG
        );
    }

    private function patch($module, $model, $id=null)
    {
        // parse request (raw/json/post)
        return $this->jsonError(
            Status::NOTIMPLEMENTED, Status::NOTIMPLEMENTED_MSG
        );
    }

    private function del($module, $model, $id=null)
    {
        return $this->jsonError(
            Status::NOTIMPLEMENTED, Status::NOTIMPLEMENTED_MSG
        );
    }

    protected function loadRegistry()
    {
        $this->registry = array();
    }

    private function translateModel($model)
    {
        $model = explode("_", $model);
        array_walk($model, function(&$w) { $w = ucfirst($w); });
        $model = join("", $model);

        if (is_string($this->modelNamespace) AND !empty($this->modelNamespace)) {
            $len            = strlen($this->modelNamespace);
            $namespace      = $this->modelNamespace;
            if ($namespace[$len-1] != "\\") {
                $namespace  = sprintf("%s\\", $namespace);
            }
            $model = sprintf("%s%s", $namespace, $model);
        }

        return $model;
    }

}
