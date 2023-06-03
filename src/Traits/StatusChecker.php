<?php

namespace Majeedfahad\Workflower\Traits;

trait StatusChecker
{
    public function __call($name, $arguments)
    {
        if(str_starts_with($name, "is")) {
            return $this->checker($name);
        }

        if(str_starts_with($name, "set")) {
            return $this->setter($name);
        }

        return parent::__call($name, $arguments);
    }

    /**
     * @param $name
     * @return bool
     */
    private function checker($name): bool
    {
        $status = substr($name, 2);
        $status = $this->kebab($status);

        $workflowState = static::workflow()->states()->where("name", $status)->firstOrFail();

        if ($this->status == $workflowState->id) {
            return true;
        }
        return false;
    }
    private function setter($name)
    {
        $status = substr($name, 3);
        $status = $this->kebab($status);

        $workflowState = static::workflow()->states()->where("name", $status)->firstOrFail();

        $this->update(['status' => $workflowState->id]);
        return $this->refresh();
    }

    private function kebab($string): string
    {
        $string = \Str::camel($string);
        return \Str::kebab($string);
    }
}
