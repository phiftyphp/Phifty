<?php
namespace Phifty;
use Phifty\View;
use ActionKit\ActionRunner;

class Web
{

    public function render_all_results()
    {
        $runner = kernel()->action;
        $results = $runner->getResults();
        $html = '';
        foreach ($results as $key => $value) {
            $html .= $this->render_result( $key );
        }

        return $html;
    }


    /**
     * @param string[] $assets asset names
     * @param string   $target   name
     *
     * {{ Web.include_assets(['jquery','jquery-ui'], 'page_name')|raw}}
     */
    public function include_assets($assetNames, $target = null)
    {
        $kernel = kernel();
        $assetObjs = $kernel->asset->loader->loadAssets($assetNames);
        return $kernel->asset->render->renderAssets($assetObjs,$target);
    }

    public function langs()
    {
        return kernel()->locale->getLangList();
    }

    public function get_result( $resultName )
    {
        $runner = ActionRunner::getInstance();

        return $runner->getResult( $resultName );
    }

    public function render_result( $resultName )
    {
        $runner = ActionRunner::getInstance();
        if ( $result = $runner->getResult( $resultName ) ) {
            $view = new View;
            $view->result = $result;
            return $view->render('@CoreBundle/phifty/action_result_box.html');
        }
    }

}
