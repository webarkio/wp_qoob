<?php
class MockQoob extends Qoob {

    public function createDefaultPost() {
    	$post_data = array(
             'post_title'    => 'Some title',
             'post_type' => 'page'
        );
        $post_id = wp_insert_post( wp_slash($post_data) );

        return $post_id;
    }


    public function updateDefaultPost($post_id, $title) {
    	$post_data = array(
    		'ID'           => $post_id,
            'post_title'    => $title
        );
        return wp_update_post( $post_data );
    }


    // helper function for saving page data
    public function saveDefaultBlocksData($post_id = null) {
        if (!is_null($post_id)) {
            $data = [
            'page_id' => $post_id,
            'blocks' => [
                            'html' => 'Some text',
                            'data' => file_get_contents('tests/phptests/demo_test.txt')
                        ]
            ];
            
            return $this->savePageData(json_encode($data));
        }
    }
}

class QoobTestAjax extends WP_Ajax_UnitTestCase {

    public function setUp() {
        parent::setUp();
        $this->qoob = new MockQoob();
        $this->_setRole('administrator');
    }

    // // loadQoobData test
    // public function testLoadQoobData() {
    //     try {
    //         $this->_handleAjax( 'qoob_load_qoob_data' );
    //     } catch ( WPAjaxDieContinueException $e ) {}
     
    //     $response = json_decode( $this->_last_response );
    //     $this->assertInternalType( 'object', $response );
    //     $this->assertObjectHasAttribute( 'data', $response );
    //     $this->assertInternalType( 'object', $response->data );
    // }

    // // loadSavePageData test
    // public function testSavePageData() {
    //     $post_id = $this->qoob->createDefaultPost();
    //     $response = $this->qoob->saveDefaultBlocksData($post_id);

    //     $this->assertInternalType( 'array', $response );
    //     $this->assertArrayHasKey( 'success', $response );
    //     $this->assertTrue( $response['success'] );        
    // }

    // // loadPageData test
    // public function testLoadPageData() {
    //     $_POST['page_id'] = $this->qoob->createDefaultPost();
    //     $this->qoob->saveDefaultBlocksData($_POST['page_id']);

    //     try {
    //         $this->_handleAjax( 'qoob_load_page_data' );
    //     } catch ( WPAjaxDieContinueException $e ) {}

    //     $response = json_decode( $this->_last_response );
    //     $this->assertInternalType( 'object', $response );
    //     $this->assertObjectHasAttribute( 'data', $response );
    //     $this->assertEquals($response->data, file_get_contents('tests/phptests/demo_test.txt'));
    // }

    // // pluginUpdate
    // public function testPluginUpdate() {
    //     $ver = '0.9.0';
    //     $this->qoob->setVersion($ver);
    //     delete_option('qoob_version');
    //     $this->qoob->pluginUpdate();
    //     $verUp = get_site_option('qoob_version');
    //     $this->assertEquals($ver, $verUp);
    // }

    // // getTranslationArray
    // public function testGetTranslationArray() {
    //     $result = $this->qoob->getTranslationArray();
    //     $this->assertTrue(is_array($result) && !empty($result));
    // }

    // // infoMetabox
    // public function testinfoMetabox() {
    //     $post_id = $this->qoob->createDefaultPost();
    //     $this->qoob->saveDefaultBlocksData($post_id);

    //     // Set global $post variable for use in tested function
    //     global $post;
    //     global $wp_meta_boxes;
    //     $post = get_post($post_id);
    //     $this->qoob->infoMetabox();
    //     $this->assertTrue(!is_null($wp_meta_boxes['page']['advanced']['default']['qoob-page-info']));
    // }

    // // infoMetaboxDisplay
    // public function testInfoMetaboxDisplay() {
    //     $meta = '<p>Current page has been edited with Qoob Page Builder. To edit this page as regular one - go to Qoob editor by pressing "qoob" button and remove all blocks.</p>';
    //     $metabox = '';
    //     ob_start();
    //     $this->qoob->infoMetaboxDisplay();
    //     $metabox = ob_get_contents();
    //     ob_end_clean();
    //     $this->assertEquals($metabox, $meta);
    // }

    // // savePostMeta
    // public function testSavePostMeta() {
    //     $testMeta=array();
    //     $testMeta[]='Test fot SavePostMeta1';
    //     $testMeta[]='Test fot SavePostMeta2';
    //     $testMeta[]='Test fot SavePostMeta3';

    //     $post_id = $this->qoob->createDefaultPost();
        
    //     add_post_meta($post_id, 'qoob_data', $testMeta[0]);
    //     $this->qoob->updateDefaultPost($post_id, "title0");

    //     update_post_meta($post_id, 'qoob_data', $testMeta[1]);
    //     $this->qoob->updateDefaultPost($post_id, "title1");
        
    //     update_post_meta($post_id, 'qoob_data', $testMeta[2]);
    //     $this->qoob->updateDefaultPost($post_id,"title2");
        

    //     $rev = wp_get_post_revisions( $post_id );

    //     $key = 1;
    //     foreach ($rev as $rev_post_id => $value) {
    //         $meta = get_post_meta($rev_post_id);
    //         $this->assertEquals($meta['qoob_data'][0], $testMeta[count($rev) - $key]);
    //         $key++;
    //     }
    // }

    // // restoreRevision
    // public function testRestoreRevision() {
    //     $post_id = $this->qoob->createDefaultPost();
    //     $this->qoob->saveDefaultBlocksData($post_id);

    //     // metadata saved on first post save
    //     $prevMeta = stripslashes(get_post_meta($post_id, 'qoob_data', true));

    //     $this->qoob->updateDefaultPost($post_id, 'Changed title');

    //     // Setting new qoob_data field
    //     update_post_meta($post_id, 'qoob_data', 'My data');

    //     $revision_id = array_keys(wp_get_post_revisions($post_id))[0];

    //     // restoring previous revision with default data
    //     $this->qoob->restoreRevision($post_id, $revision_id);

    //     $newMeta = get_post_meta($post_id, 'qoob_data', true);

    //     $this->assertEquals($prevMeta, $newMeta);
    // }

    // // filterContent
    // public function testFilterContent() {
    //     $post_id = $this->qoob->createDefaultPost();
        
    //     $_GET['qoob'] = true;

    //     $this->assertEquals('<div id="qoob-blocks"></div>', $this->qoob->filterContent());
        
    //     $_GET['qoob'] = false;

    //     $this->assertEquals('', $this->qoob->filterContent());

    //     $this->qoob->saveDefaultBlocksData($post_id);

    //     global $post;

    //     $post = get_post($post_id);

    //     $this->assertEquals('', $this->qoob->filterContent());
    // }

    // // onPageEdit
    // public function testoOnPageEdit() {
    //     $post_id = $this->qoob->createDefaultPost();

    //     $this->qoob->saveDefaultBlocksData($post_id);

    //     $_GET['post'] = $post_id;

    //     $this->qoob->onPageEdit();

    //     // if we edit page, that contains qoob_data - editor supprot should be removed
    //     $this->assertNotTrue(post_type_supports( 'page', 'editor' ));
    // }

    // // getUrlAssets
    // public function testGetUrlAssets() {
    //     $this->assertEquals($this->qoob->getUrlAssets(), "http://example.org/wp-content/plugins/wp_qoob/assets/");
    // }

    // // getUrlQoob
    // public function testGetUrlQoob() {
    //     $this->assertEquals($this->qoob->getUrlQoob(), "http://example.org/wp-content/plugins/wp_qoob/qoob/");
    // }

    // // getPathTemplates
    // public function testGetPathTemplates() {
    //     $this->assertTrue((bool) strpos($this->qoob->getPathTemplates(), 'wp_qoob/templates'));
    // }

    // // setDefaultTitle
    // public function testSetDefaultTitle() {
    //     $this->assertEquals($this->qoob->setDefaultTitle('Default'),'Default');
    //     $this->assertEquals($this->qoob->setDefaultTitle([]),'(no title)');
    // }

    // // getUrlPage
    // public function testGetUrlPage() {
    //     // We have to take id of no-existing page to be cappable in output prediction
    //     $this->assertEquals($this->qoob->getUrlPage(-1), "http://example.org/wp-admin/post.php?post_id=-1&post_type=&qoob=true");
    // }

    // // addEditLinkAction
    // public function testAddEditLinkAction() {
    //     $post_id = $this->qoob->createDefaultPost();
    //     $this->qoob->saveDefaultBlocksData($post_id);
    //     global $post;
    //     $post = get_post($post_id);

    //     $result = $this->qoob->addEditLinkAction(array());
    //     $this->assertTrue(isset($result['edit_qoob']));
    // }

    // // setPost
    // public function testSetPost() {
    //     global $post;
    //     $post_id = $this->qoob->createDefaultPost();
    //     $this->qoob->saveDefaultBlocksData($post_id);
    //     $post = get_post($post_id);
    //     $_GET['post_id'] = null;
    //     $_POST['post_id'] = $post_id;

    //     $this->qoob->setPost();

    //     $this->assertEquals($this->qoob->post_id, $post_id);
    // }

    // // showButton
    // public function testShowButton() {
    //     global $post;
    //     $post_id = $this->qoob->createDefaultPost();
    //     $this->qoob->saveDefaultBlocksData($post_id);
    //     $post = get_post($post_id);
    //     $show = $this->qoob->showButton($post_id);
    //     $this->assertTrue($show);
    // }

    // // addAdminBarLink
    // public function testAddAdminBarLink() {
    //     // set post for page
    //     global $post;

    //     // set custom is_singular check to pass into test
    //     global $wp_query;
    //     $wp_query->is_singular = function($post_types) {
    //         return true;
    //     };

    //     $post_id = $this->qoob->createDefaultPost();
    //     $this->qoob->saveDefaultBlocksData($post_id);
    //     $post = get_post($post_id);

    //     // set admin bar object
    //     require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
    //     global $wp_admin_bar;
    //     $wp_admin_bar = new WP_Admin_Bar();

    //     do_action('admin_bar_menu');
    //     //FIXME: private property 'nodes' changed and we need to check it
    //     $this->assertObjectHasAttribute('nodes', $wp_admin_bar);
    // }
}

// class QoobTest extends WP_UnitTestCase { 

//     public function testAllowInsertEmptyPost() {
//     	$qoob = new MockQoob();
//     	$empty = $qoob->allowInsertEmptyPost(null);
//     	$this->assertEquals($empty, false);
//     }

//     public function testSetTitlePage() {
//         $qoob = new MockQoob();
//         $title = $qoob->setTitlePage();
//         $title_page = "Edit Page with qoob";
//         $this->assertEquals($title, $title_page);
//     }

//     public function testIframeScripts() {
//     	$qoob = new MockQoob();
//     	$qoob->iframeScripts();
//     	$this->assertTrue(wp_script_is('control.edit.page.iframe'));
//     }

//     public function testFrontendScripts() {
//     	$qoob = new MockQoob();
//     	$qoob->frontendScripts();
//     	$this->assertTrue(wp_style_is('qoob.frontend.style'));
//     }

//     public function testAdminScripts() {
//     	$qoob = new MockQoob();
//     	$qoob->setUser();
//     	$post_id = $qoob->createDefaultPost();
//     	$qoob->saveDefaultBlocksData($post_id);
//     	global $post;
//     	$post = get_post($post_id);
//     	$_GET['qoob'] = true;
//     	$qoob->adminScripts();
//     	$this->assertTrue(wp_script_is('qoob.admin'));
//     	$this->assertTrue(wp_style_is('bootstrap'));
//     }

//     public function testLoadScripts() {
//     	$qoob = new MockQoob();
//     	$qoob->loadScripts();
//     	$this->assertTrue(wp_script_is('jquery'));
//     	if(!WP_DEBUG)
// 	    	$this->assertTrue(wp_script_is('qoob'));
//     	else
//     		$this->assertTrue(wp_script_is('bootstrap')); 

//     }

//     //Load qoob data

//     public function testGetUrlQoobTemplates() {
//         $qoob = new MockQoob();
//         $class  = new ReflectionClass($qoob);
//         $method = $class->getMethod('getUrlQoobTemplates');
//         $method->setAccessible(true);//makes the property available
//         $result = $method->invoke($qoob);//calls a function
//         $path =  substr(PLUGIN_PATH, 0, -1) . "/qoob/tmpl/block/block-default-blank.html";
//         $demo = array(
//                         'id' => 'block-default-blank.html',
//                         'url' => $path
//                     );
//         $this->assertEquals($result[0], $demo);
//     }


//     public function testGetTplFiles() {
//     	$qoob = new MockQoob();
//         $class  = new ReflectionClass($qoob);  
//         $method = $class->getMethod('getTplFiles');
//         $method->setAccessible(true);//makes the property available
//         $result = $method->invoke($qoob);//calls a function

//         $getUrlTem = $class->getMethod('getUrlQoobTemplates');
//         $getUrlTem->setAccessible(true);//makes the property available
//         $resGetUrlTem = $getUrlTem->invoke($qoob);//calls a function  

//         $this->assertEquals(array_key_exists('menu-blocks-preview',$result), true);
//         $this->assertEquals(count($result), count($resGetUrlTem));
//     }

//     public function testLoadTmpl() {
//     	$qoob = new MockQoob();
//     	$result = $qoob->loadTmpl(true);
//    		$this->assertTrue(isset($result['qoobTemplate']));
//     }


// }