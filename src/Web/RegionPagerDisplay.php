<?php
namespace Phifty\Web;

use Phifty\Web\PagerDisplay;

class RegionPagerDisplay extends PagerDisplay
{

    public function render_link( $num , $text = null , $moreclass = "" , $disabled = false )
    {
        if ( $text == null )
            $text = $num;

        if ( $disabled )

            return $this->render_link_dis( $text , $moreclass );

        return <<<EOF
 <a class="pager-link $moreclass"
   onclick="return Region.of( this ).refreshWith({ page: $num  });">$text</a>
EOF;

    }

    public function render_link_dis( $text , $moreclass = "" )
    {
        return <<<EOF
<a class="pager-link pager-disabled $moreclass">$text</a>
EOF;
    }
}
