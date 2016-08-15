<?php
/*
  Plugin Name: qoob
  Plugin URI: http://qoob.it/
  Description: Qoob - by far the easiest free page builder plugin for WP
  Version: 1.1.3
  Author: webark.io
  Author URI: http://webark.io/
 */

/**
 * This file is part of the Qoob builder package
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     webark.io <info@webark.io>
 * @link       http://qoob.it/
 * @copyright  2015-2016 Webark.io
 * @license    http://qoob.it/LISENCE
 */

class Qoob {
    /**
     * Name shortcode
     */
    const NAME_SHORTCODE = 'qoob-page';
    /**
     * Table name plugin
     * @var string
     */
    /**
     * All items url's
     * @var array
     */
    private $urls = array();
    /**
     * All fields items url's
     * @var array
     */
    private $tmplUrls = array();
    /**
     * Default post types
     * @var string
     */
    private $defaultPostTypes = array('page');
    /**
     * Default post types
     * @var string
     */
    private $qoob_version = '1.1.3';
    /**
     * Register actions for module
     */
    public function __construct() {

        if (is_admin()) {
            // Load backend
            add_action('admin_enqueue_scripts', array($this, 'adminScripts'));
            //Load JS and CSS
            if (isset($_GET['qoob']) && $_GET['qoob'] == true) {
                add_action('current_screen', array($this, 'initEditPage'));
            }
        } else {
            // add edit link to admin bar
            add_action('admin_bar_menu', array(&$this, "addAdminBarLink"), 999);
        }
        // Load js in frame
        if (isset($_GET['qoob']) && $_GET['qoob'] == true) {
            add_filter('the_title', array($this, 'setDefaultTitle'));
            add_action('wp_enqueue_scripts', array($this, 'iframeScripts'));
        }
        add_action('wp_enqueue_scripts', array($this, 'frontendScripts'));
        // Add edit link
        add_filter('page_row_actions', array($this, 'addEditLinkAction'));
        // Adding filter to check content
        add_filter('the_content', array($this, 'filterContent'));
        // Remove tinymce if page was used as Qoob page
        add_action('load-page.php', array($this, 'onPageEdit'));
        // Controlling revisions
        add_action('save_post', array($this, 'savePostMeta'));
        add_action( 'wp_restore_post_revision', array($this, 'restoreRevision'), 10, 2 );
        // Add metabox
        add_action( 'add_meta_boxes', array($this, 'infoMetabox') );
        add_action( 'plugins_loaded', array($this, 'pluginUpdate'), 10, 2);
        // registration ajax actions
        $this->registrationAjax();
        // Creating blocks paths
        $this->blocks_path = is_dir(get_template_directory() . '/blocks') ? (get_template_directory() . '/blocks') : (plugin_dir_path(__FILE__) . 'blocks');
        $this->blocks_url = is_dir(get_template_directory() . '/blocks') ? (get_template_directory_uri() . '/blocks') : (plugin_dir_url(__FILE__) . 'blocks');
    }

    /**
     * Update plugin on migrating between versions
     */
    public function pluginUpdate() {

        if ( get_site_option( 'qoob_version' ) !== $this->qoob_version ) {

            $cur_version = get_site_option( 'qoob_version' ) ? get_site_option( 'qoob_version' ) : '0.0.0';
            
            if ( version_compare( '1.1.0', $cur_version ) > 0 )
                $this->pluginUpdateTo_1_1_0();

            update_option('qoob_version', $this->qoob_version);

        }

    }

    /**
     * Update callback for versions lower then 1.1.0
     */
    public function pluginUpdateTo_1_1_0() {
        global $wpdb;

        if($wpdb->get_var("SHOW TABLES LIKE 'wp_pages'") == 'wp_pages') {
            
            $pids = $wpdb->get_results(
                    "SELECT pid FROM wp_pages" .
                    " GROUP BY pid", "ARRAY_N"
                    );

            for ($i = 0; $i < count($pids); $i++) {
                
                $pid = $pids[$i][0];
                
                if ( !is_null(get_post($pid)) ) {
                    
                    $last_page_node = $wpdb->get_row(
                        "SELECT * FROM wp_pages" .
                        " WHERE pid=" . $pid . " ORDER BY date DESC LIMIT 1", "ARRAY_A"
                        );

                    // Saving received meta
                    update_post_meta( $pid, 'qoob_data', $last_page_node['data'] );
                    
                    // Updating post content
                    $update_args = array(
                      'ID'           => $pid,
                      'post_content' => $last_page_node['html'],
                    );
                    wp_update_post( $update_args );

                }

            }

            $wpdb->query("DROP TABLE wp_pages"); 

        }
    }

    /**
     * Add metabox with info about current Qoob page
    */
    public function infoMetabox() {
        global $post;

        $data = get_post_meta($post->ID, 'qoob_data', true);

        // If have blocks - remove tinymce editor
        if ( $data != '{"blocks":[]}' && $data != '')
            add_meta_box( 'qoob-page-info', __( 'Attention!', 'qoob' ), array($this, 'infoMetaboxDisplay'), 'page' );
    }

    /*
     * Display metabox
    */
    public function infoMetaboxDisplay() {
        echo '<p>Current page has been edited with Qoob Page Builder. To edit this page as regular one - go to Qoob editor by pressing "qoob it" button and remove all blocks.</p>';
    }

     /**
      * Saving post meta to revision
      * @param  int $post_id ID of the current post
      */
    public function savePostMeta($post_id) {
        $parent_id = wp_is_post_revision( $post_id );

        if ( $parent_id ) {

            $parent  = get_post( $parent_id );
            $qoob_data = get_post_meta( $parent->ID, 'qoob_data', true );

            if ( $qoob_data !== false )
                add_metadata( 'post', $post_id, 'qoob_data', $qoob_data );
        }
    }

    /**
     * [restoreRevision description]
     * @param  int $post_id     Post id
     * @param  int $revision_id Current revision id
     */
    public function restoreRevision( $post_id, $revision_id ) {
        $post     = get_post( $post_id );
        $revision = get_post( $revision_id );
        $data  = get_metadata( 'post', $revision->ID, 'qoob_data', true );

        if ( $data !== false )
            update_post_meta( $post_id, 'qoob_data', $data );
        else
            delete_post_meta( $post_id, 'qoob_data' );
    }

    /**
     * Filtering the content for theme templating
     *
     */
    public function filterContent($content = null) {
        $result = '';
        if (is_user_logged_in() && (isset($_GET['qoob']) && $_GET['qoob'] == true) )
            $result = '<div id="qoob-blocks"></div>';
        else {

            global $post;
            $data = get_post_meta($post->ID, 'qoob_data', true);

            // If have blocks - use get_the_content() function. In other way - return basic content
            if ( $data != '{"blocks":[]}' && $data != '') {
                $result = do_shortcode(stripslashes(get_the_content()));
            } else {
                $result = do_shortcode($content);
            } 

        }

        return $result;
    }

    /**
     * Filtering metaboxes on page edit screen
     */
    public function onPageEdit() {
        if (isset($_GET['post'])) {

            // Getting qoob_html from metadata
            $post_id =  $_GET['post'];
            $data = get_post_meta($post_id, 'qoob_data', true);

            // If have blocks - remove tinymce editor
            if ( $data != '{"blocks":[]}' && $data != '') {
                remove_post_type_support('page', 'editor');
            }

        }
    }

    /**
     * Registration ajax actions
     */
    public function registrationAjax() {

        add_action('wp_ajax_qoob_load_page_data', array($this, 'loadPageData'));
        add_action('wp_ajax_qoob_load_data', array($this, 'loadData'));
        add_action('wp_ajax_qoob_load_item', array($this, 'loadItem'));
        add_action('wp_ajax_qoob_save_page_data', array($this, 'savePageData'));
        add_action('wp_ajax_qoob_load_tmpl', array($this, 'loadTmpl'));
        add_action('wp_ajax_load_blocks_scripts', array($this, 'loadBlocksAssets'));
        add_action('wp_ajax_load_blocks_styles', array($this, 'loadBlocksAssets'));
        add_action('wp_ajax_nopriv_load_blocks_scripts', array($this, 'loadBlocksAssets'));
        add_action('wp_ajax_nopriv_load_blocks_styles', array($this, 'loadBlocksAssets'));
    
    }

    /**
     * Concating parsing and loading styles and scripts,
     * that qoob blocks contain in their assets
     *
     */
    public function loadBlocksAssets() {
        if ($_GET['action'] === 'load_blocks_scripts') {
            $type = 'script';
        } else if (($_GET['action'] === 'load_blocks_styles')) {
            $type = 'style';
        }

        $qoob_scripts = '';
        $blocks_path = is_dir(get_template_directory() . '/blocks') ? (get_template_directory() . '/blocks') : (plugin_dir_path(__FILE__) . 'blocks');
        $blocks_url = is_dir(get_template_directory() . '/blocks') ? (get_template_directory_uri() . '/blocks') : (plugin_dir_url(__FILE__) . 'blocks');

        $directory = new DirectoryIterator($blocks_path);

        foreach ($directory as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                // masks urls
                $theme_url = get_template_directory_uri();
                $block_url = $blocks_url . '/' . $file->getFilename();
                // get block's config file
                $config_json = file_get_contents($block_url . '/config.json');

                // parsing config masks            
                $config_json = preg_replace('/%theme_url%/', $theme_url, $config_json);
                $config_json = preg_replace('/%block_url%/', $block_url, $config_json);
                $config_json = preg_replace('/%blocks_url%/', $blocks_url, $config_json);
                // getting assets
                $config = json_decode($config_json, true);
                if (isset($config['assets'])) {
                    $assets = $config['assets'];
                    for ($i = 0; $i < count($assets); $i++) {
                        if ($assets[$i]['type'] === $type) {
                            
                            // parsing styles masks
                            $script = file_get_contents($assets[$i]['src']);
                            $script = preg_replace('/%theme_url%/', $theme_url, $script);
                            $script = preg_replace('/%block_url%/', $block_url, $script);
                            $script = preg_replace('/%blocks_url%/', $blocks_url, $script);
                            $qoob_scripts .= $script;
                        }
                    }
                }
            }
        }
        if ($type === 'script') {
            header('Content-Type: application/javascript');
        } else if ($type === 'style') {
            header('Content-Type: text/css');
        }
        // printing scripts
        echo $qoob_scripts;
        die();
    }
    /**
     * Get current module assets directory URL
     * 
     * This method is easiest way to make link to assets file in module. This is 
     * very useful method, because qoob builder can work the same as plugin and 
     * as part of theme. 
     * 
     * <pre><code>$qoob = new Qoob();
     * $url = $qoob->getUrlAssets(); //Get assets Url with path ABSPATH
     * wp_enqueue_script("somefile", $url . "/js/somefile.js"); //add core script
     * 
     * </code></pre>
     * 
     * @return String Url to assets directory of current module
     */
    public function getUrlAssets() {
        return plugin_dir_url( __FILE__) . "assets/";
    }
    /**
     * Get current module qoob directory URL
     * 
     * This method is easiest way to make link to qoob file in module.
     * 
     * <pre><code>$qoob = new Qoob();
     * $url = $qoob->getUrlQoob(); //Get assets Url with path ABSPATH
     * wp_enqueue_script("somefile", $url . "/js/somefile.js"); //add core script
     * 
     * </code></pre>
     * 
     * @return String Url to assets directory of current module
     */
    public function getUrlQoob() {
        return plugin_dir_url( __FILE__) . "qoob/";
    }
    /**
     * Get path to current module templates dir
     * 
     * This method is easiest way to find out where is module templates located.
     * This is very useful method, because qoob builder can work the same as 
     * plugin and as part of theme. If your module has different templates 
     * directory, method getPathTemplates() should be overwritten.
     * 
     * <pre><code>
     * $template = $self->getPathTemplates(). DIRECTORY_SEPARATOR. "template.php"; //path to template file
     * echo QoobtUtils::template($template); //apply template
     * </code></pre>
     * 
     * @return String Path to templates directory
     */
    public function getPathTemplates() {
        return plugin_dir_path( __FILE__ ) . "templates" . DIRECTORY_SEPARATOR;
    }
    /**
     * Set default title for page
     *
     * @param string $title
     * @return string|void
     */
    public function setDefaultTitle($title) {
        return !is_string($title) || strlen($title) == 0 ? __('(no title)', 'qoob') : $title;
    }
    /**
     * Get qoob url edit page 
     *
     * @param string $id Post id
     * @return string
     */
    public function getUrlPage($id) {
        return admin_url() . 'post.php?post_id=' . $id . '&post_type=' . get_post_type($id) . '&qoob=true';
    }
    /**
     * Add link to posts list
     * 
     * @param $actions
     * @return mixed
     */
    public function addEditLinkAction($actions) {
        //TODO: check if page has qoob shortcode show Edit with qoob
        $post = get_post();
        $id = (strlen($post->ID) > 0 ? $post->ID : get_the_ID());
        $url = $this->getUrlPage($id);
        //Check for qoob page
        $meta = get_post_meta($id, 'qoob_data', true);
        if ($meta != '{"blocks":[]}' && $meta != '') {
            return array('edit_qoob' => '<a href="' . $url . '">' . __('Edit with qoob it', 'qoob') . '</a>') + $actions;
        } else {
            return $actions;
        }
    }
    /**
     * Initialize page qoob
     */
    public function initEditPage() {
        $this->setPost();
        $this->renderPage();
    }
    /**
     * Set post data for page
     *
     * @global object $post
     */
    public function setPost() {
        global $post;
        $this->post = get_post();
        $this->post_id = isset($_GET['post_id']) ? $_GET['post_id'] : '';
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : '';
        if ($post_id) {
            $this->post_id = $post_id;
        }
        if ($this->post_id) {
            $this->post = get_post($this->post_id);
        }
        do_action_ref_array('the_post', array(&$this->post));
        $post = $this->post;
        $this->post_id = $this->post->ID;
    }
    /**
     * Get state show button
     * @param null $post_id
     * @return bool
     */
    public function showButton($post_id = null) {
        wp_get_current_user();
        if (!current_user_can('edit_post', $post_id)) {
            return false;
        }
        return in_array(get_post_type(), $this->defaultPostTypes);
    }
    /**
     * Add link to admin bar
     * @param WP_Admin_Bar $wp_admin_bar
     */
    public function addAdminBarLink($wp_admin_bar) {
        if (!is_object($wp_admin_bar)) {
            global $wp_admin_bar;
        }
        if (is_singular()) {
            if ($this->showButton(get_the_ID())) {
                $wp_admin_bar->add_menu(array(
                    'id' => 'qoob-admin-bar-link',
                    'title' => __('qoob it', "qoob"),
                    'href' => $this->getUrlPage(get_the_ID()),
                    'meta' => array('class' => 'qoob-inline-link')
                ));
            }
        }
    }
    /**
     * Used for wp filter 'wp_insert_post_empty_content' to allow empty post insertion.
     *
     * @param $allow_empty
     * @return bool
     */
    public function allowInsertEmptyPost($allow_empty) {
        return false;
    }
    /**
     * Create qoob page
     *
     * @global object $current_user
     */
    public function renderPage() {
        global $current_user;
        global $post;
        wp_get_current_user();
        //TODO: check for data custom field
        $this->current_user = $current_user;
        $this->post_url = str_replace(array('http://', 'https://'), '//', get_permalink($this->post_id));
        if (!current_user_can('edit_post', $this->post_id)) {
            header('Location: ' . $this->post_url);
        }
        if ($this->post && $this->post->post_status === 'auto-draft') {
            $post_data = array(
                'ID' => $this->post_id,
                'post_status' => 'publish',
                'post_title' => ''
            );
            add_filter('wp_insert_post_empty_content', array($this, 'allowInsertEmptyPost'));
            wp_update_post($post_data, true);
            $this->post->post_status = 'draft';
            $this->post->post_title = '';
        }
        $this->post_type = get_post_type_object($this->post->post_type);
        wp_enqueue_media(array('post' => $this->post_id));
        remove_all_actions('admin_notices', 3);
        remove_all_actions('network_admin_notices', 3);
        add_filter('user_can_richedit', '__return_false');
        //Load JS and CSS for frontend
        add_action('admin_enqueue_scripts', array($this, 'loadScripts'));
        add_filter('admin_title', array($this, 'setTitlePage'));
        is_array($this) && extract($this);
        require_once $this->getPathTemplates() . 'template.php';
        die();
    }
    /**
     * Set title edit page
     *
     * @return string
     */
    public function setTitlePage() {
        return __('Edit Page with qoob', 'qoob');
    }
    /**
     * Load javascript and css on iframe
     */
    public function iframeScripts() {
        // Load style
        wp_enqueue_style('qoob.iframe.style', $this->getUrlQoob() . "css/qoob.css");
        // Load js
        wp_enqueue_script('control.edit.page.iframe', $this->getUrlAssets() . 'js/control-edit-page-iframe.js', array('jquery'), '', true);
    }
    /**
     * Load js and css on frontend pages
     */
    public function frontendScripts() {
        // load qoob blocks asset's styles
        $this->loadAssetsScripts();
        // load qoob styles
        wp_enqueue_style('qoob.frontend.style', $this->getUrlAssets() . "css/qoob.css");

    }
    /**
     * Load javascript and css on admin page
     *
     */
    public function adminScripts() {
        if (get_post_type() == 'page') {
            wp_enqueue_script('qoob.admin', $this->getUrlAssets() . 'js/qoob-admin.js', array('jquery'), '', true);
            wp_enqueue_style('qoob.admin.style', $this->getUrlAssets() . "css/qoob-admin.css");
            if (isset($_GET['qoob']) && $_GET['qoob'] == true) {
                wp_enqueue_style('wheelcolorpicker-minicolors', $this->getUrlQoob() . "css/wheelcolorpicker.css");
                wp_enqueue_style('bootstrap', $this->getUrlQoob() . "css/bootstrap.min.css");
                wp_enqueue_style('bootstrap-select', $this->getUrlQoob() . "css/bootstrap-select.min.css");
            }
        }
    }
    /**
     * Load javascript and css for qoob page
     */
    public function loadScripts() {
        // add ajax url
        $url = add_query_arg('qoob', 'true', $this->post_url);
        wp_localize_script('jquery', 'ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'logged_in' => is_user_logged_in(),
            'iframe_url' => $url,
            'theme_url' => get_template_directory_uri(),
            'qoob' => ( isset($_GET['qoob']) && $_GET['qoob'] == true ? true : false )
                )
        );
        // qoob styles
        wp_enqueue_style('qoob-style', $this->getUrlQoob() . "css/qoob.css");
        // load qoob blocks asset's styles
        $this->loadAssetsScripts();
        // core libs
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('jquery-touch-punch');
        wp_enqueue_script('underscore');
        wp_enqueue_script('backbone');
        wp_enqueue_script('qoob-tinymce', $this->getUrlQoob() . 'js/libs/tinymce/tinymce.min.js', array('jquery'), '', true);
        if (!WP_DEBUG) {
            wp_enqueue_script('qoob', $this->getUrlQoob() . '/qoob.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'backbone', 'underscore'));
        } else {
            wp_enqueue_script('control_edit_page', $this->getUrlAssets() . 'js/control-edit-page.js', array('qoob'), '', true);
            wp_enqueue_script('qoob-wordpress-driver', $this->getUrlAssets() . 'js/qoob-wordpress-driver.js', array('jquery'), '', true);
            wp_enqueue_script('handlebars', $this->getUrlQoob() . 'js/libs/handlebars.js', array('jquery'), '', true);
            wp_enqueue_script('handlebars-helper', $this->getUrlQoob() . 'js/libs/handlebars-helper.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-ui-droppable-iframe', $this->getUrlQoob() . 'js/libs/jquery-ui-droppable-iframe.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-wheelcolorpicker', $this->getUrlQoob() . 'js/libs/jquery.wheelcolorpicker.js', array('jquery'), '', true);
            wp_enqueue_script('bootstrap', $this->getUrlQoob() . 'js/libs/bootstrap.min.js', array('jquery'), '', true);
            wp_enqueue_script('bootstrap-select', $this->getUrlQoob() . 'js/libs/bootstrap-select.min.js', array('jquery', 'bootstrap'), '', true);
            wp_enqueue_script('bootstrap-progressbar', $this->getUrlQoob() . 'js/libs/bootstrap-progressbar.js', array('jquery', 'bootstrap'), '', true);
            // Application scripts
            wp_enqueue_script('block-view', $this->getUrlQoob() . 'js/views/qoob-block-view.js', array('jquery'), '', true);
            wp_enqueue_script('block-wrapper-view', $this->getUrlQoob() . 'js/views/qoob-block-wrapper-view.js', array('jquery'), '', true);
            wp_enqueue_script('field-text', $this->getUrlQoob() . 'js/views/fields/field-text.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-text-autocomplete', $this->getUrlQoob() . 'js/views/fields/field-text-autocomplete.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-checkbox', $this->getUrlQoob() . 'js/views/fields/field-checkbox.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-checkbox-switch', $this->getUrlQoob() . 'js/views/fields/field-checkbox-switch.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-select', $this->getUrlQoob() . 'js/views/fields/field-select.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-texarea', $this->getUrlQoob() . 'js/views/fields/field-textarea.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-slider', $this->getUrlQoob() . 'js/views/fields/field-slider.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-image', $this->getUrlQoob() . 'js/views/fields/field-image.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-colorpicker', $this->getUrlQoob() . 'js/views/fields/field-colorpicker.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-accordion', $this->getUrlQoob() . 'js/views/fields/field-accordion.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-accordion-item', $this->getUrlQoob() . 'js/views/fields/field-accordion-item-expand.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-accordion-item-front', $this->getUrlQoob() . 'js/views/fields/field-accordion-item-flip.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('fields-view', $this->getUrlQoob() . 'js/views/qoob-fields-view.js', array('jquery'), '', true);
            wp_enqueue_script('page-model', $this->getUrlQoob() . 'js/models/page-model.js', array('jquery'), '', true);
            wp_enqueue_script('block-model', $this->getUrlQoob() . 'js/models/block-model.js', array('jquery'), '', true);
            wp_enqueue_script('field-view', $this->getUrlQoob() . 'js/views/qoob-field-view.js', array('jquery'), '', true);
            wp_enqueue_script('qoob-menu-groups-view', $this->getUrlQoob() . 'js/views/qoob-menu-groups-view.js', array('jquery'), '', true);
            wp_enqueue_script('settings-view', $this->getUrlQoob() . 'js/views/qoob-settings-view.js', array('jquery'), '', true);
            wp_enqueue_script('qoob-menu-blocks-preview-view', $this->getUrlQoob() . 'js/views/qoob-menu-blocks-preview-view.js', array('jquery'), '', true);
            wp_enqueue_script('qoob-layout', $this->getUrlQoob() . 'js/views/qoob-layout.js', array('jquery'), '', true);
            wp_enqueue_script('qoob-menu-view', $this->getUrlQoob() . 'js/views/qoob-menu-view.js', array('jquery'), '', true);
            wp_enqueue_script('qoob-toolbar-view', $this->getUrlQoob() . 'js/views/qoob-toolbar-view.js', array('jquery'), '', true);
            wp_enqueue_script('qoob-edit-mode-button-view', $this->getUrlQoob() . 'js/views/qoob-edit-mode-button-view.js', array('jquery'), '', true);
            wp_enqueue_script('qoob-viewport-view', $this->getUrlQoob() . 'js/views/qoob-viewport-view.js', array('jquery'), '', true);
            wp_enqueue_script('qoob-controller', $this->getUrlQoob() . 'js/controllers/qoob-controller.js', array('jquery'), '', true);
            wp_enqueue_script('field-accordion-item-flip-view', $this->getUrlQoob() . 'js/views/fields/field-accordion-item-flip-settings.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('image-center-view', $this->getUrlQoob() . 'js/views/fields/image-center-view.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-video-view', $this->getUrlQoob() . 'js/views/fields/field-video.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('video-center-view', $this->getUrlQoob() . 'js/views/fields/video-center-view.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('field-icon-view', $this->getUrlQoob() . 'js/views/fields/field-icon.js', array('jquery', 'field-view'), '', true);
            wp_enqueue_script('icon-center-view', $this->getUrlQoob() . 'js/views/fields/icon-center-view.js', array('jquery', 'field-view'), '', true);
            // qoob scripts
            wp_enqueue_script('qoob-loader', $this->getUrlQoob() . 'js/qoob-loader.js', array('jquery'), '', true);
            wp_enqueue_script('qoob-storage', $this->getUrlQoob() . 'js/qoob-storage.js', array('jquery'), '', true);
            wp_enqueue_script('qoob-utils', $this->getUrlQoob() . 'js/qoob-utils.js', array('jquery'), '', true);
            wp_enqueue_script('qoob', $this->getUrlQoob() . 'js/qoob.js', array('jquery'), '', true);
            wp_enqueue_script('handlebar-extension', $this->getUrlQoob() . 'js/extensions/template-adapter-handlebars.js', array('handlebars'), '', true);
            wp_enqueue_script('underscore-extension', $this->getUrlQoob() . 'js/extensions/template-adapter-underscore.js', array('underscore'), '', true);

        }

    }

    /**
     * Load data page
     * @return json
     */
    public function loadPageData() { 
        $page_id = $_POST['page_id'];
        $data = get_post_meta($page_id, 'qoob_data', true);

        // Send decoded page data to the Qoob editor page
        if ($data != '') {

            $data = stripslashes_deep(json_decode($data, true));

            $response = array(
                'success' => true,
                'data' => $data
            );

        } else {

            $response = array('success' => false);

        }

        wp_send_json($response);
        exit();
    }

    /**
     * Save data page
     * @return json
     */
    public function savePageData() {
        // Checking for administration rights
        if (!current_user_can('manage_options')) {
            return;
        }
        global $wpdb;
        $post_data = json_decode(file_get_contents('php://input'), true);
        $blocks_html = trim($post_data['blocks']['html']);
        $post_id = $post_data['page_id'];
        $qoob_data = wp_slash( json_encode( isset($post_data['blocks']['data']) ? $post_data['blocks']['data'] : '' ) );

        // Saving metafield
        update_post_meta( $post_id, 'qoob_data', $qoob_data );

        // Updating post content and post content filtered
        $update_args = array(
          'ID'           => $post_id,
          'post_content' => $blocks_html,
        );
        $updated = wp_update_post( $update_args );

        $responce = array('success' => (boolean) $updated);
        wp_send_json($responce);
        exit();
    }
    /**
     * Get url items
     * @return array
     */
    private function getUrlItems() {
        if (!empty($this->urls)) {
            return $this->urls;
        }
        $path = $this->blocks_path;
        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot())
                continue;
            if ($file->isDir()) {
                $url = $this->blocks_url . '/' . $file->getFilename() . '/';
                $this->urls[] = array(
                    'id' => $file->getFilename(),
                    'url' => $url
                );
            }
        }
        return $this->urls;
    }
    /**
     * Get url qoob templates
     * @return array
     */
    private function getUrlQoobTemplates() {
        if (!empty($this->tmplUrls)) {
            return $this->tmplUrls;
        }
        $path = plugin_dir_path( __FILE__ ) . 'qoob/tmpl';
        foreach (new DirectoryIterator($path) as $folder) {
            if ($folder->isDot())
                continue;
            if (is_dir($folder->getPathname())) {
                $pathtofiles = $path. '/' . $folder->getFilename();
                foreach (new DirectoryIterator($pathtofiles) as $file) {
                    if ($file->isDot())
                        continue;
                    $filename = $file->getFilename();
                    $url = $path . '/' . $folder->getFilename() . '/' . $file->getFilename();
                    $this->tmplUrls[] = array(
                        'id' => $file->getFilename(),
                        'url' => $url
                    );
                }
            }
        }
        return $this->tmplUrls;
    }
    /**
     * Get all blocks in folder
     * @return array
     */
    private function getItems() {
        if (isset($this->items)) {
            return $this->items;
        }
        $items = array();
        $urls = $this->getUrlItems();
        foreach ($urls as $val) {
            $theme_url = get_template_directory_uri();
            $blocks_url = $this->blocks_url;
            $block_url = substr($val['url'], 0, -1);
            
            $config_json = file_get_contents($block_url . '/' . 'config.json');
            //Parsing for url masks to replace            
            $config_json = preg_replace('/%theme_url%/', $theme_url, $config_json);
            $config_json = preg_replace('/%block_url%/', $block_url, $config_json);
            $config_json = preg_replace('/%blocks_url%/', $blocks_url, $config_json);
            //Decoding json config
            $config = json_decode($config_json, true);
            $config['id'] = $val['id'];
            $config['url'] = $val['url'];
            $items[] = $config;
        }
        // cash blocks for next ajax request
        $this->items = $items;
        return $items;
    }
    /**
     * Get block by $id
     * 
     * @param type $id
     * @param type $array
     * @return null|array
     */
    private function getItem($id) {
        $templates = $this->getUrlItems();
        foreach ($templates as $key => $val) {
            if ($val['id'] === $id) {
                return $val;
            }
        }
        return null;
    }
    /**
     * Get groups blocks
     * @return array
     */
    private function getGroups() {
        $json = file_get_contents($this->blocks_url . '/groups.json');
        $json = json_decode($json, true);
        return $json;
    }
    /**
     * Enqueueing scripts and styles, contained in theme block's assets
     * 
     * @param Array $blocks blocks that contain wordpress theme
     */
    private function loadAssetsScripts() {
        wp_enqueue_style('blocks-custom-styles', admin_url('admin-ajax.php') . '?action=load_blocks_styles');
        wp_enqueue_script('blocks-custom-scripts', admin_url('admin-ajax.php') . '?action=load_blocks_scripts', array('jquery'));
    }
    
    /**
     * Load qoob data
     * @return json
     */
    public function loadData() {
        $blocks = $this->getItems();
        $groups = $this->getGroups();
        if (isset($blocks)) {
            $response = array(
                'success' => true,
                'data' => array(
                    'items' => $blocks,
                    'groups' => $groups
                )
            );
        } else {
            $response = array('success' => false);
        }
        wp_send_json($response);
        exit();
    }
    /**
     * Get qoob tmpl files contents
     * @return array $tmpl Array of config's json
     */
    private function getTplFiles() {
        $tmpl = array();
        $urls = $this->getUrlQoobTemplates();
        foreach ($urls as $val) {
            $html_content = file_get_contents($val['url']);
            $id = str_replace('.html', '', $val['id']);
            $tmpl[$id] = $html_content;
        }
        return $tmpl;
    }
    /**
     * Get content hbs file's
     * @return html
     */
    private function getHtml($itemId) {
        $item = $this->getItem($itemId);
        $config = json_decode(file_get_contents($item['url'] . 'config.json'));
        return file_get_contents($item['url'] . $config->template);
    }
    /**
     * Load item
     * @return html
     */
    public function loadItem() {
        $item = $this->getHtml($_POST['item_id']);
        echo $item;
        exit();
    }
    /**
     * Loading all qoob's templates  
     */
    public function loadTmpl() {
        $templates = $this->getTplFiles();
        if (isset($templates)) {
            $tmpl = array();
            foreach ($templates as $key => $value) {
                $tmpl[$key] = $value;
            }
            $response = array(
                'success' => true,
                'qoobTemplate' => $tmpl
            );
        } else {
            $response = array('success' => false);
        }
        wp_send_json($response);
        exit();
    }

    /**
    * Get scripts or styles, contained in theme block's assets
    * @param string $assets_type Asset's type
    * @return scring
    */
    public function load_blocks_scripts($assets_type) {
        $qoob_scripts = '';
        $blocks_path = $this->blocks_path;
        $blocks_url = $this->blocks_url;

        $directory = new DirectoryIterator($blocks_path);

        foreach ($directory as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                // masks urls
                $theme_url = get_template_directory_uri();
                $block_url = $blocks_url . '/' . $file->getFilename();
                // get block's config file
                $config_json = file_get_contents($block_url . '/config.json');

                // parsing config masks            
                $config_json = preg_replace('/%theme_url%/', $theme_url, $config_json);
                $config_json = preg_replace('/%block_url%/', $block_url, $config_json);
                $config_json = preg_replace('/%blocks_url%/', $blocks_url, $config_json);
                // getting assets
                $config = json_decode($config_json, true);
                if (isset($config['assets'])) {
                    $assets = $config['assets'];
                    for ($i = 0; $i < count($assets); $i++) {
                        if ($assets[$i]['type'] === $assets_type) {
                            
                            // parsing styles masks
                            
                            $script = file_get_contents($assets[$i]['src']);
                            $script = preg_replace('/%theme_url%/', $theme_url, $script);
                            $script = preg_replace('/%block_url%/', $block_url, $script);
                            $script = preg_replace('/%blocks_url%/', $blocks_url, $script);
                            $qoob_scripts .= $script;
                        }
                    }
                }
            }
        }
        // printing scripts
        return $qoob_scripts;
    }
}

$qoob = new Qoob();