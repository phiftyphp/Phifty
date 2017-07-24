<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;

class NotificationSubscriber
{

    /**
     * @var string Subscriber Identity
     */
    public $id;

    /**
     * @var ZMQSocket
     */
    public $subscriber;

    public $requester;

    public $center;

    public function __construct($id = null, $center = null)
    {
        $this->center = $center ?: NotificationCenter::getInstance();
        $this->id = $this->center->getSubscriberId($id);
        $this->subscriber = new ZMQSocket($this->center->getContext(), ZMQ::SOCKET_SUB);
        $this->subscriber->setSockOpt(ZMQ::SOCKOPT_IDENTITY, $this->id); // prevent break
        $this->subscriber->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, $this->id); // subscribe by client Id
        $this->subscriber->connect( $this->center->getSubscribePoint() );

        $this->requester = $this->center->createRequester();
    }

    public function askBacklog($topic)
    {
        $tId = is_string($topic) ? $topic : $topic->id;
        $this->requester->send( 'blog ' . $tId . ' ' . $this->id );

        return $this->requester->recv();
    }

    public function unsubscribe($topic)
    {
        $tId = is_string($topic) ? $topic : $topic->id;
        $this->requester->send( 'unsub ' . $tId . ' ' . $this->id );

        return $this->requester->recv();
    }

    public function subscribe($topic)
    {
        $tId = is_string($topic) ? $topic : $topic->id;
        $this->requester->send( 'sub ' . $tId . ' ' . $this->id );

        return $this->requester->recv();
    }

    public function listen($callback)
    {
        while (true) {
            $string = $this->subscriber->recv();
            list($sId,$topicId,$binary) = explode(' ',$string,3);
            $payload = $this->center->decode($binary);
            call_user_func($callback,$topicId,$payload,$this);
        }
    }
}
