<?php
namespace Phifty\Testing;

class CRUDTestCase extends AdminTestCase
{

    public function waitForList()
    {
        wait_for('.crud-list');
    }

    public function clickCreateBtn()
    {
        find_element_ok('input.record-create-btn')->click();
        wait_for('div[id^=record-div]');
        wait_for_tinymce();
    }

    public function clickRecordCloseBtn()
    {
        find_element_ok('input.record-close-btn')->click();
    }

    public function clickRecordSaveBtn()
    {
        find_element_ok('.crud-edit input[type=submit]')->click();
    }

    public function getDeleteBtnElements()
    {
        wait_for('.crud-list');

        return find_elements('.result tbody input.record-delete-btn');
    }

    public function clickAllDeleteBtns()
    {
        $o = get_test_obj();

        $elements = $this->getDeleteBtnElements();
        foreach ($elements as $element) {
            $this->assertNotNull($element,'Found delete button');
            $element->click();
            contains_ok('確認刪除', get_alert_text() );
            accept_alert();
            wait_for_jgrowl();
            jgrowl_close();
            usleep(100 * 1000); // 0.10 second
        }
    }

    public function clickRecordEditBtn($i = 1)
    {
        $this->waitForList();
        find_element_ok(".result tbody tr:nth-child($i) input.record-edit-btn")->click();
        wait_for('.crud-edit');
        wait_for('div[id^=record-div]');
        wait_for_tinymce();
        wait();
    }

}
