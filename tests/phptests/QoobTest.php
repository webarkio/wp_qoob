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

    public function testGetUrlItems()
    {
        $qoob = new MockQoob();
        $class  = new ReflectionClass($qoob);
        $method = $class->getMethod('getUrlItems');
        $method->setAccessible(true);//makes the property available
        $result = $method->invoke($qoob);//calls a function
        $demo = array(
                        'id' => 'demo',
                        'url' =>PLUGIN_PATH.'/blocks/demo/'
                    );

        $this->assertEquals($result[0], $demo);
    }

    public function testGetUrlQoobTemplates()
    {
        $qoob = new MockQoob();
        $class  = new ReflectionClass($qoob);
        $method = $class->getMethod('getUrlQoobTemplates');
        $method->setAccessible(true);//makes the property available
        $result = $method->invoke($qoob);//calls a function
        $path =  PLUGIN_PATH."/qoob/tmpl/block/block-default-blank.html";
        $demo = array(
                        'id' => 'block-default-blank.html',
                        'url' => $path
                    );

        $this->assertEquals($result[0], $demo);
    }

    public function testGetItems()
    {
        $qoob = new MockQoob();
        $class  = new ReflectionClass("MockQoob");
        $method = $class->getMethod('getItems');
        $method->setAccessible(true);//makes the property available
        $result = $method->invoke($qoob);//calls a function
        $this->assertEquals(array_key_exists('title',$result[0][defaults]), true);
    }

    public function testGetItem()
    {
        $qoob = new MockQoob();
        $class  = new ReflectionClass("MockQoob");
        $method = $class->getMethod('getItem');
        $method->setAccessible(true);//makes the property available
        $result = $method->invoke($qoob,'demo');//calls a function
        $demo = array(
                    'id' => 'demo',
                    'url' =>PLUGIN_PATH.'/blocks/demo/'
                    );

        $this->assertEquals($result, $demo);
    }

    public function testGetGroups()
    {
        $qoob = new MockQoob();
        $class  = new ReflectionClass("MockQoob");
        $method = $class->getMethod('getGroups');
        $method->setAccessible(true);//makes the property available
        $result = $method->invoke($qoob);//calls a function

        $demo = array(
                        'id' => 'demo',
                        'label' => 'Demo',
                        'position' => '0'
                    );

        $this->assertEquals($result[0], $demo);
    }

    //loadAssetsScripts()
    //function loadData()

    // public function testLoadData()
    // {
    //     $qoob = new MockQoob(); 
    //     ob_start();
    //     $qoob->loadData();
    //     file_put_contents('test.txt', ob_get_contents());
    //     ob_end_close();

    //     // $data = ob_get_contents();
    //     // $data = json_encode($data);
    //     //print_r($metabox);
    //     //ob_end_clean();
 
    //     var_dump($data);
    //     $this->assertEquals($data, 2);
    // }

    //function getTplFiles()

    public function testGetHtml()
    {
        $qoob = new MockQoob();
        $class  = new ReflectionClass("MockQoob");
        $method = $class->getMethod('getHtml');
        $method->setAccessible(true);//makes the property available
        $result = $method->invoke($qoob,'demo');//calls a function
        $html = '<div class="demo-block">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="header-block">{{title}}</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <p>
                    {{{text}}}
                </p>
            </div>
        </div>
    </div>
</div>';
        $this->assertEquals($result, $html);
    }
    //function loadItem()

    //function loadTmpl()
    //function load_blocks_scripts($assets_type)

    // public function testLoadBlocksScripts()
    // {
    //     $qoob = new MockQoob();
    //     $blocks_path = dirname(get_template_directory()) . '/wp_qoob_theme/blocks/entypo';
    //     $blocks_url = dirname(get_template_directory()) . '/wp_qoob_theme/blocks/entypo';
    //     $theme_url = dirname(get_template_directory()) . '/wp_qoob_theme/blocks/entypo';
    //     $config_json = dirname(get_template_directory()) . '/wp_qoob_theme/blocks/entypo/config.json';
    //     $w = $qoob->load_blocks_scripts('style');
    //     print_r($blocks_path);
    //     print_r($blocks_url);
    //     $this->assertEquals($w,2);
    // }

}