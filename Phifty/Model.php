<?php
namespace Phifty;
use Maghead\Runtime\Model;
use ActionKit\RecordAction\BaseRecordAction;

class Model extends BaseModel
{
    public function getCurrentUser()
    {
        return kernel()->currentUser;
    }
}
