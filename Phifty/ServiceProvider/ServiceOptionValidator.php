<?php

namespace Phifty\ServiceProvider;

interface ServiceOptionValidator
{
    public function validateOptions($options = array());
}
