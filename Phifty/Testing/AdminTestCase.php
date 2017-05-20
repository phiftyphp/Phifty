<?php
namespace Phifty\Testing;
use Exception;

class AdminTestCase extends Selenium2TestCase
{
    protected $urlOf = array(
        'login' => '/bs/login',
        'news' => '/bs/news',
        'newsCategory' => '/bs/news_category',
        'contacts' => '/bs/contacts',
        'contactGroups' => '/bs/contact_groups',
        'product' => '/bs/product',
        'pages' => '/bs/pages'
    );

    protected function gotoLoginPage()
    {
        $this->url( $this->getBaseUrl() . $this->urlOf['login'] );
    }

    protected function login( $transferTo = null )
    {
        $this->gotoLoginPage();

        find_element_ok('input[name=account]')->value('admin');
        find_element_ok('input[name=password]')->value('admin');
        find_element_ok('.submit')->click();

        // $this->assertNotNull( ! find_element('.message.error') , 'login error' );
        wait_for('#aimMenuStart');

        if ($transferTo) {
            $url = @$this->urlOf[$transferTo];
            $a = find_element_ok("#adminAimMenu a[href=\"$url\"]");
            if (!$a)
                throw new Exception("Menu link $transferTo not found.");
            $a->click();
        }
    }

    protected function logout()
    {
        find_element('#operation .buttons a[href]')->click();
        wait();
    }

    protected function isCreated()
    {
        message_like('/created|已經建立|成功/');
    }

    protected function isUpdated()
    {
        $msg = find_element('.message.success')->text();
        jgrowl_like('/updated|已經更新|成功|更新成功/');
    }

    protected function isDeleted()
    {
        jgrowl_like('/(deleted|刪除成功|成功)/');
    }

    public function isUploaded()
    {
        jgrowl_like('/(created|已經建立|成功)/');
    }

    public function uploadFile( $sel, $filepath )
    {
        find_element($sel)->value( realpath( $filepath ));
    }
}
