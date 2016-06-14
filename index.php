<?php

/*
  Plugin Name: qoob
  Plugin URI: http://qoob.webark.io/
  Description: Amazing qoob builder for wordpress
  Version: 1.0
  Author: webark.io
  Author URI: http://webark.io/
 */

if (defined('ABSPATH')) {
    //Includes dir
    $includesDirectory = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . "includes");

    include($includesDirectory . DIRECTORY_SEPARATOR . "Qoob.class.php");
    include($includesDirectory . DIRECTORY_SEPARATOR . "SmartUtils.class.php");

    // Register qoob
    $qoob = new Qoob();
    $qoob->register();
}     