<?php
/*
Plugin Name: qoob
Plugin URI: http://webark.com/qoob/
Text Domain: qoob
Domain Path: /languages
Description: Qoob - by far the easiest free page builder plugin for WP
Version: 2.0.0
Author: webark.com
Author URI: http://webark.com/
*/

/**
 * This file is part of the Qoob builder package
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     webark.com <qoob@webark.com>
 * @link       http://webark.com/qoob/
 * @copyright  2015-2017 WebArk.com
 * @license    http://webark.com/qoob/LISENCE
 */

class Qoob {
	private $mode = 'prod';
	/**
	 * Default post types
	 *
	 * @var string
	 */
	private $version = '2.0.0';
	/**
	 * Register actions for plugin
	 */
	public function __construct( $mode = 'prod' ) {
		$this->mode = $mode;

		// Plugin have been loaded
		add_action( 'plugins_loaded', array( $this, 'pluginLoaded' ), 10, 2 );

		// Load libraries data action. Needs for front and backend.
		add_action( 'wp_ajax_qoob_load_libraries_data', array( $this, 'loadQoobLibrariesData' ) );

		if ( is_admin() ) {
			// =================ONLY ADMIN ZONE=====================
			// Register action for qoob builder page
			add_action( 'post_action_qoob', array( $this, 'starterPage' ) );

			// Add scripts for media library dialog
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueueBackendScripts' ) );

			// Ajax load page data
			add_action( 'wp_ajax_qoob_load_page_data', array( $this, 'loadQoobPageData' ) );

			// Ajax save page data
			add_action( 'wp_ajax_qoob_save_page_data', array( $this, 'saveQoobPageData' ) );

			// Ajax load page templates
			add_action( 'wp_ajax_qoob_load_page_templates', array( $this, 'loadQoobPageTemplates' ) );

			// Ajax save page template
			add_action( 'wp_ajax_qoob_save_page_template', array( $this, 'SaveQoobPageTemplate' ) );

			// Controlling revisions
			add_action( 'save_post', array( $this, 'savePostMeta' ) );
			add_action( 'wp_restore_post_revision', array( $this, 'restoreRevision' ), 10, 2 );

			// Add demo blocks lib
			add_filter( 'qoob_libs', array( $this, 'addDemoLib' ), 10, 2 );

			// Save libraries data action
			add_action( 'wp_ajax_qoob_save_libraries_data', array( $this, 'saveQoobLibrariesData' ) );

			// Add metabox
			add_action( 'add_meta_boxes', array( $this, 'infoMetabox' ) );

			// Remove tinymce if page was used as Qoob page
			add_action( 'load-page.php', array( $this, 'onPageEdit' ) );

			// Add edit link
			add_filter( 'page_row_actions', array( $this, 'addEditLinkPost' ) );

			// Filter qoob meta data
			add_filter( 'wxr_export_skip_postmeta', array( $this, 'filterChangeExportQoobmeta' ), 10, 3 );
		} else {
			// ==================ONLY FRONTEND======================
			// add edit link to admin bar
			add_action( 'admin_bar_menu', array( &$this, 'addAdminBarLink' ), 999 );
			// Adding filter to check content
			add_filter( 'the_content', array( $this, 'modifyContent' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueFrontendScripts' ) );
		}

		// Add new image to media
		add_action( 'wp_ajax_qoob_add_new_image', array( $this, 'addNewImage' ) );
	}

	/**
	 * Add actions when class is loaded.
	 *
	 * @return void
	 */
	public function pluginLoaded() {
		// load localize
		load_plugin_textdomain( 'qoob', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function enqueueFrontendScripts() {
		wp_enqueue_style( 'qoob-frontend-style', plugins_url( 'assets/css/qoob-frontend-custom.css', __FILE__ ) );

		// if page have $get['qoob']
		if ( isset( $_GET['qoob'] ) && $_GET['qoob'] == true ) {
			wp_enqueue_script( 'qoob-frontend-custom', plugins_url( 'assets/js/qoob-frontend-custom.js', __FILE__ ), array( 'jquery' ) );
		}
	}

	public function enqueueBackendScripts() {
		wp_enqueue_media();
		wp_enqueue_script( 'qoob-backend-starter', plugins_url( 'qoob/qoob-backend-starter.js', __FILE__ ), array( 'jquery' ) );
		wp_register_script( 'qoob-backend-custom', plugins_url( 'assets/js/qoob-backend-custom.js', __FILE__ ),  array( 'jquery' ), true );

		// Media WP style
		wp_enqueue_style('common');
		wp_enqueue_style('forms');

		// Localize the script with new data
		$translation_array = array(
			'button_text' => esc_html__( 'qoob', 'qoob' ),
		);
		wp_localize_script( 'qoob-backend-custom', 'qoob_backend_custom', $translation_array );
		// Enqueued script with localized data.
		wp_enqueue_script( 'qoob-backend-custom' );

		wp_enqueue_style( 'qoob-backend-style',  plugins_url( 'assets/css/qoob-backend-custom.css', __FILE__ ) );
	}

	/**
	 * Checking for administration rights
	 */
	public function checkAccess() {
		return (current_user_can( 'edit_posts' ) ? true : false);
	}

	private function getDriverHtml() {
		global $post;
		if ( empty( $post ) ) {
			wp_die( 'Post not found' );
		}
		return '<script type="text/javascript" src="' . plugins_url( 'qoob-wordpress-driver.js', __FILE__ ) . '"></script><script type="text/javascript"> var starter = new QoobStarter({"mode": "' . $this->mode . '", "skip":["jquery","underscore","backbone"],"qoobUrl": "' . plugins_url( 'qoob/', __FILE__ ) . '", "driver": new QoobWordpressDriver({"ajaxUrl": "' . admin_url( 'admin-ajax.php' ) . '", "iframeUrl": "' . add_query_arg( 'qoob', 'true', get_permalink( $post->ID ) ) . '", "pageId": ' . $post->ID . ' }) });</script>';
	}

	/**
	 * Render qoob builder starter page
	 */
	public function starterPage( $post_id ) {
		if ( ! $this->checkAccess() ) {
			wp_die( 'Access is denied' );
		}

		// if new page
		$post_data = get_post( $post_id );
		if ( 'auto-draft' === $post_data->post_status ) {
			$data = array(
				'ID' => $post_id,
				'post_status' => 'publish',
				'post_title' => '',
			);

			$result = wp_update_post( $data, true );

			if ( is_wp_error( $result ) ) {
				wp_die( 'Post not saved' );
			}
		}

		global $hook_suffix;
		echo '<!DOCTYPE html><html><head>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		echo '<title>' . esc_html__( 'Edit Page with qoob', 'qoob' ) . '</title>';
		do_action( 'admin_enqueue_scripts', $hook_suffix );
		do_action( 'admin_print_styles' );
		do_action( 'admin_print_scripts' );
		add_filter( 'admin_footer_text', '__return_empty_string', 11 );
		add_filter( 'update_footer', '__return_empty_string', 11 );
		echo $this->getDriverHtml();
		echo '</head><body>';
		include( ABSPATH . 'wp-admin/admin-footer.php' );
		die();
	}

	/**
	 * Send array of libraries data for Qoob
	 */
	public function loadQoobLibrariesData() {
		if ( ! $this->checkAccess() ) {
			$response = array(
				'success' => false,
				'error' => 'Access is denied',
			);
		} else {
			$libs = $this->getLibs();

			$response = array(
				'success' => true,
				'libs' => (isset( $libs ) ? $libs : []),
			);
		}
		wp_send_json( $response );
	}

	/**
	 * Add new library to array libs
	 */
	public function saveQoobLibrariesData() {
		$incoming = file_get_contents( 'php://input' );

		$updated = update_option( 'qoob_libs', wp_slash( json_decode( $incoming, true ) ) );

		if ( $updated ) {
			$response = array(
				'success' => true,
			);
		} else {
			$response = array(
				'success' => false,
				'error' => "Couldn't save libs",
			);
		}

		wp_send_json( $response );
	}

	/**
	 * Get libs
	 *
	 * @return json
	 */
	public function getLibs() {
		$qoob_libs = get_site_option( 'qoob_libs' );

		if ( $qoob_libs ) {
			$result = wp_unslash( $qoob_libs );
		} else {
			$libs = apply_filters( 'qoob_libs', array() );

			$result = array();
			foreach ( $libs as $value ) {
				if ( file_exists( $value ) ) {
					$lib = json_decode( file_get_contents( $value ), true );
					if ( ! array_key_exists( 'url', $lib ) ) {
						$lib['url'] = $this->getUrlFromPath( str_replace( '/lib.json', '', $value ) );
					}

					// if old version lib
					if ( ! array_key_exists( 'version', $lib ) ) {
						// Set current version
						$lib['version'] = $this->version;

						foreach ( $lib['blocks'] as $index => $block ) {
							$lib['blocks'][ $index ]['url'] = str_replace( '%theme_url%/blocks/', '', $block['url'] );
						}

						foreach ( $lib['res'] as $key => $val ) {
							if ( 'js' === $key || 'css' === $key ) {
								foreach ( $val as $v ) {
									$temp = array(
											'type' => $key,
											'name' => $v['name'],
											'src' => str_replace( '%theme_url%/blocks/', '', $v['url'] ),
											);

									if ( $v['use'] ) {
										$lib['res'][] = array_merge_recursive( $temp, $v['use'] );
									} else {
										$lib['res'][] = $temp;
									}

									unset( $temp );
								}
							}
						}

						unset( $lib['res']['css'] );
						unset( $lib['res']['js'] );
					}

					$result[] = $lib;
				}
			}

			update_option( 'qoob_libs', wp_slash( $result ) );
		}

		return $result;
	}

	public function getUrlFromPath( $path ) {
		// Get correct URL and path to wp-content
		$content_url = untrailingslashit( dirname( dirname( get_stylesheet_directory_uri() ) ) );
		$content_dir = untrailingslashit( WP_CONTENT_DIR );

		// Fix path on Windows
		$path = wp_normalize_path( $path );
		$content_dir = wp_normalize_path( $content_dir );

		return str_replace( $content_dir, $content_url, $path );
	}

	/**
	 * Load data page
	 *
	 * @return json
	 */
	public function loadQoobPageData() {
		if ( ! $this->checkAccess() ) {
			$response = array(
				'success' => false,
				'error' => 'Access is denied',
			);
		} else {
			if ( is_string( get_post_status( $_REQUEST['page_id'] ) ) ) {
				$data = get_post_meta( $_REQUEST['page_id'], 'qoob_data', true );

				// Send decoded page data to the Qoob editor page
				if ( '' !== $data ) {
					$data = wp_unslash( json_decode( $data, true ) );

					if ( ! empty( $data['blocks'] ) && is_null( $data['version'] ) ) {
						foreach ( $data['blocks'] as $index => $block ) {
							$data['blocks'][ $index ]['block'] = $data['blocks'][ $index ]['template'];
							unset( $data['blocks'][ $index ]['template'] );
						}

						$data['version'] = $this->version;
					}

					$response = array(
						'success' => true,
						'data' => $data,
					);
				} else {
					$response = array( 'success' => true, 'data' => array( 'version' => $this->version, 'blocks' => array() ) );
				}
			} else {
				$response = array( 'success' => false, 'error' => 'Page with id ' + $_REQUEST['page_id'] + ' not found.' );
			}
		}
		wp_send_json( $response );
	}

	/**
	 * Save data page
	 *
	 * @return json
	 */
	public function saveQoobPageData() {
		$incoming = file_get_contents( 'php://input' );

		$decoded = json_decode( $incoming, true );
		$data = $decoded['data'];

		$blocks_html = trim( $data['html'] );

		$post_id = $decoded['pageId'];

		$qoob_data = wp_slash( wp_json_encode( $data['data'] ) );

		// Saving metafield
		$updated_meta = update_post_meta( $post_id, 'qoob_data', $qoob_data );

		// Updating post content and post content filtered
		$update_args = array(
		  'ID'           => $post_id,
		  'post_content' => $blocks_html,
		);

		$updated = wp_update_post( $update_args );

		if ( $updated && $updated_meta ) {
			$response = array( 'success' => true );
		} else {
			$response = array( 'success' => false, 'error' => "Couldn't save data" );
		}

		wp_send_json( $response );
	}

	/**
	 * Load page templates
	 *
	 * @return json
	 */
	public function loadQoobPageTemplates() {
		$page_templates = get_site_option( 'qoob_page_templates' );
		if ( $page_templates ) {
			$response = array( 'success' => true, 'templates' => wp_unslash( $page_templates ) );
		} else {
			$response = array( 'success' => false );
		}

		wp_send_json( $response );
	}

	/**
	 * Save page template
	 *
	 * @return json
	 */
	public function SaveQoobPageTemplate() {
		$incoming = file_get_contents( 'php://input' );

		$update_option = update_option( 'qoob_page_templates', wp_slash( $incoming ) );

		if ( $update_option ) {
			$response = array( 'success' => true );
		} else {
			$response = array( 'success' => false, 'error' => "Couldn't save template" );
		}

		wp_send_json( $response );
	}

	/**
	 * Filtering the content for theme templating
	 */
	public function modifyContent( $content = null ) {
		$result = '';
		if ( is_user_logged_in() && (isset( $_GET['qoob'] ) && $_GET['qoob'] == true) ) {
			$result = '<div id="qoob-blocks"></div>';
		} else {
			global $post;
			$data = json_decode( get_post_meta( $post->ID, 'qoob_data', true ), true );

			// If have blocks - use get_the_content() function. In other way - return basic content
			if ( count( $data['blocks'] ) > 0 ) {
				$result = do_shortcode( stripslashes( get_the_content() ) );
			} else {
				$result = do_shortcode( $content );
			}
		}
		$result .= '    <!-- qoob starter --><script type="text/javascript" src="' . plugins_url( 'qoob/qoob-frontend-starter.js', __FILE__ ) . '"></script>'
		. $this->getDriverHtml() . '<!-- end qoob starter -->';
		return $result;
	}

	/**
	 * Add link to admin bar
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public function addAdminBarLink( $wp_admin_bar ) {
		if ( ! is_object( $wp_admin_bar ) ) {
			global $wp_admin_bar;
		}
		if ( is_singular() ) {
				// FIX: check page use qoob or empty
				$wp_admin_bar->add_menu(array(
					'id' => 'qoob-admin-bar-link',
					'title' => esc_html__( 'Edit with qoob', 'qoob' ),
					'href' => $this->getEditWithQoobUrl( get_the_ID() ),
					'meta' => array( 'class' => 'qoob-inline-link' ),
				));
		}
	}

	/**
	 * Get qoob url edit page
	 *
	 * @param string $id Post id
	 * @return string
	 */
	public function getEditWithQoobUrl( $pageId ) {
		return admin_url() . 'post.php?post=' . $pageId . '&action=qoob';
	}

	/**
	 * Saving post meta to revision
	 *
	 * @param  int $post_id ID of the current post
	 */
	public function savePostMeta( $post_id ) {
		$parent_id = wp_is_post_revision( $post_id );
		if ( $parent_id ) {
			$parent  = get_post( $parent_id );
			$qoob_data = get_post_meta( $parent->ID, 'qoob_data', true );
			if ( $qoob_data !== false ) {
				add_metadata( 'post', $post_id, 'qoob_data', $qoob_data );
			}
		}
	}

	/**
	 * Updating post metadata during revision restoring
	 *
	 * @param  int $post_id     Post id
	 * @param  int $revision_id Current revision id
	 */
	public function restoreRevision( $post_id, $revision_id ) {
		$revision = get_post( $revision_id );
		$data  = get_metadata( 'post', $revision->ID, 'qoob_data', true );
		if ( false !== $data ) {
			update_post_meta( $post_id, 'qoob_data', $data );
		} else {
			delete_post_meta( $post_id, 'qoob_data' );
		}
	}

	/**
	 * Create array libs from json file
	 *
	 * @param array $qoobLibs Array libs from json file.
	 */
	public function addDemoLib( $qoob_libs ) {
		$qoob_libs[] = plugin_dir_path( __FILE__ ) . 'qoob/blocks/lib.json';
		return $qoob_libs;
	}

	/**
	 * Add metabox with info about current Qoob page
	 */
	public function infoMetabox() {
		global $post;
		$post_meta = json_decode( get_post_meta( $post->ID, 'qoob_data', true ), true );
		// If have blocks - remove tinymce editor
		if ( count( $post_meta['blocks'] ) ) {
			add_meta_box( 'qoob-page-info', esc_html__( 'Attention!', 'qoob' ), array( $this, 'infoMetaboxDisplay' ), 'page' );
		}
	}

	/**
	 * Display metabox
	 */
	public function infoMetaboxDisplay() {
		$allowed_html = array(
			'p' => array(),
		);
		echo wp_kses( __( '<p>Current page has been edited with Qoob Page Builder. To edit this page as regular one - go to Qoob editor by pressing "qoob" button and remove all blocks.</p>', 'qoob' ), $allowed_html );
	}

	/**
	 * Filtering metaboxes on page edit screen
	 */
	public function onPageEdit() {
		if ( isset( $_GET['post'] ) ) {
			// Getting qoob_html from metadata
			$post_id = $_GET['post'];
			$data = json_decode( get_post_meta( $post_id, 'qoob_data', true ), true );

			// If have blocks - remove tinymce editor
			if ( count( $data['blocks'] ) ) {
				remove_post_type_support( 'page', 'editor' );
			}
		}
	}

	/**
	 * Add link to posts list
	 *
	 * @param $actions
	 * @return mixed
	 */
	public function addEditLinkPost( $actions ) {
		$post_data = get_post();
		$id = ( strlen( $post_data->ID ) > 0 ? $post_data->ID : get_the_ID() );
		$url = $this->getEditWithQoobUrl( $id );
		// Check for qoob page
		$post_meta = json_decode( get_post_meta( $id, 'qoob_data', true ), true );

		if ( count( $post_meta ) > 0 ) {
			$actions['edit_qoob'] = '<a href="' . $url . '">' . esc_html__( 'Edit with qoob', 'qoob' ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Quote qoob_data with slashes
	 *
	 * @param (bool)   $skip Whether to skip the current post meta. Default false.
	 * @param  (string) $meta_key  Current meta key.
	 * @param  (object) $meta Current meta object.
	 * @return Returns the escaped meta object.
	 */
	public function filterChangeExportQoobmeta( $skip, $meta_meta_key, $meta ) {
		if ( 'qoob_data' === $meta_meta_key ) {
			$meta->meta_value = addslashes( $meta->meta_value );
		}
		return $skip;
	}

	/**
	 * Upload image
	 * @param (array) file data array
	 */
	public function uploadImage( $file = array() ) {
		require_once ABSPATH . 'wp-admin/includes/admin.php';
		$file_return = wp_handle_upload( $file, array( 'test_form' => false ) );

		if ( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
			return false;
		} else {
			$filename = $file_return['file'];
			$attachment = array(
				'post_mime_type' => $file_return['type'],
				'post_content' => '',
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'guid' => $file_return['url'],
			);
			if ( $title ) {
				$attachment['post_title'] = $title;
			}
			$attachment_id = wp_insert_attachment( $attachment, $filename );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );
			if ( 0 < intval( $attachment_id ) ) {
				return $attachment_id;
			}
		}
		return false;
	}

	/**
	 * Send json data from image
	 */
	public function addNewImage() {
		$data = array();

		if ( empty( $_FILES ) ) {
			$data['error'] = false;
			$data['message'] = __( 'Please select an image to upload!','qoob' );
		} elseif ( $file['size'] > 5242880 ) { // Maximum image size is 5M
			$data['size'] = $files[0]['size'];
			$data['error'] = false;
			$data['message'] = __( 'Image is too large. It must be less than 2M!','qoob' );
		} else {
			$data['message'] = '';

			if ( isset( $_FILES['image'] ) ) {
				$file = $_FILES['image'];
				$attachment_id = $this->uploadImage( $file, false );

				if ( is_numeric( $attachment_id ) ) {
					$img_thumb = wp_get_attachment_image_src( $attachment_id, 'full' );
					$data['success'] = true;
					$data['url'] = $img_thumb[0];
				}
			}

			if ( ! $attachment_id ) {
				$data['error'] = false;
				$data['message'] = __( 'An error has occured. Your image was not added.','qoob' );
			}
		}

		echo json_encode( $data );
		die();
	}
}
$qoob = new Qoob( 'dev' );
