<?php

namespace Phifty\View;

/*
 * A Generic Action View Generator
 *
 *    $action = Phifty\View\Action
 *
 * */
class Action
{
    public $action;

    public function __construct( $action )
    {
        $this->action = $action;
    }

    public function render()
    {
        echo $this->action->getName();

    }
}
