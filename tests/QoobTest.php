<?php

class MockQoob extends Qoob {

    const TYPE_TEST = "test";

    public function type() {
        return self::TYPE_TEST;
    }

}

class QoobTest extends WP_UnitTestCase {

    private $test;

    function setUp() {
        parent::setUp();
        $this->test = null;
    }

    public function testEmpty()
    {
        $this->assertTrue(empty($this->test));
    }

    public function testloadPageData() {
        $module = new MockQoob();
        $data = [
            'page_id' => '1',
            'blocks' => '2',
            'html' => 'text'
            ];

        $save = $module->savePageData($data);
        print_r($save);
        $newData = $module->loadPageData('1');
        print_r($newData);
        $this->deepEquals($newData, $data);

    }

}