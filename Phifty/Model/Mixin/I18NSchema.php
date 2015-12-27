<?php
namespace Phifty\Model\Mixin;
use LazyRecord\Schema\MixinSchemaDeclare;

class I18NSchema extends MixinSchemaDeclare
{
    public function schema()
    {
        $this->column('lang')
            ->varchar(12)
            ->validValues(function() {
                return array_flip( kernel()->locale->available() );
            })
            ->label('語言')
            ->default( function() {
                return kernel()->locale->getDefault();
            })
            ->renderAs('SelectInput')
            ;
    }
}
