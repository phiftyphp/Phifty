<?php
namespace Phifty\Message;
use Swift_Message;
use ArrayAccess;
use RuntimeException;
use Twig_Loader_String;
use Twig_Environment;

class Email extends Message implements ArrayAccess
{
    public $message;

    public $subject;

    /**
     * @var string pre-defined email title
     *
     * A title is a part of a subject, that is, subject includes site name as its prefix 
     * and append the title.
     */
    public $title;

    public $template;

    /**
     * format can be 'text/html' or 'text/plain', 'markdown'
     */
    public $format;

    protected $data = array();

    public $from;
    public $to;
    public $cc;
    public $bcc;


    /**
     * In the constructor we create a Swift Message instance
     */
    public function __construct() 
    {
        $this->message = Swift_Message::newInstance();
    }

    public function subject() {
        if ( $this->subject ) {
            return $this->subject;
        }
        return kernel()->getApplicationName() . ' - ' . $this->title();
    }

    /**
     * This method is for subclasses to override.
     *
     * @return string title string
     */
    public function title() {
        return $this->title ?: 'Untitled Mail';
    }

    public function from() {
        if ( $this->from ) {
            return $this->from;
        }
        return $this->message->getFrom();
    }

    public function to() {
        if ( $this->to ) {
            return $this->to;
        }
        return $this->message->getTo();
    }

    public function cc() {
        if ( $this->cc ) {
            return $this->cc;
        }
        return array();
    }

    public function bcc() {
        if ( $this->bcc ) {
            return $this->bcc;
        }
        return array();
    }

    public function template() {
        return $this->template;
    }

    /**
     * Get template file path.
     *
     * @return string template file path.
     */
    public function getTemplate()
    {
        if ( $this->template ) {
            return $this->template;
        }
        return $this->template();
    }




    /**
     * Set template file path.
     *
     * @param string $template
     */
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
        return array_merge(array( 
            'Email' => $this,
            'Kernel' => kernel(),
        ),$this->data );
    }

    public function getArgument($key) {
        if ( isset($this->data[$key]) ) {
            return $this->data[$key];
        }
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

    /**
     * The default renderContent method, get the template and render content.
     *
     * @return string rendered content.
     */
    public function renderContent() {
        $twig     = kernel()->twig->env;
        $template = $twig->loadTemplate($this->getTemplate());
        $html     = $template->render($this->getArguments());

        // Support more formats here.
        // rewrite format to 'text/markdown'
        if ( $this->format && $this->format === 'text/markdown' || $this->format === "markdown" ) {
            if ( ! function_exists('Markdown') ) {
                throw new RuntimeException('Markdown library is not loaded.');
            }
            $this->format = 'text/html';
            return Markdown($html);
        }
        return $html;
    }

    public function renderSubject() {
        $subjectTpl = $this->subject();
        $loader = new Twig_Loader_String();
        $twig = new Twig_Environment($loader);
        return $twig->render($subjectTpl, $this->getArguments());
    }

    public function send()
    {
        if ( ! $this->message->getSubject() ) {
            $this->message->setSubject( $this->renderSubject() );
        }
        if ( ! $this->message->getFrom() ) {
            $this->message->setFrom( $this->from() );
        }
        if ( ! $this->message->getTo() ) {
            $this->message->setTo( $this->to() );
        }
        if ( ! $this->message->getCc() ) {
            $this->message->setCc( $this->cc() );
        }
        if ( ! $this->message->getBcc() ) {
            $this->message->setBcc( $this->bcc() );
        }


        // $view = kernel()->getObject('view',array('Phifty\\View'));
        // $view->setArgs( $this->getArguments() );
        $content = $this->renderContent();

        if ( $this->format ) {
            $this->message->setBody($content,$this->format);
        } else {
            $this->message->setBody($content);
        }
        return kernel()->mailer->send($this->message);
    }
}



