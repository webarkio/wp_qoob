<?php



class MockQoob extends Qoob {
    public function __construct() {
        $this->blocks_url = PLUGIN_PATH.'/blocks';
        $this->blocks_path = PLUGIN_PATH.'/blocks';
    }
}

class QoobTest extends WP_UnitTestCase { //WP_UnitTestCase

    private $test;

    function setUp() {
        parent::setUp();
        $this->test = null;
    }

    public function testLoadPageData() {
        $qoob = new MockQoob();

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
                            'data' => file_get_contents('tests/phptests/demo_test.txt')
                        ]
                ];

        $data = json_encode($data);

        // $this->setExpectedException( 'WPAjaxDieContinueException' );
        // try {
            $qoob->savePageData($data);
            $newData = $qoob->loadPageData($post_id);
        // } catch ( WPAjaxDieContinueException $e ) {}

        $this->assertEquals($newData, file_get_contents('tests/phptests/demo_test.txt'));
    }

    //???
    public function testPluginUpdate()
    {
        $qoob = new MockQoob();
        $ver_old = get_site_option('qoob_version');
        $qoob->pluginUpdate();
        $ver_up = get_site_option('qoob_version');
        $this->assertEquals($ver_up, $ver_old);
    }

    //???
    // public function testPluginUpdateTo()
    // {
    //     $qoob = new MockQoob();
    //     $ver_last = $qoob->pluginUpdateTo_1_1_0();
    //     $this->assertEquals($ver_last, '1.1.3');
    // }


    //testinfoMetabox()

    public function testInfoMetaboxDisplay()
    {
        $qoob = new MockQoob();
        $meta = '<p>Current page has been edited with Qoob Page Builder. To edit this page as regular one - go to Qoob editor by pressing "qoob" button and remove all blocks.</p>';

        $infometa = $qoob->infoMetaboxDisplay();
        $metabox = ob_get_contents();
        ob_clean();
        $this->assertEquals($metabox, $meta);
    }

    //???
    public function testSavePostMeta()
    {
        $qoob = new MockQoob();

        //Creating post in database
        $post_data = array(
             'post_title'    => 'Some PostMeta',
        );
        $post_id = wp_insert_post( wp_slash($post_data) );
        add_post_meta($post_id, 'post_meta', 'Test fot SavePostMeta');
        $qoob->savePostMeta($post_id);
        $meta = get_post_meta($post_id);
        $this->assertEquals(array_key_exists('post_meta',$meta), true);

    }

    // public function testrestoreRevision()

    // filterContent()

    // onPageEdit()

    // registrationAjax()

    // loadBlocksAssets()

    public function testGetUrlAssets()
    {

        $qoob = new MockQoob();
        $url = $qoob->getUrlAssets();
        $url_path = "http://example.org/wp-content/plugins/wp_qoob/assets/";
        $this->assertEquals($url, $url_path);
    }

    public function testGetUrlQoob()
    {
        $qoob = new MockQoob();
        $url = $qoob->getUrlQoob();
        $url_path = "http://example.org/wp-content/plugins/wp_qoob/qoob/";
        $this->assertEquals($url, $url_path);
    }

    public function testGetPathTemplates()
    {
        $qoob = new MockQoob();
        $template = $qoob->getPathTemplates();
        $this->assertEquals(!(strstr($template, 'wp_qoob/templates')), false);
    }

    public function testSetDefaultTitle()
    {
        $qoob = new MockQoob();
        $title = $qoob->setDefaultTitle('Default');
        $this->assertEquals($title,'Default');
    }

    public function testGetUrlPage()
    {
        $qoob = new MockQoob();
        $url = $qoob->getUrlPage('5');
        $url_path = "http://example.org/wp-admin/post.php?post_id=5&post_type=&qoob=true";
        $this->assertEquals($url, $url_path);
    }

    public function testAddEditLinkAction()
    {
        $qoob = new MockQoob();
        $url = $qoob->addEditLinkAction('edit');
        $url_path = "edit";
        $this->assertEquals($url, $url_path);
    }

    //public function testinitEditPage()

    //public function testsetPost()

    public function testShowButton()
    {
        $qoob = new MockQoob();

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

        $show = $qoob->showButton($post_id);
        $this->assertEquals($show, false);
    }

    //public function testaddAdminBarLink()

    //allowInsertEmptyPost()

    //renderPage()

    public function testSetTitlePage()
    {
        $qoob = new MockQoob();
        $title = $qoob->setTitlePage();
        $title_page = "Edit Page with qoob";
        $this->assertEquals($title, $title_page);
    }

    //function iframeScripts()
    //function frontendScripts()
    //function adminScripts()
    //function loadScripts()

    public function testGetUrlQoobTemplates()
    {
        $qoob = new MockQoob();
        $class  = new ReflectionClass($qoob);
        $method = $class->getMethod('getUrlQoobTemplates');
        $method->setAccessible(true);//makes the property available
        $result = $method->invoke($qoob);//calls a function
        $path =  substr(PLUGIN_PATH, 0, -1) . "/qoob/tmpl/block/block-default-blank.html";
        $demo = array(
                        'id' => 'block-default-blank.html',
                        'url' => $path
                    );

        $this->assertEquals($result[0], $demo);
    }


    //loadAssetsScripts()
    //function loadData()


    //function getTplFiles()

    //function loadItem()

    //function loadTmpl()
    //function load_blocks_scripts($assets_type)

}