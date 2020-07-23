<?php
namespace Nish\Utils;


class Pipe
{
    protected $funcList = [];

    public function __toString(): string
    {
        return print_r($this->funcList, true);
    }

    public function push(callable $func, bool $receivePrevResult = true, array $extraParams = [])
    {
        $this->funcList[] = [
            'func' => $func,
            'receivePrevRes' => $receivePrevResult,
            'extraParams' => $extraParams
        ];
    }

    public function unload()
    {
        $this->funcList = [];
    }

    public function isEmpty()
    {
        return empty($this->funcList);
    }

    public function flush(array $startParams = null)
    {
        $result = $startParams;

        if (!$this->isEmpty()) {
            foreach ($this->funcList as $runnableObj) {
                $result = $this->runFunc($runnableObj, $result);
            }
        }

        return $result;
    }

    private function runFunc(array $runnableObj, $previousResult)
    {
        $params = [];
        if ($runnableObj['receivePrevRes']) {
            if (!empty($previousResult)) {
                if (is_array($previousResult)) {
                    $params = $previousResult;
                } else {
                    $params = [$previousResult];
                }
            }
        }

        if (!empty($runnableObj['extraParams'])) {
            foreach ($runnableObj['extraParams'] as $i => $param) {
                if (is_callable($param)) {
                    $runnableObj['extraParams'][$i] = call_user_func($param);
                }
            }

            $params = array_merge($params, $runnableObj['extraParams']);
        }

        return call_user_func_array($runnableObj['func'], $params);
    }
}