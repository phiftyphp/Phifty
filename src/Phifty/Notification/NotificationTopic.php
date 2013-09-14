<?php
namespace Phifty\Notification;

class NotificationTopic
{
    public $id;
    public $encoder;
    public $center;
    public $requester;

    public function __construct($topicId = null, $center = null)
    {
        $this->id = $topicId ?: uniqid();
        $this->center = $center ?: NotificationCenter::getInstance();
        $this->encoder = $this->center->getEncoder();
        $this->requester = $this->center->createRequester();
    }

    public function register()
    {
        $this->requester->send('reg ' . $this->id);

        return $this->requester->recv();
    }

    public function unregister()
    {
        $this->requester->send('unreg ' . $this->id);

        return $this->requester->recv();
    }

    /**
     * Publish normal message
     *
     * @param mixed $message
     */
    public function publish($message)
    {
        $payload = $this->center->encode($message);

        //  Socket to talk to server (REP-REQ)
        $this->requester->send( $this->id . ' ' . $payload);

        return $this->requester->recv() === '1';
    }
}
