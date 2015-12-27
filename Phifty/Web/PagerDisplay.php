<?php

namespace Phifty\Web;

class PagerDisplay
{
    public $pager;
    public $first_text;
    public $last_text;
    public $next_text;
    public $prev_text;

    public $show_pager_header = false;
    public $show_navigator = true;
    public $wrapper_class = 'pager';
    public $when_overflow  = true;

    public function __construct( $pager )
    {
        $this->pager = $pager;
        $this->config();
    }

    public function config()
    {
        $this->text_config();
    }

    public function text_config()
    {
        $this->first_text = _('__pager.First');
        $this->last_text  = _('__pager.Last');
        $this->next_text  = _('__pager.Next');
        $this->prev_text  = _('__pager.Previous');
    }

    public function merge_to_query( $orig_params , $params = array() )
    {
        foreach ($params as $key => $value) {
            /* override original get params */
            $orig_params[ $key ] = $value;
        }

        $qs = "";
        $parts = array();
        foreach ($orig_params as $key => $value) {
            $pair = $key . "=" . $value;
            array_push( $parts , $pair );
        }
        // return urlencode("?" . join( '&' , $params ) );
        return "?" . join( '&' , $parts );
    }

    public function render_link( $num , $text = null , $moreclass = "" , $disabled = false )
    {
        if ( $text == null )
            $text = $num;

        if ( $disabled )

            return $this->render_link_dis( $text , $moreclass );

        $args = array_merge( $_GET , $_POST );
        $href = $this->merge_to_query( $args , array( "page" => $num ) );

        return <<<EOF
<a class="pager-link $moreclass" href="$href">$text</a>
EOF;
    }

    public function render_link_dis( $text , $moreclass = "" )
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
            'currentPage' => $this->pager->currentPage,
            'totalPages'  => $this->pager->totalPages,
        ));
    }

    public function render()
    {

        $cur = $this->pager->currentPage;
        $total_pages = $this->pager->totalPages;

        if ($this->when_overflow && $this->pager->totalPages == 1) {
            return "";
        }

        $pagenum_start = $cur > 5 ? $cur - 5 : 1 ;
        $pagenum_end   = $cur + 5 < $total_pages ?  $cur + 5 : $total_pages;

        $output = "";
        $output .= '<div class="'.$this->wrapper_class.'">';

        if ( $this->show_pager_header )
            $output .= '<div class="pager-current">' . _('__pager.page') .': ' . $this->pager->currentPage . '</div>';

        if ($this->show_navigator) {

            if ( $cur > 1 )
                $output .= $this->render_link( 1       , $this->first_text , 'pager-first' , $cur == 1 );

            if ( $cur > 5 )
                $output .= $this->render_link( $cur - 5 , _("__pager.Prev 5 Pages") , 'pager-number' );

            if ( $cur > 1 )
                $output .= $this->render_link( $cur -1 , $this->prev_text  , 'pager-prev'  , $cur == 1 );
        }

        if ( $cur > 5 )
            $output .= $this->render_link( 1 , 1 , 'pager-number' ) . ' ... ';

        for ($i = $pagenum_start ; $i <= $pagenum_end ; $i++) {
            if ( $i == $this->pager->currentPage )
                $output .= $this->render_link( $i , $i , 'pager-number active pager-number-current' );
            else
                $output .= $this->render_link( $i , $i , 'pager-number' );
        }

        if ( $cur + 5 < $total_pages )
            $output .= ' ... ' . $this->render_link( $total_pages , $total_pages , 'pager-number' );

        if ($this->show_navigator) {

            if ( $cur < $total_pages )
                $output .= $this->render_link( $cur + 1,
                            $this->next_text , 'pager-next' , $cur == $this->pager->totalPages );

            if ( $cur + 5 < $total_pages )
                $output .= $this->render_link( $cur + 5,
                            _("Pager.Next 5 Pages") , 'pager-number' );

            if ( $total_pages > 1 && $cur < $total_pages )
                $output .= $this->render_link( $this->pager->totalPages,
                            $this->last_text , 'pager-last' , $cur == $this->pager->totalPages );
        }

        $output .= '</div>';

        return $output;
    }
}
