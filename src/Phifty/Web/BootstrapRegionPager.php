<?php
namespace Phifty\Web;
use WebUI\Components\Pager\BootstrapPager;

class BootstrapRegionPager extends BootstrapPager
{
    public function renderLink( $num , $text = null , $moreclass = "" , $disabled = false , $active = false)
    {
        if ( $text == null ) {
            $text = $num;
        }

        if ($disabled) {
            return $this->renderLinkDisabled( $text , $moreclass );
        }

        $liClass = '';
        if ($active) {
            $liClass = 'active';
        }

        return <<<EOF
    <li class="$liClass"><a data-target-page="$num" class="pager-link $moreclass"
        onclick="return Region.of( this ).refreshWith({ page: $num });">$text</a></li>
EOF;

    }

    public function renderLinkDisabled( $text , $moreclass = "" )
    {
        return <<<EOF
<li class="disabled"><a class="pager-link pager-disabled $moreclass">$text</a></li>
EOF;
    }

}
