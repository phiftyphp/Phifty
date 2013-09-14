<?php
/*

$email = new Phifty\Email;
$email->to( $to );
$email->to( $to );
$email->from( $from );
$email->subject(  $subject );
$email->template( $template , array(
        "user" => $user   // $assign
    ) );
$email->send();
*/

namespace Phifty;

use Phifty\View;

class Email
{
    public $to = array();
    public $from;
    public $cc = array();
    public $bcc = array();
    public $subject;
    public $replyTo;

    public $template;
    public $templateVars = array();
    public $templateEngineOpts = array();

    public $contentType = 'html';
    public $content;

    public function __construct()
    {
        // load default email config from config file.

    }

    public function to( $to )
    {
        $this->to[] = $to;

        return $this;
    }

    public function from( $from )
    {
        $this->from = $from;  // chould be array

        return $this;
    }

    public function bcc( $bcc )
    {
        $this->bcc[] = $bcc;

        return $this;
    }

    public function cc( $cc )
    {
        array_push( $this->cc , $cc );

        return $this;
    }

    public function subject( $subject )
    {
        $this->subject = $subject;

        return $this;
    }

    public function replyTo( $replies )
    {
        $this->replyTo = $replies;

        return $this;
    }

    public function encodeColumns($data)
    {
        if ( empty( $data ) )

            return;

        if ( is_array($data) ) {
            $cols = array();
            foreach ($data as $name => $email) {
                if ( is_integer( $name ) ) {
                    $cols[] = $email;  // just append email chars
                } else {
                    $cols[] = $this->encode( $name ) . ' <' . $email . '>';
                }
            }

            return join(',', $cols);
        } elseif ( is_string($data) ) {
            if ( preg_match('/(.*?)\s*<(.*?)>/',$data,$regs) ) {
                list($orig,$name,$email) = $regs;

                return $this->encode($name) . ' <' . $email . '>';
            } else {
                return $data;
                # throw new \Exception( _('Unsupported email column format') );
            }
        }
    }

    public function template_vars( $vars )
    {
        $this->templateVars = $vars;

        return $this;
    }

    public function applyLangTag( $str )
    {
        // XXX: put current_lang() tag out of this class.
        $tag = str_replace( '_' , '-' , strtolower( current_lang() ) );

        return str_replace( '{lang}' , $tag , $str );
    }

    // when setting template, the contentType will be 'html'
    public function template( $template , $vars = array() , $engineOpts = array() )
    {
        $this->template = $template;
        if ($vars) {
            $this->templateVars = $vars;
        }
        $this->templateEngineOpts = $engineOpts;
        $this->contentType = 'html';

        return $this;
    }

    public function assign( $name , $value )
    {
        $this->templateVars[ $name ] = $value;

        return $this;
    }

    private function renderTemplate()
    {
        $view = new View;
        if ($this->templateVars) {
            $view->setArgs( $this->templateVars );
        }
        $view->subject = $this->subject;
        $view->from = $this->from;
        $view->to = $this->to;

        return $view->render($this->template);
        /*
        $templateFile = $this->applyLangTag( $this->template );
        if ( $smt->templateExists( $templateFile ) )
            return $smt->fetch( $templateFile );
        else
            throw new Exception( "{$templateFile} doesn't exist." );
        */
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getContent()
    {
        if ( $this->template )

            return $this->renderTemplate();
        if ( $this->content )

            return $this->content;
    }

    private function getHeader()
    {
        $from = $this->encodeColumns($this->from);
        $to   = $this->encodeColumns($this->to);
        $cc   = $this->encodeColumns($this->cc);
        $bcc     = $this->encodeColumns($this->bcc);
        $replyTo = $this->encodeColumns($this->replyTo);

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "From: $from\r\n";
        $headers .= "To: $to\r\n";

        if ( $cc )           $headers .= "CC: $cc\r\n";
        if ( $bcc )          $headers .= "BCC: $bcc\r\n";
        if ( $replyTo )      $headers .= "REPLY-TO: $replyTo\r\n";

        return $headers;
    }

    public function text( $text )
    {
        $this->contentType = 'text';
        $this->content = $text;

        return $this;
    }

    public function html( $html )
    {
        $this->contentType = 'html';
        $this->content = $html;

        return $this;
    }

    public function encode($str)
    {
        return '=?UTF-8?B?'.base64_encode($str).'?=';
    }

    public function send()
    {
        $headers = $this->getHeader();

        if ( $this->contentType == 'html' )
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        elseif ( $this->contentType == 'text' )
            $headers .= "Content-type: text/plain; charset=UTF-8\r\n";

        $subject = $this->getSubject();
        $content = $this->getContent();

        if ( ! $subject )
            throw new \Exception("mail subject is not defined.");
        if ( ! $content )
            throw new \Exception("mail content is not defined.");

        $subject = $this->encode($subject);

        return mail(
            $this->encodeColumns($this->to),
            $subject,
            $content,
            $headers);
    }
}
