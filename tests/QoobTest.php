<?php

class MockQoob extends Qoob {}

class QoobTest extends WP_UnitTestCase {

    private $test;

    function setUp() {
        parent::setUp();
        $this->test = null;
    }

    public function testloadPageData() {
        $module = new MockQoob();

        //Setting current user to aministrator
        // $this->_setRole( 'administrator' );
        $user_id = wp_create_user( 'Test', 'test', 'test@gmail.com');
        $wp_user_object = new WP_User($user_id);
        $wp_user_object->set_role('administrator');
        wp_set_current_user($user_id);

        //Creating post in database
        $post_data = array(
             'post_title'    => 'Some title'
        );
        $post_id = wp_insert_post( wp_slash($post_data) );

        $testString = '{"text": "Test string to save into metafield."}';
        $data = [
            'page_id' => $post_id,
            'blocks' => [
                'html' => 'Some text',
                'data' => $testString
                ]
            ];

        $data = json_encode($data);

        // $this->setExpectedException( 'WPAjaxDieContinueException' );
        // try {
            $module->savePageData($data);
            $newData = $module->loadPageData($post_id);
        // } catch ( WPAjaxDieContinueException $e ) {}

        $this->assertEquals($newData, $testString);
    }

    //???
    public function testpluginUpdatee()
    {
        $module = new MockQoob();
        $ver_old = get_site_option('qoob_version');
        $module->pluginUpdate();
        $ver_up = get_site_option('qoob_version');
        $this->assertEquals($ver_up, $ver_old);
    }

    //???
    // public function testpluginUpdateTo_1_1_0()
    // {
    //     $module = new MockQoob();
    //     $ver_last = $module->pluginUpdateTo_1_1_0();
    //     $this->assertEquals($ver_last, '1.1.3');
    // }


    //testinfoMetabox()

    public function testinfoMetaboxDisplay()
    {
        $module = new MockQoob();
        $meta = '<p>Current page has been edited with Qoob Page Builder. To edit this page as regular one - go to Qoob editor by pressing "qoob it" button and remove all blocks.</p>';

        $infometa = $module->infoMetaboxDisplay();
        $metabox = ob_get_contents();
        ob_clean();
        $this->assertEquals($metabox, $meta);
    }

    //???
    public function testsavePostMeta()
    {
        $module = new MockQoob();

        //Creating post in database
        $post_data = array(
             'post_title'    => 'Some PostMeta',
        );
        $post_id = wp_insert_post( wp_slash($post_data) );
        add_post_meta($post_id, 'post_meta', 'Test fot SavePostMeta');
        $module->savePostMeta($post_id);
        $meta = get_post_meta($post_id);
        $this->assertEquals(array_key_exists('post_meta',$meta), true);

    }

    // public function testrestoreRevision()

    // filterContent()

    // onPageEdit()

    // registrationAjax()

    // loadBlocksAssets()

    public function testgetUrlAssets()
    {
        $module = new MockQoob();
        $url = $module->getUrlAssets();
        $url_path = "http://example.org/wp-content/plugins/wp_qoob/assets/";
        $this->assertEquals($url, $url_path);
    }

    public function testgetUrlQoob()
    {
        $module = new MockQoob();
        $url = $module->getUrlQoob();
        $url_path = "http://example.org/wp-content/plugins/wp_qoob/qoob/";
        $this->assertEquals($url, $url_path);
    }

    public function testgetPathTemplates()
    {
        $module = new MockQoob();
        $template = $module->getPathTemplates();
        $this->assertEquals(!(strstr($template, 'wp_qoob/templates')), false);
    }

    public function testsetDefaultTitle()
    {
        $module = new MockQoob();
        $title = $module->setDefaultTitle('Default');
        $this->assertEquals($title,'Default');
    }

    public function testgetUrlPage()
    {
        $module = new MockQoob();
        $url = $module->getUrlPage('5');
        $url_path = "http://example.org/wp-admin/post.php?post_id=5&post_type=&qoob=true";
        $this->assertEquals($url, $url_path);
    }

    public function testaddEditLinkAction()
    {
        $module = new MockQoob();
        $url = $module->addEditLinkAction('edit');
        $url_path = "edit";
        $this->assertEquals($url, $url_path);
    }

    //public function testinitEditPage()

    //public function testsetPost()

    public function testshowButton()
    {
        $module = new MockQoob();

        //Creating post in database
        $post_data = array(
             'post_title'    => 'Some title'
        );
        $post_id = wp_insert_post( wp_slash($post_data) );

        $testString = '{"text": "Test string to save into metafield."}';
        $data = [
            'page_id' => $post_id,
            'blocks' => [
                'html' => 'Some text',
                'data' => $testString
                ]
            ];

        $data = json_encode($data);

        $show = $module->showButton($post_id);
        $this->assertEquals($show, false);
    }

    //public function testaddAdminBarLink()

    //allowInsertEmptyPost()

    //renderPage()

    public function testsetTitlePage()
    {
        $module = new MockQoob();
        $title = $module->setTitlePage();
        $title_page = "Edit Page with qoob";
        $this->assertEquals($title, $title_page);
    }



}