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
class Qoob {

    /**
     * Name shortcode
     */
    const NAME_SHORTCODE = 'qoob-page';

    /**
     * Revisions count for page in database
     */
    const REVISIONS_COUNT = 20;

    /**
     * Table name plugin
     * @var string
     */
    var $qoob_table_name;

    /**
     * State first shortcode
     * @var boolean
     */
    private $statusShortcode = false;

    /**
     * All items url's
     * @var array
     */
    private $urls = [];

    /**
     * All fields items url's
     * @var array
     */
    private $builderTmplUrls = [];

    /**
     * Default post types
     * @var string
     */
    private $default_post_types = array('page');

    /**
     * Register actions for module
     */
    public function register() {
        // Create table in DB
        $this->creater_db_table();

        if (is_admin()) {
            // Load backend
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

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
            add_action('wp_enqueue_scripts', array($this, 'iframe_scripts'));
        }
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));

        // Registration ajax
        add_action('wp_ajax_load_page_data', array($this, 'loadPageData'));
        add_action('wp_ajax_load_builder_data', array($this, 'loadBuilderData'));
        add_action('wp_ajax_load_settings', array($this, 'loadSettings'));
        add_action('wp_ajax_load_item', array($this, 'loadItem'));
        add_action('wp_ajax_save_page_data', array($this, 'savePageData'));
        add_action('wp_ajax_load_assets', array($this, 'loadAssets'));
        add_action('wp_ajax_load_builder_tmpl', array($this, 'loadBuilderTmpl'));

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
     * <pre><code>$qoob = new Qoob();
     * $path = $qoob->getPath(); //Get path of core module
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
     * <pre><code>$qoob = new Qoob();
     * $path = $qoob->getPathAssets(); //Get assets path of core module
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
     * <pre><code>$qoob = new Qoob();
     * $url = $qoob->getUrlAssets(); //Get assets Url with path ABSPATH
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
     * Get path to current module qoob dir
     * 
     * This method is easiest way to find out where is module qoob located.
     * 
     * <pre><code>$qoob = new Qoob();
     * $path = $qoob->getPathQoob(); //Get qoob path of core module
     * </code></pre>
     * 
     * @return String Path to qoob direcroty
     */
    public function getPathQoob() {
        return $this->getPath() . "qoob" . DIRECTORY_SEPARATOR;
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
        return SmartUtils::getUrlFromPath($this->getPathQoob());
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
        return !is_string($title) || strlen($title) == 0 ? __('(no title)', 'qoob') : $title;
    }

    /**
     * Get qoob url edit page 
     *
     * @param string $id Post id
     * @return string
     */
    public function getUrlQoobPage($id) {
        return admin_url() . 'post.php?qoob=true&post_id=' . $id . '&post_type=' . get_post_type($id);
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
        $url = $this->getUrlQoobPage($id);

        if (preg_match("/" . self::NAME_SHORTCODE . "/", $post->post_content)) {
            return array('edit_qoob' => '<a href="' . $url . '">' . __('Edit with qoob it', 'qoob') . '</a>') + $actions;
        } else {
            return $actions;
        }
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
     * Get state show button
     * @param null $post_id
     * @return bool
     */
    public function showButton($post_id = null) {
        wp_get_current_user();
        if (!current_user_can('edit_post', $post_id)) {
            return false;
        }
        return in_array(get_post_type(), $this->default_post_types);
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
                    'href' => $this->getUrlQoobPage(get_the_ID()),
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
     * Add shortcode to content
     *
     * @param int $pageId
     */
    private function addShortcodeToContent() {
        $post_data = array(
            'ID' => $this->post_id,
            'post_status' => ($this->post->post_status == 'publish' ? 'publish' : 'draft'),
            'post_title' => ($this->post->post_title != '' ? $this->post->post_title : ''),
            'post_content' => '[' . self::NAME_SHORTCODE . ']',
        );

        // Update the post into the database
        wp_update_post($post_data);
    }

    /**
     * Create new page in Qoob db table
     *
     * @return int id new page
     */
    private function createQoobPage($lang) {
        global $wpdb;

        $wpdb->insert($this->qoob_table_name, array(
            'pid' => $this->post_id,
            'lang' => $lang,
            'data' => '',
            'html' => '',
            'rev' => 0,
        ));
    }

    /**
     * Get id from shortcode attr
     *
     * @param string $post_content
     * @return string id from page
     */
    private function checkPage($lang = 'en') {
        global $wpdb;

        //getting existing pages by id
        $pages = $wpdb->get_results(
                'SELECT * FROM ' . $this->qoob_table_name .
                ' WHERE pid = ' . $this->post_id .
                ' AND lang = "' . $lang . '"', "ARRAY_A"
        );

        //if pages don't exist - creating page in database
        if (empty($pages)) {
            $this->createQoobPage($lang);
        }
    }

    /**
     * Create builder page
     *
     * @global object $current_user
     */
    public function renderPage() {
        global $current_user;
        global $post;
        wp_get_current_user();

        $this->checkPage();
        if (!preg_match('/\[qoob-page\]/', $post->post_content)) {
            $this->addShortcodeToContent();
        }

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
        return __('Edit Page with qoob', 'qoob');
    }

    /**
     * Get block from id
     *
     * @global object $wpdb
     * @param type int
     * @return string html
     */
    private function getBlock($id, $lang = 'en') {
        global $wpdb;
        $block = $wpdb->get_results(
                "SELECT * FROM " . $this->qoob_table_name .
                " WHERE pid = " . $id .
                " AND lang='" . $lang . "'" .
                " ORDER BY date DESC LIMIT 1", "ARRAY_A");

        return $block[0];
    }

    /**
     * Create shortcode
     * @param array $atts An associative array of attributes, or an empty string if no attributes are given
     * @param string $content The enclosed content (if the shortcode is used in its enclosing form)
     * @return string Html code our shortcode
     */
    public function add_shortcode($atts, $content = null) {
        if (is_user_logged_in() && ( isset($_GET['qoob']) && $_GET['qoob'] == true)) {
            if ($this->statusShortcode == true) {
                return;
            }
            return $this->addMainBuilderBlock();
            $this->statusShortcode = true;
        } else {
            global $post;
            $id = $post->ID;
            $block = $this->getBlock($id);
            $html = stripslashes($block['html']);
            return $html;
        }
    }

    /**
     * Create plugin table
     *
     * @global object $wpdb
     */
    public function creater_db_table() {
        global $wpdb;

        $this->qoob_table_name = $wpdb->prefix . "pages";
        if ($wpdb->get_var("show tables like '$this->qoob_table_name'") != $this->qoob_table_name) {
            $sql = "CREATE TABLE " . $this->qoob_table_name . " (
                pid INT(9) NOT NULL,
                data TEXT NOT NULL,
                html TEXT NOT NULL,
                rev CHAR(32) NOT NULL,
                date DATETIME NOT NULL DEFAULT NOW(),
                lang VARCHAR(9) NOT NULL DEFAULT 'en', 
                PRIMARY KEY (pid, rev, lang),
                KEY pid(pid)
            );";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Load javascript and css on iframe
     */
    public function iframe_scripts() {
        // Load style
        wp_enqueue_style('builder.qoob.iframe', $this->getUrlQoob() . "css/iframe-builder.css");

        // Load js
        wp_enqueue_script('control.edit.page.iframe', $this->getUrlAssets() . 'js/control-edit-page-iframe.js', array('jquery'), '', true);
        wp_enqueue_script('jquery.appear.js', $this->getUrlQoob() . 'js/libs/jquery.appear.js', array('jquery'), '', true);
        wp_enqueue_script('builder.controls.scroll', $this->getUrlQoob() . 'js/builder-controls-scroll.js', array('jquery', 'jquery.appear.js'), '', true);
    }

    /**
     * Load js and css on frontend pages
     */
    function frontend_scripts() {
        wp_enqueue_style('builder.qoob', $this->getUrlAssets() . "css/qoob.css");
    }

    /**
     * Load javascript and css on admin page
     *
     */
    public function admin_scripts() {
        if (get_post_type() == 'page') {
            wp_enqueue_script('builder.admin', $this->getUrlAssets() . 'js/builder-admin.js', array('jquery'), '', true);
            wp_enqueue_style('wheelcolorpicker-minicolors', $this->getUrlQoob() . "css/wheelcolorpicker.css");
            wp_enqueue_style('builder.qoob.iframe', $this->getUrlAssets() . "css/builder-admin.css");
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
            'qoob' => ( isset($_GET['qoob']) && $_GET['qoob'] == true ? true : false )
                )
        );

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
        wp_enqueue_script('jquery-appear', $this->geturlQoob() . 'js/libs/jquery.appear.js');
        wp_enqueue_script('builder-tinymce', $this->getUrlQoob() . 'js/libs/tinymce/tinymce.min.js', array('jquery'), '', true);
        wp_enqueue_script('handlebars', $this->getUrlQoob() . 'js/libs/handlebars.js', array('jquery'), '', true);
        wp_enqueue_script('handlebars-helper', $this->getUrlQoob() . 'js/libs/handlebars-helper.js', array('jquery'), '', true);
        wp_enqueue_script('jquery-ui-droppable-iframe', $this->getUrlQoob() . 'js/libs/jquery-ui-droppable-iframe.js', array('jquery'), '', true);
        wp_enqueue_script('jquery-wheelcolorpicker', $this->getUrlQoob() . 'js/libs/jquery.wheelcolorpicker.js', array('jquery'), '', true);

        // Application scripts
        wp_enqueue_script('block-view', $this->getUrlQoob() . 'js/views/block-view.js', array('jquery'), '', true);
        wp_enqueue_script('field-text', $this->getUrlQoob() . 'js/fields/field-text.js', array('jquery'), '', true);
        wp_enqueue_script('field-text-autocomplete', $this->getUrlQoob() . 'js/fields/field-text-autocomplete.js', array('jquery'), '', true);
        wp_enqueue_script('field-checkbox', $this->getUrlQoob() . 'js/fields/field-checkbox.js', array('jquery'), '', true);
        wp_enqueue_script('field-select', $this->getUrlQoob() . 'js/fields/field-select.js', array('jquery'), '', true);
        wp_enqueue_script('field-texarea', $this->getUrlQoob() . 'js/fields/field-textarea.js', array('jquery'), '', true);
        wp_enqueue_script('field-slider', $this->getUrlQoob() . 'js/fields/field-slider.js', array('jquery'), '', true);
        wp_enqueue_script('field-image', $this->getUrlQoob() . 'js/fields/field-image.js', array('jquery'), '', true);
        wp_enqueue_script('field-colorpicker', $this->getUrlQoob() . 'js/fields/field-colorpicker.js', array('jquery'), '', true);
        wp_enqueue_script('field-accordion', $this->getUrlQoob() . 'js/fields/field-accordion.js', array('jquery'), '', true);
        wp_enqueue_script('field-accordion-item', $this->getUrlQoob() . 'js/fields/field-accordion-item-expand.js', array('jquery'), '', true);
        wp_enqueue_script('field-accordion-item-front', $this->getUrlQoob() . 'js/fields/field-accordion-item-flip.js', array('jquery'), '', true);
        wp_enqueue_script('field-devices', $this->getUrlQoob() . 'js/fields/field-devices.js', array('jquery'), '', true);
        wp_enqueue_script('fields-view', $this->getUrlQoob() . 'js/views/fields-view.js', array('jquery'), '', true);
        wp_enqueue_script('buildermenu-groups-view', $this->getUrlQoob() . 'js/views/builder-menu-groups-view.js', array('jquery'), '', true);
        wp_enqueue_script('settings-view', $this->getUrlQoob() . 'js/views/settings-view.js', array('jquery'), '', true);
        wp_enqueue_script('buildermenu-blocks-preview-view', $this->getUrlQoob() . 'js/views/builder-menu-blocks-preview-view.js', array('jquery'), '', true);
        wp_enqueue_script('builder-layout', $this->getUrlQoob() . 'js/views/builder-layout.js', array('jquery'), '', true);
        wp_enqueue_script('builder-menu-view', $this->getUrlQoob() . 'js/views/builder-menu-view.js', array('jquery'), '', true);
        wp_enqueue_script('builder-toolbar-view', $this->getUrlQoob() . 'js/views/builder-toolbar-view.js', array('jquery'), '', true);
        wp_enqueue_script('builder-viewport-view', $this->getUrlQoob() . 'js/views/builder-viewport-view.js', array('jquery'), '', true);
        // builder scripts
        wp_enqueue_script('builder-loader', $this->getUrlQoob() . 'js/builder-loader.js', array('jquery'), '', true);
        wp_enqueue_script('builder-wordpress_driver', $this->getUrlAssets() . 'js/builder-wordpress-driver.js', array('jquery'), '', true);
        wp_enqueue_script('builder-toolbar', $this->getUrlQoob() . 'js/builder-toolbar.js', array('jquery'), '', true);
        wp_enqueue_script('builder-menu', $this->getUrlQoob() . 'js/builder-menu.js', array('jquery'), '', true);
        wp_enqueue_script('builder-viewport', $this->getUrlQoob() . 'js/builder-viewport.js', array('jquery'), '', true);
        wp_enqueue_script('builder-storage', $this->getUrlQoob() . 'js/builder-storage.js', array('jquery'), '', true);
        wp_enqueue_script('builder-utils', $this->getUrlQoob() . 'js/builder-utils.js', array('jquery'), '', true);
        wp_enqueue_script('builder-qoob', $this->getUrlQoob() . 'js/builder.js', array('jquery'), '', true);
        wp_enqueue_script('handlebar-extension', $this->getUrlQoob() . 'js/extensions/template-adapter-handlebars.js', array('handlebars'), '', true);
        wp_enqueue_script('underscore-extension', $this->getUrlQoob() . 'js/extensions/template-adapter-underscore.js', array('underscore'), '', true);

        // page edit script
        wp_enqueue_script('control_edit_page', $this->getUrlAssets() . 'js/control-edit-page.js', array('builder-qoob'), '', true);

        // style
        wp_enqueue_style('builder.qoob', $this->getUrlQoob() . "css/builder.css");
    }

    /**
     * add html instead shortcode
     */
    public function addMainBuilderBlock() {
        echo '<div id="builder-blocks"></div>';
    }

    /**
     * Load data page
     * @return json
     */
    public function loadPageData() {
        global $wpdb;

        $blocks = $wpdb->get_results(
                "SELECT * FROM " . $this->qoob_table_name .
                " WHERE pid=" . $_POST['page_id'] .
                " AND lang='" . $_POST['lang'] .
                "' ORDER BY date DESC LIMIT 1", "ARRAY_A");

        $block = !empty($blocks) ? $blocks[0] : null;

        if (isset($block) && isset($block['data'])) {
            $data = stripslashes_deep(json_decode($block['data'], true));

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
        //Checking for administration rights
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $blocks_html = trim($_POST['blocks']['html']);
        $data = isset($_POST['blocks']['data']) ? json_encode($_POST['blocks']['data']) : '';
        $lang = isset($_POST['lang']) ? $_POST['lang'] : 'en';
        $post_id = $_POST['page_id'];
        $updated = false;

        //Getting same blocks with such id and language
        $blocks = $wpdb->get_results(
                "SELECT * FROM " . $this->qoob_table_name .
                " WHERE pid=" . $post_id .
                " AND lang='" . $lang .
                "' ORDER BY date DESC", "ARRAY_A");

        if (!empty($blocks)) {
            //Last page revisioned
            $last_block = $blocks[0];

            //Comparing page to last page saved
            //If html hashes are equal - don't need to save the new revision
            $last_revision_hash = $last_block['rev'];
            $current_revision_hash = md5($blocks_html);

            if ($last_revision_hash !== $current_revision_hash) {
                $updated = $wpdb->insert(
                        $this->qoob_table_name, array(
                    'data' => $data,
                    'html' => $blocks_html,
                    'rev' => $current_revision_hash,
                    'pid' => $post_id,
                    'lang' => $lang)
                );

                //When the amount of revisions are more then needed, 
                // we are deleting first revision in the list
                if (count($blocks) >= self::REVISIONS_COUNT) {
                    $first_block_rev = $blocks[count($blocks)-1]['rev'];
                    $this->deletePageRow($post_id, $lang, $first_block_rev);
                }
            }
        }

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

        $path = get_template_directory() . '/blocks';

        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot())
                continue;

            if ($file->isDir()) {
                $url = get_template_directory_uri() . '/blocks/' . $file->getFilename() . '/';

                $this->urls[] = array(
                    'id' => $file->getFilename(),
                    'url' => $url
                );
            }
        }

        return $this->urls;
    }

    /**
     * Get url builder templates
     * @return array
     */
    private function getUrlBuilderTemplates() {

        if (!empty($this->builderTmplUrls)) {
            return $this->builderTmplUrls;
        }

        $path = ABSPATH . 'wp-content/plugins/qoob.wordpress/qoob/tmpl';
        
        foreach (new DirectoryIterator($path) as $folder) {
           if ($folder->isDot())
                continue;
           if(is_dir($folder->getPathname())) {

                $pathtofiles = ABSPATH . 'wp-content/plugins/qoob.wordpress/qoob/tmpl/'.$folder->getFilename();
                foreach (new DirectoryIterator($pathtofiles) as $file) {

                    if ($file->isDot())
                        continue;

                    $filename = $file->getFilename();

                    $url = plugin_dir_url($filename) . 'qoob.wordpress/qoob/tmpl/'.$folder->getFilename().'/' . $file->getFilename();

                    $this->builderTmplUrls[] = array(
                        'id' => $file->getFilename(),
                        'url' => $url
                    );
                }
            }
        }

        return $this->builderTmplUrls;
    }

    /**
     * Get all blocks in folder
     * @return array
     */
    private function getItems() {
        $templates = array();

        $urls = $this->getUrlItems();

        foreach ($urls as $val) {
            $theme_url = get_template_directory_uri();
            $blocks_url = get_template_directory_uri() . '/blocks';
            $block_url = $val['url'];

            $config_json = file_get_contents($block_url . 'config.json');
            //Parsing for url masks to replace            
            $config_json = preg_replace('/%theme_url%/', $theme_url, $config_json);
            $config_json = preg_replace('/%block_url%/', $block_url, $config_json);
            $config_json = preg_replace('/%blocks_url%/', $blocks_url, $config_json);
            //Decoding json config
            $config = SmartUtils::decode($config_json, true);
            $templates[] = array(
                'id' => $val['id'],
                'url' => $val['url'],
                'config' => $config
            );
        }
        
        return $templates;
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
        $json = file_get_contents(get_template_directory_uri() . '/blocks/groups.json');
        $json = SmartUtils::decode($json, true);

        // Get other groups
//        $json = apply_filters('Smart_blocks_groups', $json);

        return $json;
    }

    /**
     * Load builder data
     * @return json
     */
    public function loadBuilderData() {
        $templates = $this->getItems();
        $groups = $this->getGroups();

        if (isset($templates)) {
            $response = array(
                'success' => true,
                'data' => array(
                    'items' => $templates,
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
    private function getConfigFile($itemId) {
        $item = $this->getItem($itemId);
        $json = file_get_contents($item['url'] . 'config.json');
        $json = SmartUtils::decode($json, true);

        return $json;
    }

    /**
     * Get builder tmpl files contents
     * @return array $tmpl Array of config's json
     */
    private function getBuilderTmplFiles() {

        $tmpl = [];
        $urls = $this->getUrlBuilderTemplates();
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
     * Loading all builder's templates  
     */
    public function loadBuilderTmpl() {
        
        $templates = $this->getBuilderTmplFiles();

        if (isset($templates)) {
            $buildertmpl = [];
            foreach ($templates as $key => $value) {
                $buildertmpl[$key] = $value;
            }
            $response = array(
                'success' => true,
                'buildertemplate' => $buildertmpl
            );
        } else {
            $response = array('success' => false);
        }
        wp_send_json($response);
        exit();
    }

    /**
     * Deleting page row
     * @global object $wpdb
     * @param integer $pid Page id
     * @param string $lang Page language
     * @param integer $revision Number of revision
     * @return type
     */
    private function deletePageRow($pid, $lang = 'en', $revision) {
        global $wpdb;

        return $wpdb->delete($this->qoob_table_name, array(
                    'pid' => $pid,
                    'lang' => $lang,
                    'rev' => $revision
        ));
    }

}
