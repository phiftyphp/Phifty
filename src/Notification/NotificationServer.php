<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;
use ZMQDevice;
use Exception;

class NotificationServer
{
    public $center;

    public $responder;

    public $publiser;

    public function __construct($center = null)
    {
        $this->center = $center ?: NotificationCenter::getInstance();
        $this->connectDevice(
            $this->center->getPublishPoint(true),
            $this->center->getSubscribePoint(true)
        );
    }

    public function connectDevice($bind,$publishEndPoint)
    {
        //  Socket to talk to clients
        // $this->responder = new ZMQSocket($this->center->context, ZMQ::SOCKET_REP);
        $this->responder = new ZMQSocket($this->center->getContext(), ZMQ::SOCKET_REP);
        $this->responder->bind($bind);

        $this->publisher = new ZMQSocket($this->center->getContext(), ZMQ::SOCKET_PUB);

        // High Water Mark
        // Configure the maximium queue (buffer limit)
        $this->publisher->setSockOpt(ZMQ::SOCKOPT_HWM, 100);
        $this->publisher->bind($publishEndPoint);
    }

    public function start()
    {
        // new ZMQDevice($this->pull, $this->publisher);
        $subscribers = array(
            /* topicId => array( subscriberId => queue ) */
        );
        $topics = array(
            /* topic => message queue */
        );

        while (true) {
            try {
                // Wait for next request from client
                $msg = $this->responder->recv();
                $result = 0;

                printf("Received request: [%s]\n", $msg);

                // register subscriber a topic
                if ( strpos($msg,'reg') === 0 ) {
                    list($cmd,$topicId) = explode(' ',$msg,2);
                    if ( ! isset($topics[$topicId]) ) {
                        $topics[ $topicId ] = array(); // empty message queue
                        $result = 1;
                    }
                }
                // unregister subscriber from a topci
                elseif ( strpos($msg,'unreg') === 0 ) {
                    list($cmd,$topicId) = explode(' ',$msg,2);
                    unset($topics[$topicId]);
                    $result = 1;
                } elseif ( strpos($msg,'unsub') === 0 ) {
                    list($cmd,$topicId,$sId) = explode(' ',$msg,3);
                    if ( isset($subscribers[$topicId][$sId]) ) {
                        unset($subscribers[$topicId][$sId]);
                        $result = 1;
                    }
                } elseif ( strpos($msg,'sub') === 0 ) {
                    list($cmd,$topicId,$sId) = explode(' ',$msg,3);

                    if ( ! isset($subscribers[$topicId]) ) {
                        $subscribers[$topicId] = array();
                    }

                    if ( ! isset($subscribers[$topicId][$sId]) ) {
                        $subscribers[ $topicId ][ $sId ] = array();
                        $result = 1;
                    }
                } elseif ( strpos($msg,'blog') === 0 ) {
                    list($cmd,$topicId,$sId) = explode(' ',$msg,3);
                    // send backlog to subscriber

                    if ( isset( $topics[$topicId] ) ) {
                        foreach ($topics[$topicId] as $msg) {
                            printf("Publish backlog: [%s]\n", $sId . ' ' . $topicId . ' ' . $msg);
                            $this->publisher->send($sId . ' ' . $topicId . ' ' . $msg); // send messages to channels
                        }
                        $result = 1;
                    }
                } else {
                    list($topicId,$binary) = explode(' ',$msg,2);

                    if ( ! isset($topics[$topicId]) )
                        $topics[$topicId] = array();

                    // Delivery to a registered client
                    if ( isset($topics[$topicId]) ) {
                        // get subscribers and publish to
                        // them
                        if ( isset($subscribers[$topicId]) ) {
                            foreach ($subscribers[$topicId] as $sId => $q) {
                                printf("Publish message: [%s]\n", $sId . ' ' . $topicId . ' ' . $binary);
                                $this->publisher->send($sId . ' ' . $topicId . ' ' . $binary); // send messages to channels
                            }
                        }
                    }

                    // save the message
                    $topics[$topicId][] = $binary;
                    $result = 1;
                }
                $this->responder->send($result);
            } catch ( Exception $e ) {
                echo $e;
            }
        }
    }
}
