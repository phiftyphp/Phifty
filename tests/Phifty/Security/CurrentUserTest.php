<?php

class CurrentUserTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $record = new UserBundle\Model\User;
        $record->load(array('account' => 'testing'));
        if($record->id)
            $record->delete();
    }

    public function tearDown()
    {
        $record = new UserBundle\Model\User;
        $record->load(array('account' => 'testing'));
        if($record->id)
            $record->delete();
    }


    function test()
    {
        $user = new Phifty\Security\CurrentUser;
        ok($user);
        ok($user->userModelClass);
    }

    function testWithOptions() 
    {
        $user = new Phifty\Security\CurrentUser(array( 
            'model_class' => 'UserBundle\\Model\\User',
            'session_prefix' => '__phifty_',
            'primary_key' => 'id',
        ));
        ok($user);
    }

    function testWithRecord() 
    {
        $record = new UserBundle\Model\User;
        $ret = $record->create(array(
            'account' => 'testing',
            'email'   => 'testing@testing.com',
            'password' => 'testing',
        ));
        ok($ret->success,'user created:'. $ret);

        $record->addRole('admin');
        ok($record->id);
        ok($record->getRoles());
        ok($record->hasRole('admin'),'has role admin');

        $user = new Phifty\Security\CurrentUser(array( 
            'record' => $record,
        ));
        ok($user);
        ok($user->id,'Has record id');
        ok($user->record,'Get record object');
        ok($user->session,'Got session object');

        ok($user->hasRole('admin'));
        $user->removeRole('admin');
    }
}

