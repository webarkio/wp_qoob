<?php

/**
 * Runs on Uninstall of qoob
 *
 * @package   qoob
 * @author    webark.com
 * @link      http://webark.com/qoob/
 * @license   http://webark.com/qoob/LISENCE
 * @version   @package_version@
 */
/* if uninstall not called from WordPress exit */
if (!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')) {
    exit();  // silence is golden
}
