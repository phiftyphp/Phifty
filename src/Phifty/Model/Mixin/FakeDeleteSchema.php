<?php
namespace Phifty\Model\Mixin;
use LazyRecord\Schema\MixinSchemaDeclare;

class FakeDeleteSchema extends MixinSchemaDeclare
{
    public function schema()
    {
        $this->column('is_deleted')
            ->boolean()
            ->default(false)
            ->label('已刪除')
            ->renderAs('CheckboxInput')
            ;
    }
}
