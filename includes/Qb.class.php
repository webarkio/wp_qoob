<?php

/**
 * This file is part of the CubeBuilder package
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     webark.io <info@webark.io>
 * @link       http://cube.webark.io/
 * @copyright  2014-2015 Webark.io
 * @license    http://cube.webark.io/LISENCE
 */
//require_once('SmartModule.class.php');

/**
 * Cube
 *
 *
 * @package    CubeBuilder
 * @version    @package_version@
 */
class Qb {

    /**
     * Name shortcode
     */
    const NAME_SHORTCODE = "apage";

    /**
     * Table name plugin
     * @var string
     */
    var $qb_table_name;

    /**
     * Register actions for module
     */
    public function register() {
        // Create table in DB
        $this->creater_db_table();

        // Load backend
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

            //Load JS and CSS
            if (isset($_GET['qb']) && $_GET['qb'] == true) {
                add_action('current_screen', array($this, 'initEditPage'));
            }
        }

        // Load js in frame
        if (isset($_GET['qb']) && $_GET['qb'] == true) {
            add_filter('the_title', array($this, 'setDefaultTitle'));
            add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        }

        // Registration ajax
        add_action('wp_ajax_load_page_data', array($this, 'loadPageData'));
        add_action('wp_ajax_load_builder_data', array($this, 'loadBuilderData'));
        add_action('wp_ajax_load_settings', array($this, 'loadSettings'));
        add_action('wp_ajax_load_template', array($this, 'loadTemplate'));
        add_action('wp_ajax_save_page_data', array($this, 'savePageData'));

        // Add edit link
        add_filter('page_row_actions', array($this, 'addEditLinkAction'));

        //register shortcode
        add_shortcode(self::NAME_SHORTCODE, array($this, 'add_shortcode'));
    }

    /**
     * Get path of current file
     * 
     * @return string full path to current or extended file
     */
    protected function getPathCurrentFile() {
        $class = new ReflectionClass($this);
        return $class->getFileName();
    }

    /**
     * Get current module path
     * 
     * This method is easiest way to find out where is module located. This is 
     * very useful method, because smartbuilder can work the same as plugin and 
     * as part of theme. 
     * 
     * <pre><code>global $smart;
     * $path = $smart->getPath(); //Get path of core module
     * 
     * $path = $smart->module('SmartComponentRow')->getPath(); //Get path of row component
     * </code></pre>
     * 
     * @return String Path to root of current module
     */
    public function getPath() {
        return realpath(dirname($this->getPathCurrentFile()) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Get path to current module assets dir
     * 
     * This method is easiest way to find out where is module assets located.
     * This is very useful method, because smartbuilder can work the same as 
     * plugin and as part of theme. If your module has different assets 
     * directory, method getPathAssets() should be overwritten.
     * 
     * <pre><code>global $smart;
     * $path = $smart->getPathAssets(); //Get assets path of core module
     * 
     * $path = $smart->module('SmartComponentRow')->getPathAssets(); //Get assets path of row component
     * </code></pre>
     * 
     * @return String Path to assets direcroty
     */
    public function getPathAssets() {
        return $this->getPath() . "assets" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get current module assets directory URL
     * 
     * This method is easiest way to make link to assets file in module. This is 
     * very useful method, because smartbuilder can work the same as plugin and 
     * as part of theme. 
     * 
     * <pre><code>global $smart;
     * $url = $smart->getUrlAssets(); //Get assets Url with path ABSPATH
     * $url = $smart->getUrlAssets(true); //Get assets Url with path $_SERVER['DOCUMENT_ROOT']
     * 
     * wp_enqueue_script("somefile", $url . "/js/somefile.js"); //add core script
     * 
     * </code></pre>
     * 
     * @return String Url to assets directory of current module
     */
    public function getUrlAssets() {

        return SmartUtils::getUrlFromPath($this->getPathAssets());
    }

    /**
     * Get path to current module templates dir
     * 
     * This method is easiest way to find out where is module templates located.
     * This is very useful method, because smartbuilder can work the same as 
     * plugin and as part of theme. If your module has different templates 
     * directory, method getPathTemplates() should be overwritten.
     * 
     * <pre><code>global $smart;
     * $template = $smart->getPathTemplates(). DIRECTORY_SEPARATOR. "template.php"; //path to template file
     * echo SmartUtils::template($template); //apply template
     * </code></pre>
     * 
     * @return String Path to templates directory
     */
    public function getPathTemplates() {
        return $this->getPath() . "templates" . DIRECTORY_SEPARATOR;
    }

    /**
     * Set default title for page
     *
     * @param string $title
     * @return string|void
     */
    public function setDefaultTitle($title) {
        return !is_string($title) || strlen($title) == 0 ? __('(no title)', 'qb') : $title;
    }

    /**
     * Add link to posts list
     * 
     * @param $actions
     * @return mixed
     */
    public function addEditLinkAction($actions) {
        $post = get_post();
        $id = (strlen($post->ID) > 0 ? $post->ID : get_the_ID());
        $url = admin_url() . 'post.php?qb=true&post_id=' . $id . '&post_type=' . get_post_type($id);

        $actions['edit_qb'] = '<a href="' . $url . '">' . __('qb editor', 'qb') . '</a>';

        return $actions;
    }

    /**
     * Initialize page builder
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
     * Used for wp filter 'wp_insert_post_empty_content' to allow empty post insertion.
     *
     * @param $allow_empty
     * @return bool
     */
    public function allowInsertEmptyPost($allow_empty) {
        return false;
    }

    /**
     * Add shortcode to content
     *
     * @param int $pageId
     */
    private function addShortcodeToContent($pageId) {
        $post_data = array(
            'ID' => $this->post_id,
            'post_status' => ($this->post->post_status == 'publish' ? 'publish' : 'draft'),
            'post_title' => ($this->post->post_title != '' ? $this->post->post_title : ''),
            'post_content' => '[' . self::NAME_SHORTCODE . ' id="' . $pageId . '"]',
        );

        // Update the post into the database
        wp_update_post($post_data);
    }

    /**
     * Create new pageId
     *
     * @return int id new page
     */
    private function createPageId() {
        global $wpdb;
        $wpdb->insert($this->qb_table_name, array(
            'data' => '',
            'html' => ''
        ));

        $pageId = $wpdb->insert_id;

        $this->addShortcodeToContent($pageId);

        return $pageId;
    }

    /**
     * Get id from shortcode attr
     *
     * @param string $post_content
     * @return string id from page
     */
    private function getPageId($post_content) {
        $id = null;
        preg_match('/\[' . self::NAME_SHORTCODE . '.*id=.(.*).\]/', $post_content, $id);

        if (empty($id)) {
            return $this->createPageId();
        } else {
            return $id[1];
        }
    }

    /**
     * Create builder page
     *
     * @global object $current_user
     */
    public function renderPage() {
        global $current_user;
        get_currentuserinfo();

        $this->pageId = $this->getPageId($this->post->post_content);
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

        //Load JS and CSS for frontend
        add_action('admin_enqueue_scripts', array($this, 'load_builder_scripts'));

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
        return __('Edit Page with qb', 'qb');
    }

    /**
     * Get block from id
     *
     * @global object $wpdb
     * @param type int
     * @return string html
     */
    private function getBlock($id) {
        global $wpdb;
        $block = $wpdb->get_row("SELECT * FROM " . $this->qb_table_name . " WHERE pid = " . $id . "");
        return $block;
    }

    /**
     * Create shortcode
     * @param array $atts An associative array of attributes, or an empty string if no attributes are given
     * @param string $content The enclosed content (if the shortcode is used in its enclosing form)
     * @return string Html code our shortcode
     */
    public function add_shortcode($atts, $content = null) {
        if (is_user_logged_in() && ( isset($_GET['qb']) && $_GET['qb'] == true)) {
            return $this->addMainBuilderBlock();
        } else {
            $block = $this->getBlock($atts['id']);
            return stripslashes($block->html);
        }
    }

    /**
     * Create plugin table
     *
     * @global object $wpdb
     */
    public function creater_db_table() {
        global $wpdb;

        $this->qb_table_name = $wpdb->prefix . "pages";
        if ($wpdb->get_var("show tables like '$this->qb_table_name'") != $this->qb_table_name) {
            $sql = "CREATE TABLE " . $this->qb_table_name . " (
                pid int(9) NOT NULL AUTO_INCREMENT,
                data text NOT NULL,
                html text NOT NULL,
                PRIMARY KEY (pid),
                KEY pid(pid)
            );";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Load javascript and css on iframe
     */
    public function frontend_scripts() {
        // Load style
        wp_enqueue_style('builder.qb.iframe', $this->getUrlAssets() . "css/iframe-builder.css");

        // Load js
        wp_enqueue_script('control.edit.page.iframe', $this->getUrlAssets() . 'js/control-edit-page-iframe.js', array('jquery'), '', true);
    }

    /**
     * Load javascript and css on admin page
     *
     */
    public function admin_scripts() {
        if (get_post_type() == 'page') {
            wp_enqueue_script('builder.admin', $this->getUrlAssets() . 'js/builder-admin.js', array('jquery'), '', true);
            wp_enqueue_script('waves.min', $this->getUrlAssets() . 'js/libs/waves.min.js', array('builder.admin'), '', true);
            wp_enqueue_style('waves.min', $this->getUrlAssets() . "css/waves.min.css");
            wp_enqueue_style('builder.qb.iframe', $this->getUrlAssets() . "css/builder-admin.css");
            
            
        }
    }

    /**
     * Load javascript and css for builder page
     */
    public function load_builder_scripts() {
        // add ajax url
        wp_localize_script('jquery', 'ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'logged_in' => is_user_logged_in(),
            'qb' => ( isset($_GET['qb']) && $_GET['qb'] == true ? true : false )
                )
        );

        // core libs
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-touch-punch');
        wp_enqueue_script('underscore');
        wp_enqueue_script('backbone');
        wp_enqueue_script('handlebars', $this->getUrlAssets() . 'js/libs/handlebars.js', array('jquery'), '', true);
        wp_enqueue_script('handlebars-helper', $this->getUrlAssets() . 'js/libs/handlebars-helper.js', array('jquery'), '', true);
        wp_enqueue_script('jquery-ui-droppable-iframe', $this->getUrlAssets() . 'js/libs/jquery-ui-droppable-iframe.js', array('jquery'), '', true);
        wp_enqueue_script('perfect-scrollbar', $this->getUrlAssets() . 'js/libs/perfect-scrollbar.jquery.js', array('jquery'), '', true);

        // Application scripts
        wp_enqueue_script('block-view', $this->getUrlAssets() . 'js/block-view.js', array('jquery'), '', true);
        wp_enqueue_script('field-text', $this->getUrlAssets() . 'js/fields/field-text.js', array('jquery'), '', true);
        wp_enqueue_script('field-checkbox', $this->getUrlAssets() . 'js/fields/field-checkbox.js', array('jquery'), '', true);
        wp_enqueue_script('field-select', $this->getUrlAssets() . 'js/fields/field-select.js', array('jquery'), '', true);
        wp_enqueue_script('field-texarea', $this->getUrlAssets() . 'js/fields/field-texarea.js', array('jquery'), '', true);
        wp_enqueue_script('field-slider', $this->getUrlAssets() . 'js/fields/field-slider.js', array('jquery'), '', true);
        wp_enqueue_script('field-image', $this->getUrlAssets() . 'js/fields/field-image.js', array('jquery'), '', true);
        wp_enqueue_script('field-accordion', $this->getUrlAssets() . 'js/fields/field-accordion.js', array('jquery'), '', true);
        wp_enqueue_script('field-accordion-item', $this->getUrlAssets() . 'js/fields/field-accordion-item.js', array('jquery'), '', true);
        wp_enqueue_script('field-devices', $this->getUrlAssets() . 'js/fields/field-devices.js', array('jquery'), '', true);
        wp_enqueue_script('settings-view', $this->getUrlAssets() . 'js/settings-view.js', array('jquery'), '', true);

        // builder scripts
        wp_enqueue_script('builder-loader', $this->getUrlAssets() . 'js/builder-loader.js', array('jquery'), '', true);
        wp_enqueue_script('builder-wordpress_driver', $this->getUrlAssets() . 'js/builder-wordpress-driver.js', array('jquery'), '', true);
        wp_enqueue_script('builder-toolbar', $this->getUrlAssets() . 'js/builder-toolbar.js', array('jquery'), '', true);
        wp_enqueue_script('builder-iframe', $this->getUrlAssets() . 'js/builder-iframe.js', array('jquery'), '', true);
        wp_enqueue_script('builder-menu', $this->getUrlAssets() . 'js/builder-menu.js', array('jquery'), '', true);
        wp_enqueue_script('builder-viewport', $this->getUrlAssets() . 'js/builder-viewport.js', array('jquery'), '', true);
        wp_enqueue_script('builder-qb', $this->getUrlAssets() . 'js/builder.js', array('jquery'), '', true);

        // page edit script
        wp_enqueue_script('control_edit_page', $this->getUrlAssets() . 'js/control-edit-page.js', array('builder-qb'), '', true);

        // style
        wp_enqueue_style('perfect-scrollbar', $this->getUrlAssets() . "css/perfect-scrollbar.min.css");
        wp_enqueue_style('builder.qb', $this->getUrlAssets() . "css/builder.css");
    }

    /**
     * add html instead shortcode
     */
    public function addMainBuilderBlock() {
        echo '<div class="builder-sc"></div>';
    }

    /**
     * Load data page
     * @return json
     */
    public function loadPageData() {
        global $wpdb;

        $block = $wpdb->get_row("SELECT * FROM " . $this->qb_table_name . " WHERE pid = " . $_POST['page_id'] . "");

        if (isset($block) && $block->data) {
            $data = json_decode($block->data, true);
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
        global $wpdb;

        $blocks_html = trim($_POST['blocks']['html']);
        $data = json_encode($_POST['blocks']['data']);

        $result = $wpdb->update(
                $this->qb_table_name, array(
            'data' => $data,
            'html' => $blocks_html
                ), array('pid' => $_POST['page_id'])
        );

        wp_send_json($result);
        exit();
    }

    /**
     * Get all blocks in folder
     * @return array
     */
    private function getTemplates() {
        $templates = array();

        $path = get_template_directory() . '/blocks';

        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot())
                continue;

            if ($file->isDir()) {
                $url = get_template_directory_uri() . '/blocks/' . $file->getFilename() . '/';
                $settings_json = file_get_contents($url . 'settings.json');

                $settings = SmartUtils::decode($settings_json, true);

                $templates[] = array(
                    'id' => $file->getFilename(),
                    'groups' => $settings['groups'],
                    'url' => $url
                );
            }
        }

        // Get other blocks
        $templates = apply_filters('Smart_blocks', $templates);

        return $templates;
    }

    /**
     * Get block by $id
     * 
     * @param type $id
     * @param type $array
     * @return null|array
     */
    private function getTemplate($id) {
        $templates = $this->getTemplates();

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
        $json = file_get_contents(get_template_directory_uri() . '/blocks/groups.json');
        $json = SmartUtils::decode($json, true);

        // Get other groups
        $json = apply_filters('Smart_blocks_groups', $json);

        return $json;
    }

    /**
     * Load builder data
     * @return json
     */
    public function loadBuilderData() {
        $templates = $this->getTemplates();
        $groups = $this->getGroups();

        if (isset($templates)) {
            $response = array(
                'success' => true,
                'data' => array(
                    'templates' => $templates,
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
     * Get setting block
     * @param string $templateId
     * @return json
     */
    private function getSettingsFile($templateId) {
        $template = $this->getTemplate($templateId);
        $json = file_get_contents($template['url'] . 'settings.json');
        $json = SmartUtils::decode($json, true);

        return $json;
    }

    /**
     * Load settings block
     * @return json
     */
    public function loadSettings() {
        $settings = $this->getSettingsFile($_POST['template_id']);

        if (isset($settings)) {
            $response = array(
                'success' => true,
                'config' => $settings['settings']
            );
        } else {
            $response = array('success' => false);
        }

        wp_send_json($response);
        exit();
    }

    /**
     * Get content hbs file's
     * @return html
     */
    private function getHtml($templateId) {
        $template = $this->getTemplate($templateId);
        return file_get_contents($template['url'] . 'template.hbs');
    }

    /**
     * Load template
     * @return html
     */
    public function loadTemplate() {
        $template = $this->getHtml($_POST['template_id']);
        echo $template;
        exit();
    }

}
