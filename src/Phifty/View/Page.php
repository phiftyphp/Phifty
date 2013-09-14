<?php
namespace Phifty\View;
use Phifty\View;

/*
 * Phifty Page class
 *
 * used from Controller (most), render content block with custom layout.
 *
 *    $page = new Phifty\View\Page( array( 'layout' => 'layout.html' , 'content' => 'content.html' ) );
 *    $page->display();
 *
 *
 */
class Page extends View
{
    public $layout;
    public $content;
    public $i18n = false;
    public $cache = false;

    public function __construct( $options = array() )
    {
        if ( isset( $options['i18n'] ) )
            $this->i18n = $options['i18n'];

        if ( isset( $options['layout'] ) )
            $this->layout = $options['layout'];

        if ( isset( $options['content'] ) )
            $this->content = $options['content'];

        if ( isset( $options['cache'] ) )
            $this->cache = true;

        parent::__construct( @$options['engine'] );
    }

    public function getLocal()
    {
        // XXX: replace with locale from AppKernel
        return @$_SESSION[ 'locale' ];
    }

    /* split template name, join with locale name, like:
        *    template_en.html,
        *    template_zh.html,
        *    template_zh_cn.html
        *
        *    etc..
        **/
    public function jointI18n( $template )
    {
        $parts = explode( '.', $template );
        $ext = array_pop($parts);

        # generate template name with locale

        return join('.',$parts) . '_' . $this->getLocal() . '.' . $ext;
    }

    public function display( $template = null )
    {
        if ( ! $template && $this->content )
            $template = $this->content;

        $engine = $this->getEngine();

        if ( $this->i18n && $this->getLocal() )
            $template = $this->jointI18n( $template );

        // XXX: do we need i18n block seperated for layout page template?

        /* render subcontent template and put content into layout template */
        if ($this->layout) {
            $content = $engine->render( $template , $this->args );
            $engine->display( $this->layout , array(
                'PageContent' => $content
            ) );

            /*
            $layoutView = new Smarty;
            $layoutView->assign( 'PageContent' , $content );
            $layoutView->display( $this->layout );
            */
        } else {
            $engine->display( $template , $this->args );
        }
    }

}
