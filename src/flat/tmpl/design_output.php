<?php

namespace flat\tmpl;

abstract class design_output implements resolvable_design {
    public function resolvable_design_output(\flat\tmpl\data $data=null): void
    {
        $this->output($data);
    }
    abstract public function output() : void;
}