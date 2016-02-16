<?php

/**
 * Runs on Uninstall of qoob
 *
 * @package   qoob
 * @author    webark.io
 * @link      http://qoob.webark.io/
 * @license    http://qoob.webark.io/LISENCE
 * @version    @package_version@
 */
/* if uninstall not called from WordPress exit */
if (!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')) {
    exit();  // silence is golden
}

global $wpdb;

/* Drop Table */
$table_name = $wpdb->prefix . "pages";
$wpdb->query('DROP TABLE `' . $table_name . '`');
