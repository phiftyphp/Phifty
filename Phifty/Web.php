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

    public function langs()
    {
        return kernel()->locale->getLangList();
    }

    public function get_result( $resultName )
    {
        $runner = ActionRunner::getInstance();
        return $runner->getResult( $resultName );
    }
}
