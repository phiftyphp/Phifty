<?php

/*

_('en')
_('ja')
_('zh_TW')
_('zh_CN')
_('en_US')
_('fr')

*/

namespace Phifty {
use Exception;
define( 'L10N_LOCALE_KEY' , 'locale' );

class Locale
{
    public $current;
    public $langList = array();
    public $localedir;
    public $domain;
    public $defaultLang;

    public function setDefault( $lang )
    {
        $this->defaultLang = $lang;

        return $this;
    }

    public function getDefault()
    {
        return $this->defaultLang;
    }

    public function init( $force_lang = null  )
    {
        $lang = null;

        if ( $force_lang )
            $lang = $force_lang;

        if ( ! $lang && isset($_GET[ L10N_LOCALE_KEY ]) )
            $lang = $_GET[ L10N_LOCALE_KEY ];
        if ( ! $lang && isset($_POST[ L10N_LOCALE_KEY ]) )
            $lang = $_POST[ L10N_LOCALE_KEY ];
        if ( ! $lang && isset( $_SESSION[ L10N_LOCALE_KEY ] ) )
            $lang = @$_SESSION[ L10N_LOCALE_KEY ];

        if ( ! $lang && isset( $_COOKIE['locale'] ) )
            $lang = @$_COOKIE['locale'];

        if ( ! $lang )
            $lang = $this->defaultLang;
        if ( ! $lang )
            throw new Exception( 'Locale: Language is not define.' );
        $this->speak( $lang );

        return $this;
    }

    public function saveSession()
    {
        kernel()->session->set( L10N_LOCALE_KEY , $this->current );
    }

    public function saveCookie()
    {
        $time = time() + 60 * 60 * 24 * 30;
        @setcookie( L10N_LOCALE_KEY , $this->current , $time , '/' );
    }

    public function getCurrentLang()
    {
        return $this->current;
    }

    // set current language
    public function speak( $lang )
    {
        $this->current = $lang;
        $this->saveCookie();
        $this->saveSession();
        $this->initGettext();

        return $this;
    }

    public function isSpeaking( $lang )
    {
        return $this->current == $lang;
    }

    public function current()
    {
        return $this->current;
    }

    public function speaking()
    {
        return $this->current;
    }

    public function available()
    {
        return $this->getLangList();
    }

    // get available language list
    public function getLangList()
    {
        // update language Label
        foreach ($this->langList as $n => $v) {
            $this->langList[ $n ] = _( $n );
        }

        return $this->langList;
    }

    public function setLangList( $list )
    {
        $this->langList = $list;
    }

    public function add( $lang , $name = null )
    {
        if ( ! $name )
            $name = _( $lang );
        $this->langList[ $lang ] = $name;

        return $this;
    }

    public function remove( $lang )
    {
        unset( $this->langList[ $lang ] );

        return $this;
    }

    public function currentName() {
        return $this->name( $this->current );
    }

    // get language name from language hash
    public function name( $lang )
    {
        return @$this->langList[ $lang ];
    }

    public function domain( $domain )
    {
        $this->domain = $domain;

        return $this;
    }

    public function localedir( $dir )
    {
        $this->localedir = $dir;

        return $this;
    }

    public function setupEnv()
    {
        $lang = $this->current;
        // putenv("LANG=$lang");
        // putenv("LANGUAGE=$lang");
        setlocale(LC_MESSAGES, $lang );
	header('Content-Language: '. strtolower(str_replace('_', '-', $lang)) );
        // setlocale(LC_ALL,  $lang);
        // setlocale(LC_TIME, $lang);
        setlocale(LC_ALL,  "$lang.UTF-8" );
        setlocale(LC_TIME, "$lang.UTF-8");
    }

    public function initGettext( $textdomain = null , $localedir = null )
    {
        if ( ! $textdomain ) {
            $textdomain = $this->domain;
        }

        if ( ! $textdomain ) {
            throw new Exception( 'Locale: textdomain is not defined.' );
        }

        if ( ! $localedir ) {
            $localedir = $this->localedir;
        }

        if ( ! $localedir ) {
            throw new Exception( 'Locale: locale dir is not defined.' );
        }

        if ( $localedir ) {
            $this->setupEnv();
            bindtextdomain( $textdomain, $localedir );
            bind_textdomain_codeset( $textdomain, 'UTF-8');
            textdomain( $textdomain );
        }
        return $this;
    }
}

/*
function current_language()
{
    return l10n()->speaking();
}

function current_lang()
{
    return l10n()->speaking();
}
*/

}

namespace {
    function __()
    {
        $args = func_get_args();
        $msg = _( array_shift( $args ) );
        $id = 1;
        foreach ($args as $arg) {
            $msg = str_replace( "%$id" , $arg , $msg );
            $id++;
        }

        return $msg;
    }
}
