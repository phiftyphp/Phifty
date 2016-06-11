<?php
namespace Phifty\ServiceProvider;
use SessionKit;
use Phifty\Kernel;


class SessionServiceProvider extends BaseServiceProvider
{

    public function getId() { return 'Session'; }

    public function register(Kernel $kernel, $options = array())
    {
        // XXX: customize this for $options
        $kernel->session = function() {
            $session = new SessionKit\Session(array(
                'state'   => new SessionKit\State\NativeState,
                'storage' => new SessionKit\Storage\NativeStorage,
            ));

            return $session;
        };
    }
}
