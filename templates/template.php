<?php
/** @var $this Qoob */
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
    <div class="loader-inner-wrapper">
        <div class="loading-panel">
            <div class="qoob-preview-img"></div>
            <span class="sr-only">Loading <span class="precent">0</span>%</span>
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="0"
                     aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                </div>
            </div>
        </div>

        <div class="panel tip-panel">
            <span class="tip-header"><?php __("Did you know", "qoob") ?></span>
            <div class="tip-content"></div>
        </div>
    </div>
</div>
<!--SCRIPT FOR QOOB INIT-->
<script type="text/javascript">
    var qoob;
    jQuery(document).ready(function () {
        qoob = new Qoob({
            storage: new QoobStorage({
                pageId: <?php echo $pageId; ?>,
                driver: new QoobWordpressDriver()
            })
        });
        qoob.activate();
    });
</script>

<div style="height: 1px; visibility: hidden; overflow: hidden;">
    <?php
    // Fix: WP 4.0
    wp_dequeue_script('editor-expand');
    ?>
</div>

<?php
require_once(ABSPATH . 'wp-admin/admin-footer.php');
?>
