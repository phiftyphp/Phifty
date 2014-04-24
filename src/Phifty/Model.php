<?php
namespace Phifty;
use LazyRecord\BaseModel;
use ActionKit\RecordAction\BaseRecordAction;

class Model extends BaseModel
{
    public function getCurrentUser()
    {
        return kernel()->currentUser;
    }
}
