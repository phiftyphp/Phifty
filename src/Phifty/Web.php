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
     * @param string $name
     */
    public function include_loaded_assets($target = null)
    {
        $kernel = kernel();

        // Instead of loading assets by triggering asset.load event
        // the flow (plugin service->init, appliation->init) can
        // not load the assets with the correct sequence.
        /*
        foreach ($kernel->applications as $app) {
            // load application assets
            $app->loadAssets();
        }
        */
        foreach ($kernel->plugins as $plugin) {
            $plugin->loadAssets();
        }

        // call asset.load trigger to load global assets
        // $kernel->event->trigger('asset.load');

        // get all loaded assets
        $assets = $kernel->asset->loader->all();

        // use renderAssets to render html
        return $kernel->asset->render->renderAssets($assets, $target);
    }

    /**
     * @param string[] $assets asset names
     * @param string   $name   name
     *
     * {{ Web.include_assets(['jquery','jquery-ui'], 'page_name')|raw}}
     */
    public function include_assets($assets, $name = null)
    {
        $kernel = kernel();
        $assetObjs = $kernel->asset->loader->loadAssets($assets);
        return $kernel->asset->render->renderAssets($assetObjs,$name);
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
