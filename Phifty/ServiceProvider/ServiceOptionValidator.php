<?php
namespace Phifty\ServiceProvider;
use Phifty\Kernel;

interface ServiceOptionValidator
{
    public function validateOptions($options = array());
}


