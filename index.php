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

    //Module name
    $module = "Qoob";
    //Load after module
    $dependency = "SmartLoader";
    //Submodules dir
    $submodulesDirectory = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . "modules");
    //Includes dir
    $includesDirectory = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . "includes");
    //DO NOT EDIT AFTER THIS LINE
    if (class_exists("SmartLoader")) {
        //Add module to loader
        SmartLoader::add($module, $dependency, $submodulesDirectory, $includesDirectory);

        add_action("SmartLoader_module_inited", function($module) {
            if (class_exists($module)) {
                if ($module == "Qoob") {                    
                    // Register builder
                    $qoob = new Qoob();
                    $qoob->register();
                } else {
                    $moduleInstance = new $module();
                    $moduleInstance->register();
                }
            }
        });
    } else {
        //Delayed include
        add_action("SmartLoader_loaded", function() {
            include(__FILE__);
        });
        //Include loader if exists
        if (file_exists($includesDirectory . DIRECTORY_SEPARATOR . "SmartLoader.class.php")) {
            include($includesDirectory . DIRECTORY_SEPARATOR . "SmartLoader.class.php");
        }
    }
}