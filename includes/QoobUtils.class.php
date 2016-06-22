<?php

/**
 * This file is part of the Qoob package
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * @author     webark.io <info@webark.io>
 * @link       http://qoob.webark.io/
 * @copyright  2014-2016 Webark.io
 * @license    http://qoob.webark.io/LISENCE
 */

/**
 * Utils for Qoob
 * 
 * The QoobtUtils class is a collection of static methods for making development
 * process easier. For example methods getUrlFromPath or template. 
 * The Qoob class uses a static interface and should not 
 * be instantiated.
 * 
 * <pre><code>//Get url to current file
 * $url = QoobtUtils::getUrlFromPath(__FILE__);
 * </code></pre>
 *
 * @package    Qoob
 * @version    @package_version@
 */
class QoobtUtils {

    /**
     * Get url from path
     * 
     * This method delete ABSPATH or $_SERVER['DOCUMENT_ROOT'] from $path and normalize to url
     * 
     * <pre><code>//Get url to current file with path ABSPATH
     * $url = QoobtUtils::getUrlFromPath(__FILE__);
     * 
     * // Get url to current file with path $_SERVER['DOCUMENT_ROOT']
     * $url = QoobtUtils::getUrlFromPath(__FILE__, true);
     * </code></pre>
     * 
     * @param String $path path to convert
     * @return string Converted url
     */
    public static function getUrlFromPath($path) {
        $path = str_replace("\\", "/", $path);
        $pos = strpos($path, "wp-content");
        if ($pos === false) {
            
        } else {
            return content_url(substr($path, $pos + strlen("wp-content")));
        }
        return false;
    }

    /**
     * Generate randon id number
     * 
     * You can safity use this id in html tags
     * <pre><code>$id = QoobtUtils::generateId();
     * $html = "<div id='$id'></div>";
     * </code></pre>
     * 
     * @param int $length Character count of id. Default 10
     * @return string Generated id
     */
    static function generateId($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Decode a JSON string that is not strictly formed.
     *
     * @param  string  $json
     * @param  boolean $assoc
     * @return array|object
     */
    public static function decode($json, $assoc = FALSE) {
        $json = utf8_encode($json);
        $json = str_replace(array("\n", "\r"), "", $json);
        $json = preg_replace('/([{,])(\s*)([^"]+?)\s*:/', '$1"$3":', $json);
        $json = preg_replace('/(,)\s*}$/','}',$json);
        
        return json_decode($json, $assoc);
    }

}
