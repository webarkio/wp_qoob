<?php

class MockQoob extends Qoob {

    public function setUser($type = 'administrator') {
        $user_id = wp_create_user( 'Test', 'test', 'test@gmail.com');
        $wp_user_object = new WP_User($user_id);
        $wp_user_object->set_role($type);
        wp_set_current_user($user_id);
    }

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

    // loadQoobData test
    public function testLoadQoobData() {
        try {
            $this->_handleAjax( 'qoob_load_qoob_data' );
        } catch ( WPAjaxDieContinueException $e ) {}
     
        $response = json_decode( $this->_last_response );
        $this->assertInternalType( 'object', $response );
        $this->assertObjectHasAttribute( 'data', $response );
        $this->assertInternalType( 'object', $response->data );
    }

    // loadSavePageData test
    public function testSavePageData() {
        $post_id = $this->qoob->createDefaultPost();
        $response = $this->qoob->saveDefaultBlocksData($post_id);

        $this->assertInternalType( 'array', $response );
        $this->assertArrayHasKey( 'success', $response );
        $this->assertTrue( $response['success'] );        
    }

    // loadPageData test
    public function testLoadPageData() {
        $_POST['page_id'] = $this->qoob->createDefaultPost();
        $this->qoob->saveDefaultBlocksData($_POST['page_id']);

        try {
            $this->_handleAjax( 'qoob_load_page_data' );
        } catch ( WPAjaxDieContinueException $e ) {}

        $response = json_decode( $this->_last_response );
        $this->assertInternalType( 'object', $response );
        $this->assertObjectHasAttribute( 'data', $response );
        $this->assertEquals($response->data, file_get_contents('tests/phptests/demo_test.txt'));
    }

    // pluginUpdate
    public function testPluginUpdate() {
        $ver = '0.9.0';
        $this->qoob->setVersion($ver);
        delete_option('qoob_version');
        $this->qoob->pluginUpdate();
        $verUp = get_site_option('qoob_version');
        $this->assertEquals($ver, $verUp);
    }
}

// class QoobTest extends WP_UnitTestCase { 



//     // public function testPluginUpdateTo()

//     public function testinfoMetabox() {
//     	$qoob = new MockQoob();
//         $qoob->setUser();
//         $post_id = $qoob->createDefaultPost();
//         $qoob->saveDefaultBlocksData($post_id);

//         // Set global $post variable for use in tested function
//         global $post;
//         global $wp_meta_boxes;
//         $post = get_post($post_id);
//         $qoob->infoMetabox();
//         $this->assertTrue(!is_null($wp_meta_boxes['page']['advanced']['default']['qoob-page-info']));
//     }


//     public function testInfoMetaboxDisplay() {
//         $qoob = new MockQoob();
//         $meta = '<p>Current page has been edited with Qoob Page Builder. To edit this page as regular one - go to Qoob editor by pressing "qoob" button and remove all blocks.</p>';

//         $infometa = $qoob->infoMetaboxDisplay();
//         $metabox = ob_get_contents();
//         ob_clean();
//         $this->assertEquals($metabox, $meta);
//     }

//     public function testSavePostMeta() {
//     	$testMeta=array();
//     	$testMeta[]='Test fot SavePostMeta1';
//     	$testMeta[]='Test fot SavePostMeta2';
//     	$testMeta[]='Test fot SavePostMeta3';

//         $qoob = new MockQoob();

//         $post_id = $qoob->createDefaultPost();
        
//         add_post_meta($post_id, 'qoob_data', $testMeta[0]);
//         $qoob->updateDefaultPost($post_id, "title0");

// 		update_post_meta($post_id, 'qoob_data', $testMeta[1]);
// 		$qoob->updateDefaultPost($post_id, "title1");
        
// 		update_post_meta($post_id, 'qoob_data', $testMeta[2]);
//         $qoob->updateDefaultPost($post_id,"title2");
        

//         $rev = wp_get_post_revisions( $post_id );

//         $key = 1;
//         foreach ($rev as $rev_post_id => $value) {
//         	$meta = get_post_meta($rev_post_id);
//         	$this->assertEquals($meta['qoob_data'][0], $testMeta[count($rev) - $key]);
//         	$key++;
//         }
//     }

//     public function testRestoreRevision() {
//     	$qoob = new MockQoob();

//         $post_id = $qoob->createDefaultPost();
//         $qoob->saveDefaultBlocksData($post_id);

//         $prevMeta = get_post_meta($post_id, 'qoob_data');
//         $prevMeta[0] = stripslashes($prevMeta[0]);

//         $qoob->restoreRevision($post_id, $post_id);

//         $newMeta = get_post_meta($post_id, 'qoob_data');

//         $this->assertEquals($prevMeta, $newMeta);

//         $qoob->restoreRevision($post_id, null);

//         $this->assertNotTrue(get_post_meta($post_id, 'qoob_data'));
        
//     }

//     public function testFilterContent() {
//     	$qoob = new MockQoob();
//  		$qoob->setUser();
//     	$post_id = $qoob->createDefaultPost();
    	
//     	$_GET['qoob'] = true;
//     	define( 'WP_ADMIN', true );
//     	$qoob = new MockQoob();
//     	$this->assertEquals('<div id="qoob-blocks"></div>', $qoob->filterContent());
    	
//     	$_GET['qoob'] = false;
//     	$this->assertEquals('', $qoob->filterContent());

//     	$qoob->saveDefaultBlocksData($post_id);
//         global $post;
//         $post = get_post($post_id);
//     	$this->assertEquals('', $qoob->filterContent());
//     }

//     public function testoOnPageEdit() {
//     	$qoob = new MockQoob();
//     	$qoob->setUser();
//     	$post_id = $qoob->createDefaultPost();
//     	$qoob->saveDefaultBlocksData($post_id);
//     	$_GET['post'] = $post_id;
//     	$qoob->onPageEdit();
//     	$this->assertNotTrue(post_type_supports( 'page', 'editor' ));
//     }

//     public function testGetUrlAssets() {
//         $qoob = new MockQoob();
//         $url = $qoob->getUrlAssets();
//         $url_path = "http://example.org/wp-content/plugins/wp_qoob/assets/";
//         $this->assertEquals($url, $url_path);
//     }

//     public function testGetUrlQoob() {
//         $qoob = new MockQoob();
//         $url = $qoob->getUrlQoob();
//         $url_path = "http://example.org/wp-content/plugins/wp_qoob/qoob/";
//         $this->assertEquals($url, $url_path);
//     }

//     public function testGetPathTemplates() {
//         $qoob = new MockQoob();
//         $template = $qoob->getPathTemplates();
//         $this->assertEquals(!(strstr($template, 'wp_qoob/templates')), false);
//     }

//     public function testSetDefaultTitle() {
//         $qoob = new MockQoob();
//         $title = $qoob->setDefaultTitle('Default');
//         $this->assertEquals($title,'Default');
//     }

//     public function testGetUrlPage() {
//         $qoob = new MockQoob();
//         $url = $qoob->getUrlPage('5');
//         $url_path = "http://example.org/wp-admin/post.php?post_id=5&post_type=&qoob=true";
//         $this->assertEquals($url, $url_path);
//     }

//     public function testAddEditLinkAction() {
//         $qoob = new MockQoob();
//         $qoob->setUser();
//         $result = $qoob->addEditLinkAction('edit');
//         $url_path = "edit";
//         $this->assertEquals($result, $url_path);
//         $post_id = $qoob->createDefaultPost();
//         $qoob->saveDefaultBlocksData($post_id);
//         global $post;
//         $post = get_post($post_id);   
//         $result = $qoob->addEditLinkAction(array());
//         $this->assertTrue(isset($result['edit_qoob']));
//     }

//  //public function testInitEditPage()

//     public function testSetPost() {
//     	$qoob = new MockQoob();
//     	$qoob->setUser();
//     	$post_id = $qoob->createDefaultPost();
//     	$qoob->saveDefaultBlocksData($post_id);
//     	global $post;
//     	$post = get_post($post_id);
//     	$_GET['post_id'] = $post_id;
//     	$qoob->setPost();
//     	$_GET['post_id'] = null;
//     	$_POST['post_id'] = $post_id;
//     	$qoob->setPost();
//     	$this->assertEquals($post, get_post($post_id));
//     }

//     public function testShowButton() {
//         $qoob = new MockQoob();
//         $post_id = $qoob->createDefaultPost();
//         $qoob->saveDefaultBlocksData($post_id);
//         $show = $qoob->showButton($post_id);
//         $this->assertEquals($show, false);
        
//         $qoob->setUser();
//         $show2 = $qoob->showButton($post_id);
//         $this->assertEquals($show2, false);
//     }

//     public function testAddAdminBarLink() {
//     	$qoob = new MockQoob();
//     	$qoob->setUser();
//     	do_action('admin_bar_menu');
//     }

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

//     public function testGetTranslationArray() {
//         $qoob = new MockQoob();
//         $result = $qoob->getTranslationArray();
//         $this->assertTrue(is_array($result) && !empty($result));
//     }

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