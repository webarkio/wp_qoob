<?php
/** @var $this Qoob builder */
global $menu, $submenu, $parent_file, $post_ID, $post, $post_type, $is_IE;
$post_ID = $this->post_id;
$post = $this->post;
$post_type = $post->post_type;
$post_title = trim($post->post_title);
$nonce_action = 'update-post_' . $post_ID;
$user_ID = isset($this->current_user) && isset($this->current_user->ID) ? (int) $this->current_user->ID : 0;
$pageId = $post_ID;
$form_action = 'editpost';
add_thickbox();
wp_enqueue_media(array('post' => $post_ID));
require_once(ABSPATH . 'wp-admin/admin-header.php');
?>
<!--START PRELOADER -->
<div id="loader-wrapper">
    <div id="loader">
        <div class="minutes-container"><div class="minutes"></div></div>
        <div class="seconds-container"><div class="seconds"></div></div>
    </div>
    <h1><?php _e('Please wait', 'qoob'); ?></h1>
</div>
<!--END PRELOADER -->
<!--START BUILDER STRUCTURE-->
<div id="builder">
    <div id="builder-toolbar">
        <div class="logo">
            <div class="wrap-cube">
                <div class="cube">
                    <div class="front"></div>
                    <div class="back"></div>
                    <div class="top"></div>
                    <div class="bottom"></div>
                    <div class="left"></div>
                    <div class="right"></div>
                </div>
            </div>
            <div class="text"></div>
        </div>
        <div class="edit-control-bar">
            <div class="autosave">
                <label class="checkbox-sb">
                    <input type="checkbox"  onclick="parent.builder.autosavePageData();"><span></span><em>Autosave</em>
                </label>
            </div>
            <div class="edit-control-button">
                <button class="save" type="button" onclick="parent.builder.viewPort.save();
                        return false;"><span>Save</span>
                    <div class="clock">
                        <div class="minutes-container"><div class="minutes"></div></div>
                        <div class="seconds-container"><div class="seconds"></div></div>
                    </div>
                </button>
                <button class="exit-btn" onclick="builder.exit();
                        return false;" type="button">Exit</button>
                <button class="screen-size pc active" onclick="parent.builder.toolbar.screenSize(this);
                        return false;" type="button"></button>
                <button class="screen-size tablet-vertical" onclick="parent.builder.toolbar.screenSize(this);
                        return false;" type="button"></button>
                <button class="screen-size phone-vertical" onclick="parent.builder.toolbar.screenSize(this);
                        return false;" type="button"></button>
                <button class="screen-size tablet-horizontal" onclick="parent.builder.toolbar.screenSize(this);
                        return false;" type="button"></button>
                <button class="screen-size phone-horizontal" onclick="parent.builder.toolbar.screenSize(this);
                        return false;" type="button"></button>
                <button class="arrow-btn hide-builder" onclick="parent.builder.toolbar.hideBuilder(this);
                        return false;" type="button"></button>
            </div>
        </div>
    </div>
    <div id="builder-menu">
        <div id="card">
            <div class="card-wrap">
                <div class="card-main side-1">
                    <div class="blocks-settings"></div>
                    <div class="groups"></div>
                    <div class="list-group"></div>
                    <div class="global-settings"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="builder-content">
    <div id="builder-viewport" class="pc">
        <iframe src="/?page_id=<?php echo $post_ID; ?>&qoob=true" scrolling="auto" name="builder-iframe" id="builder-iframe"></iframe>
    </div>
</div>

<input type='hidden' id='post_ID' name='post_ID' value='<?php echo $post_ID; ?>' />
<!--END BUILDER STRUCTURE-->
<!--SCRIPT FOR BUILDER INIT-->
<script type="text/javascript">
    var builder;
    jQuery(document).ready(function () {
        builder = new Builder(
                new BuilderStorage({
                    pageId: <?php echo $pageId; ?>,
                    driver: new WordpressDriver()
                }));
        builder.activate();
    });
</script>

<div style="height: 1px; visibility: hidden; overflow: hidden;">
        <?php
        // Fix: WP 4.0
        wp_dequeue_script( 'editor-expand' );
        ?>
</div>

<?php
require_once(ABSPATH . 'wp-admin/admin-footer.php');
?>
