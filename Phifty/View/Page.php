<?php
namespace Phifty\View;
use Phifty\View;
use Phifty\Kernel;

/*
 * Phifty Page class
 *
 * used from Controller (most), render content block with custom layout.
 *
 *    $page = new Phifty\View\Page($kernel, array( 'layout' => 'layout.html' , 'content' => 'content.html' ) );
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

    public function __construct(Kernel $kernel, array $options = array())
    {
        parent::__construct($kernel);

        if (isset($options['i18n'])) {
            $this->i18n = $options['i18n'];
        }

        if (isset($options['layout'])) {
            $this->layout = $options['layout'];
        }

        if (isset( $options['content'] )) {
            $this->content = $options['content'];
        }

        if (isset($options['cache'])) {
            $this->cache = true;
        }
    }

    public function getLocal()
    {
        return $this->kernel->locale->current();
    }

    /* split template name, join with locale name, like:
     *    template_en.html,
     *    template_zh.html,
     *    template_zh_cn.html
     *
     *    etc..
     **/
    protected function jointI18n( $template )
    {
        $parts = explode( '.', $template );
        $ext = array_pop($parts);

        # generate template name with locale

        return join('.',$parts) . '_' . $this->getLocal() . '.' . $ext;
    }

    public function display($template = null)
    {
        if (! $template && $this->content) {
            $template = $this->content;
        }

        if ($this->i18n && $this->getLocal()) {
            $template = $this->jointI18n( $template );
        }

        // XXX: do we need i18n block seperated for layout page template?

        /* render subcontent template and put content into layout template */
        if ($this->layout) {
            $content = $this->engine->render( $template , $this->args );
            $this->engine->display($this->layout , array(
                'PageContent' => $content
            ));
        } else {
            $this->engine->display( $template , $this->args );
        }
    }

}
