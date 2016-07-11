<?php
namespace Phifty\Web;
use Phifty\Web\Pager;

class RegionPager extends Pager
{

    public function renderLink( $num , $text = null , $moreclass = "" , $disabled = false )
    {
        if ($text == null) {
            $text = $num;
        }
        if ($disabled) {
            return $this->renderLinkDisabled( $text , $moreclass );
        }
        return <<<EOF
 <a class="pager-link $moreclass"
   onclick="return Region.of( this ).refreshWith({ page: $num  });">$text</a>
EOF;

    }

    public function renderLinkDisabled( $text , $moreclass = "" )
    {
        return <<<EOF
<a class="pager-link pager-disabled $moreclass">$text</a>
EOF;
    }

}
