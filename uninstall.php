<?php

/**
 * Runs on Uninstall of qoob
 *
 * @package   qoob
 * @author    webark.com
 * @link      http://qoob-builder.com/
 * @license   http://qoob-builder.com/licenses/
 * @version   @package_version@
 */
/* if uninstall not called from WordPress exit */
if (!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')) {
    exit();  // silence is golden
}

// Remove options "qoob"
delete_option( 'qoob_libs' );
delete_option( 'qoob_version' );