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
    <h1></h1>
</div>
<!--SCRIPT FOR BUILDER INIT-->
<script type="text/javascript">
    var builder;
    jQuery(document).ready(function () {
        builder = new Builder({
                    storage: new BuilderStorage({
                        pageId: <?php echo $pageId; ?>,
                        driver: new WordpressDriver()
                    })
                });
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
