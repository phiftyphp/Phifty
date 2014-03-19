<?php
namespace Phifty\Message;
use Swift_Message;
use ArrayAccess;
use RuntimeException;

class Email extends Message implements ArrayAccess
{
    public $message;

    public $from;

    public $to;

    public $subject;

    public $template;

    /**
     * format can be 'text/html' or 'text/plain', 'markdown'
     */
    public $format;

    public $data = array();


    /**
     * In the constructor we create a Swift Message instance
     */
    public function __construct() 
    {
        $this->message = Swift_Message::newInstance();

        // processing subject, TODO: rename getSubject() to subject()
        //
        // `subject()` should be predefine-able from class
        //
        // `getSubject()` should call subject()
        //
        // `setSubject()` should override the $this->subject.
        //
        if ( $subject = $this->getSubject() ) {
            $this->message->setSubject($subject);
        }

        if ( $from = $this->getFrom() ) {
            $this->message->setFrom( (array) $from );
        }
        if ( $to = $this->getTo() ) {
            $this->message->setTo( (array) $to );
        }
    }

    public function from() { }
    public function to() { }
    public function subject() { }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        $this->message->setSubject($subject);
    }

    public function getFrom() {
        return $this->from;
    }

    public function getTo() {
        return $this->from;
    }

    public function setTo($to) {
        $this->to = $to;
        $this->message->setTo((array) $to);
    }

    public function setFrom($from) {
        $this->from = $from;
        $this->message->setFrom((array) $from);
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template) 
    {
        $this->template = $template;
    }

    public function setFormat($format) {
        $this->format = $format;
    }

    public function getFormat() {
        return $this->format;
    }


    public function setArguments($args) {
        $this->data = $args;
    }

    public function getArguments() {
        return $this->data;
    }

    public function getArgument($key) {
        if ( isset($this->data[$key]) ) {
            return $this->data[$key];
        }
    }


    // XXX: Rename getData to getArguments()
    public function getData()
    {
        return $this->data;
    }
    
    public function offsetSet($name,$value)
    {
        $this->data[ $name ] = $value;
    }
    
    public function offsetExists($name)
    {
        return isset($this->data[ $name ]);
    }
    
    public function offsetGet($name)
    {
        return $this->data[ $name ];
    }
    
    public function offsetUnset($name)
    {
        unset($this->data[$name]);
    }

    public function __get($n) {
        if ( isset($this->data[$n]) ) {
            return $this->data[$n];
        }
    }


    public function __set($n, $v)
    {
        $this->data[$n] = $v;
    }

    public function __call($m, $a) 
    {
        if ( method_exists($this->message, $m) ) {
            return call_user_func_array( array($this->message, $m) , $a );
        } else {
            throw new RuntimeException("$m is not defined. in " . get_class($this) );
        }
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getContent() {
        $twig = kernel()->twig->env;
        return $twig->loadTemplate($this->getTemplate())->render($this->getData());
    }

    public function send() 
    {
        // $view = kernel()->getObject('view',array('Phifty\\View'));
        // $view->setArgs( $this->getData() );
        $content = $this->getContent();

        if ( $this->format && $this->format === 'text/markdown' || $this->format === "markdown" ) {
            if ( ! function_exists('Markdown') ) {
                throw new RuntimeException('Markdown library is not loaded.');
            }
            $this->format = 'text/html';
            $content = Markdown($content);
        }

        if ( $this->format ) {
            $this->message->setBody($content,$this->format);
        } else {
            $this->message->setBody($content);
        }
        return kernel()->mailer->send($this->message);
    }
}



