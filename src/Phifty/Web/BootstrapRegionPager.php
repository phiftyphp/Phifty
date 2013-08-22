<?php
namespace Phifty\Web;
use Phifty\Web\Pager;

class BootstrapRegionPager extends BootstrapPager
{

    public function renderLink( $num , $text = null , $moreclass = "" , $disabled = false )
    {
        if ( $text == null ) {
            $text = $num;
        }

        if ( $disabled ) {
            return $this->renderLinkDisabled( $text , $moreclass );
        }

        return <<<EOF
 <li><a class="pager-link $moreclass"
   onclick="return Region.of( this ).refreshWith({ page: $num  });">$text</a></li>
EOF;

    }

    public function renderLinkDisabled( $text , $moreclass = "" )
    {
        return <<<EOF
 <li><a class="pager-link pager-disabled $moreclass">$text</a></li>
EOF;
    }

}
