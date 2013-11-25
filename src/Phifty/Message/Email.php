<?php
namespace Phifty\Message;
use Swift_Message;
use ArrayAccess;

class Email extends Message implements ArrayAccess
{
    public $message;

    public $from;

    public $subject;

    public $template;

    /**
     * format can be 'text/html' or 'text/plain'
     */
    public $format;

    public $data = array();




    public function __construct() 
    {
        $this->message = Swift_Message::newInstance();

        // processing subject
        if ( $subject = $this->getSubject() ) {
            $this->message->setSubject($subject);
        }
        if ( $from = $this->getFrom() ) {
            $this->message->setFrom( (array) $from );
        }
    }

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
        }
    }

    public function send() 
    {
        $view = kernel()->view;
        $view->setArgs( $this->getData() );
        $content = $view->render($this->getTemplate());
        if ( $this->format ) {
            $this->message->setBody($content,$this->format);
        } else {
            $this->message->setBody($content);
        }
        return kernel()->mailer->send($this->message);
    }
}



