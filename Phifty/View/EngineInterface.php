<?php

namespace Phifty\View;

interface EngineInterface
{

    /*
     * Return Renderer Object
     *
     * @return object
     */
    public function newRenderer();

    /*
     * Render template with args , return string
     *
     * @param string $template template name
     * @param array  $args     template args
     *
     * @return string the rendererd template content
     *
     */
    public function render( $template , $args = null );

    /*
     * Render template from string
     *
     * @param string $stringTemplate
     * @param array  $args
     *
     */
    // function renderString( $stringTemplate , $args = null );

    /*
     * Display template from string
     *
     * @param string $stringTemplate
     * @param array  $args
     *
     */
    // function displayString( $stringTemplate , $args = null );

    /* display template directly */
    // function display( $template , $args = null );
}
