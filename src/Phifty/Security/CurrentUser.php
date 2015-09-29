<?php
namespace Phifty\Security;
use Phifty\Session;
use Exception;
use BadMethodCallException;

/**
 * CurrentUserRole interface, for getting roles from model.
 */
use Phifty\Security\CurrentUserRole;

/**
 * @package Phifty
 *
 * Phifty CurrentUser object
 *
 * managing current user data stash, you can
 * define your custom user model and your custom current user class
 * to customize this.
 *
 * This class is mixined with current user model class.
 *
 * TODO: support login from cookie
 *
 *   $currentUser = new CurrentUser;  // load current user from session data
 *
 *   $currentUser = new CurrentUser(array(
 *       'model_class' => 'UserBundle\Model\User',
 *   ));
*/
class CurrentUser
{
    /**
     * User model class
     */
    public $userModelClass;

    /**
     * @var mixed User model record
     */
    public $record; // user model record

    /**
     * @var string model primary key
     */
    public $primaryKey = 'id';

    /**
     * @var string session prefix string
     */
    public $sessionPrefix = '__user_';

    /**
     * @var Phifty\Session Session Manager
     */
    public $session;

    public function __construct($args = array() )
    {
        $record = null;
        if ( is_object($args) ) {
            $record = $args;
        } else {
            if ( isset($args['record']) ) {
                $record = $args['record'];
                $this->userModelClass = get_class($record);
            } else {
                $this->userModelClass =
                    isset($args['model_class'])
                        ? $args['model_class']
                        : kernel()->config->get( 'framework', 'CurrentUser.Model' )
                            ?: 'UserBundle\Model\User';  // default user model (UserBundle\Model\User)
            }

            if ( isset($args['session_prefix']) ) {
                $this->sessionPrefix = $args['session_prefix'];
            }
            if ( isset($args['primary_key']) ) {
                $this->primaryKey = $args['primary_key'];
            }
        }

        /**
         * Initialize a session pool with prefix 'user_'
         */
        $this->session = new Session( $this->sessionPrefix );

        /* if record is specified, update session from record */
        if ($record) {
            if ( ! $this->setRecord( $record ) ) {
                throw new Exception('CurrentUser can not be loaded from record.');
            }
        } else {
            // load record from session,
            // get current user record id, and find record from it.
            //
            // TODO: use virtual loading, do not manipulate database if we have
            // data in session already.
            //
            // TODO: provide a verify option to verify database item before
            // loading.
            if ( $userId = $this->session->get( $this->primaryKey ) ) {
                $class = $this->userModelClass;
                $virtualRecord = new $class;
                foreach ( $virtualRecord->getColumnNames() as $name ) {
                    $virtualRecord->$name = $this->session->get($name);
                }
                $this->record = $virtualRecord;
                // $this->setRecord(new $this->userModelClass(array( $this->primaryKey => $userId )));
            }
        }
    }

    /**
     * Set user model class
     *
     * @param string $class user model class
     */
    public function setModelClass($class)
    {
        $this->userModelClass = $class;
    }


    /**
     * Get user model class.
     */
    public function getModelClass()
    {
        return $this->userModelClass;
    }

    /**
     * Reload record and update session
     */
    public function updateSession()
    {
        if (! $this->record) {
            throw new Exception("Record is empty, Can not update session.");
        }
        $this->record->reload();
        $this->updateSessionFromRecord($this->record);
    }

    public function getSessionFields($record)
    {
        return $record->getColumnNames();
    }

    /**
     * Update session data from record
     *
     * @param mixed User record object
     */
    public function updateSessionFromRecord($record)
    {
        // get column maes to register 
        foreach ( $this->getSessionFields($record) as $name ) {
            $val = $record->$name;
            $this->session->set( $name, is_object($val) ? $val->__toString() : $val );
        }
        if ($record instanceof CurrentUserRole) {
            $this->session->set('roles', $record->getRoles() );
        } elseif ( method_exists($record,'getRoles') ) {
            $this->session->set('roles', $record->getRoles() );
        } elseif ( isset($record->role) ) {
            $this->session->set('roles', array($record->role) );
        } else {
            $this->session->set('roles', array() );
        }
    }

    /**
     * Set current user record
     *
     * @param mixed User record object
     *
     * @return bool
     */
    public function setRecord( $record )
    {
        if ($record && $record->id) {
            $this->record = $record;
            $this->updateSessionFromRecord($record);
            return true;
        }

        return false;
    }

    public function getRecord()
    {
        if ($this->record && $this->record->id) {
            return $this->record;
        }
    }

    /**
     * Integrate setter with model record object
     */
    public function __set( $key , $value )
    {
        if ($this->record) {
            $this->record->update(array($key => $value));
            $this->session->set($key, $value);
        }
    }

    public function __isset($key)
    {
        return $this->session->has($key)
             || ($this->record && $this->record->__isset($key));
    }

    /**
     * Mixin getter with model record object
     *
     * @param  string $key session key
     * @return mixed
     */
    public function __get( $key )
    {
        if ($val = $this->session->get($key)) {
            return $val;
        }
        if ($this->record && isset($this->record->$key) ) {
            return $this->record->$key;
        }
        // throw new Exception('CurrentUser Record is undefined.');
    }

    /**
     * Mixin with user record object.
     */
    public function __call($method,$args)
    {
        if ($this->record) {
            if ( method_exists($this->record,$method) ) {
                return call_user_func_array(array($this->record,$method), $args);
            } else {
                throw new BadMethodCallException("Record $method not found.");
            }
        }
    }

    /**
     * Returns role identities
     *
     * @return string[] returns role identities
     */
    public function getRoles()
    {
        if ( $roles = $this->session->get('roles') ) {
            return $roles;
        }
        if ($this->record && $this->record->id) {
            if ($this->record instanceof CurrentUserRole) {
                return $this->record->getRoles();
            } elseif ( method_exists($this->record,'getRoles') ) {
                return $this->record->getRoles();
            }
        }

        return array();
    }

    /**
     * Check if a role exists.
     *
     * @param  string  $roleId
     * @return boolean
     */
    public function hasRole($roleId)
    {
        if ( $roles = $this->session->get('roles') ) {
            if ( is_object($roleId) )

                return in_array($roleId->__toString(), $roles );
            return in_array($roleId , $roles);
        }
        if ($this->record && $this->record->id) {
            return $this->record->hasRole($roleId);
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id; // call __get
    }

    public function logout()
    {
        $this->session->clear();
    }

    /*******************
     * Helper functions
     *******************/

    // XXX: should be integrated with ACL
    public function isLogged()
    {
        return $this->getId();
    }

    public function isLogin()
    {
        return $this->getId();
    }

}
