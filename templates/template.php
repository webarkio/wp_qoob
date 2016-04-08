<?php
/** @var $this Qoob builder */
global $menu, $submenu, $parent_file, $post_ID, $post, $post_type;
$post_ID = $this->post_id;
$post = $this->post;
$post_type = $post->post_type;
$post_title = trim($post->post_title);
$nonce_action = 'update-post_' . $this->post_id;
$user_ID = isset($this->current_user) && isset($this->current_user->ID) ? (int) $this->current_user->ID : 0;
$pageId = $this->pageId;
$form_action = 'editpost';
add_thickbox();
wp_enqueue_media(array('post' => $this->post_id));

require_once(ABSPATH . 'wp-admin/admin-header.php');
?>

<div id="loader-wrapper">
    <div id="loader">
        <div class="minutes-container"><div class="minutes"></div></div>
        <div class="seconds-container"><div class="seconds"></div></div>
    </div>
    <h1><?php _e('Please wait', 'qoob'); ?></h1>
</div>

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
    // fix missed meta boxes
    require_once ABSPATH . 'wp-admin/edit-form-advanced.php';
    ?>
</div>
<?php
require_once(ABSPATH . 'wp-admin/admin-footer.php');
?>
