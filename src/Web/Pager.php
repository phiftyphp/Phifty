<?php
namespace Phifty\Web;

/**
 * A simple pager, does not depends on Pager interface.
 *
 * @version 2
 */
class Pager
{
    public $firstText;
    public $lastText;
    public $nextText;
    public $prevText;

    public $showHeader = false;
    public $showNavigator = true;
    public $wrapperClass = array('pager');
    public $whenOverflow  = true;

    public $rangeLimit = 3;

    public $totalPages = 0;
    public $pageSize = 20;
    public $currentPage = 1;

    /**
     *
     * @param integer current page
     * @param integer total size
     * @param integer page size (optional)
     */
    public function __construct()
    {
        $this->firstText = _('Page.First');
        $this->lastText  = _('Page.Last');
        $this->nextText  = _('Page.Next');
        $this->prevText  = _('Page.Previous');
        if ( $args = func_get_args() ) {
            if ( 2 === count($args) ) {
                $this->currentPage = $args[0] ?: 1;
                $this->calculatePages($args[1]);
            } elseif ( 3 === count($args) ) {
                $this->currentPage = $args[0] ?: 1;
                $this->calculatePages($args[1],$args[2]);
            }
        }
    }

    public function setFirstPageText($text)
    {
        $this->firstText = $text;
    }

    public function setLastPageText($text)
    {
        $this->lastText = $text;
    }

    public function setNextPagetext($text)
    {
        $this->nextText = $text;
    }

    public function setPrevPageText($text)
    {
        $this->prevText = $text;
    }

    public function addClass($class)
    {
        $this->wrapperClass[] = $class;
    }

    /**
     * @param integer $total
     * @param integer $size  = null  (optional)
     */
    public function calculatePages($total,$size = 20)
    {
        $this->pageSize = $size ?: 20;
        $this->totalPages = $total > 0 ? (int) ceil($total / $this->pageSize ) : 0;
    }

    public function mergeQuery( $orig_params , $params = array() )
    {
        $params = array_merge(  $orig_params , $params );

        return '?' . http_build_query( $params );
    }

    public function renderLink( $num , $text = null , $moreclass = "" , $disabled = false )
    {
        if ( $text == null )
            $text = $num;

        if ( $disabled )

            return $this->renderLink_dis( $text , $moreclass );

        $args = array_merge( $_GET , $_POST );
        $href = $this->mergeQuery( $args , array( "page" => $num ) );

        return <<<EOF
 <a class="pager-link $moreclass" href="$href">$text</a>
EOF;
    }

    public function renderLink_dis( $text , $moreclass = "" )
    {
        return <<<EOF
<a class="pager-link pager-disabled $moreclass">$text</a>
EOF;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render2()
    {
        $heredoc = new \Phifty\View\Heredoc('twig');
        $heredoc->content =<<<TWIG
<div class="pager">
    {% for i in 0 .. totalPages %}

    {% endfor %}
</div>
TWIG;
        $html = $heredoc->render(array(
            'currentPage' => $this->currentPage,
            'totalPages'  => $this->totalPages,
        ));
    }

    public function render()
    {
        $cur = $this->currentPage;
        $total_pages = $this->totalPages;

        if ($this->whenOverflow && $this->totalPages <= 1) {
            return "";
        }

        $pagenum_start = $cur > $this->rangeLimit ? $cur - $this->rangeLimit : 1 ;
        $pagenum_end   = $cur + $this->rangeLimit < $total_pages ?  $cur + $this->rangeLimit : $total_pages;

        $output = "";
        $output .= '<div class="'. join(' ',$this->wrapperClass) .'">';

        if ( $this->showHeader )
            $output .= '<div class="pager-current">' . _('Pager.page') .': ' . $this->currentPage . '</div>';

        if ($this->showNavigator) {

            if ( $cur > 1 )
                $output .= $this->renderLink( 1       , $this->firstText , 'pager-first' , $cur == 1 );

            if ( $cur > 5 )
                $output .= $this->renderLink( $cur - 5 , _("Pager.Prev 5 Pages") , 'pager-number' );

            if ( $cur > 1 )
                $output .= $this->renderLink( $cur -1 , $this->prevText  , 'pager-prev'  , $cur == 1 );
        }

        if ( $cur > 5 )
            $output .= $this->renderLink( 1 , 1 , 'pager-number' ) . ' ... ';

        for ($i = $pagenum_start ; $i <= $pagenum_end ; $i++) {
            if ( $i == $this->currentPage )
                $output .= $this->renderLink( $i , $i , 'pager-number active pager-number-current' );
            else
                $output .= $this->renderLink( $i , $i , 'pager-number' );
        }

        if ( $cur + 5 < $total_pages )
            $output .= ' ... ' . $this->renderLink( $total_pages , $total_pages , 'pager-number' );

        if ($this->showNavigator) {

            if ( $cur < $total_pages )
                $output .= $this->renderLink( $cur + 1,
                            $this->nextText , 'pager-next' , $cur == $this->totalPages );

            if ( $cur + 5 < $total_pages )
                $output .= $this->renderLink( $cur + 5,
                            _("Pager.Next 5 Pages") , 'pager-number' );

            if ( $total_pages > 1 && $cur < $total_pages )
                $output .= $this->renderLink( $this->totalPages,
                            $this->lastText , 'pager-last' , $cur == $this->totalPages );
        }

        $output .= '</div>';

        return $output;
    }
}
