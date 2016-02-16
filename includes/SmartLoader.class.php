<?php

if (!class_exists('SmartLoader')) {

    add_action('init', array('SmartLoader', 'start'));

    class SmartLoader {

        /**
         * @var Singleton The reference to SmartLoader instance of this class
         */
        private static $instance;

        /**
         * Returns the SmartLoader instance of this class.
         *
         * @return Singleton The SmartLoader instance.
         */
        public static function getInstance() {
            if (null === static::$instance) {
                static::$instance = new static();
            }
            return static::$instance;
        }

        /**
         * Protected constructor to prevent creating a new instance of the
         * SmartLoader via the `new` operator from outside of this class.
         */
        protected function __construct() {
            
        }

        /**
         * Private clone method to prevent cloning of the instance of the
         * SmartLoader instance.
         *
         * @return void
         */
        private function __clone() {
            
        }

        /**
         * Private unserialize method to prevent unserializing of the SmartLoader
         * instance.
         *
         * @return void
         */
        private function __wakeup() {
            
        }

        public $modules = array();

        public static function add($module, $dependency, $submodulesDir, $includesDir) {
            //Add or rewrite module

            SmartLoader::getInstance()->modules[$module] = array($dependency, $submodulesDir, $includesDir);

            //Load modules from submodules dir
            SmartLoader::loadModules($submodulesDir);
        }

        public static function start() {
            do_action("SmartLoader_loaded");
            $modules = SmartLoader::getInstance()->modules;
            foreach ($modules as $module => $prop) {
                add_action($prop[0] . "_inited", array(new SmartCaller(array("SmartLoader", "loadModule"), $module, $prop[2]), "call"));
            }
            do_action("SmartLoader_inited");
            do_action("SmartLoader_end");
        }

        public static function loadModule($module, $includeDir) {
            //Load all files from includes dir
            SmartLoader::loadIncludes($includeDir);
            //Call "$module_inited" action for start load dependency
            do_action("SmartLoader_module_inited", $module);
            do_action($module . "_inited");

        }

        /**
         * Load index.php file from modules from path
         * 
         * All modules should have index.php file in root directory
         * 
         * <pre><code>//load all submodules
         * SmartLoader::loadModules($path); 
         * </code></pre>
         * 
         * @param string $modulesPath path to scan for modules
         */
        public static function loadModules($modulesPath) {
            if ($modulesPath != '' && is_dir($modulesPath)) {
                $files = dir($modulesPath);
                while (false !== ($name = $files->read())) {
                    if ($name != '.' && $name != '..' && $name != '' && file_exists($modulesPath . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'index.php')) {
                        require_once($modulesPath . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'index.php');
                    }
                }
            }
        }

        /**
         * Include all files from directory
         * 
         * 
         * <pre><code>//load all files
         * SmartLoader::loadIncludes($path); 
         * </code></pre>
         * 
         * @param string $includesDir path to scan for files
         */
        public static function loadIncludes($includesDir) {
            if (is_dir($includesDir)) {
                $files = dir($includesDir);
                while (false !== ($name = $files->read())) {
                    $fullPath = $includesDir . DIRECTORY_SEPARATOR . $name;
                    if ($name != '.' && $name != '..' && $name != '' && is_file($fullPath)) {
                        require_once($fullPath);
                    }
                }
            }
        }

    }

    /**
     * Helper class for adding params to action function
     * 
     * This class very usefull use for add_action function
     * 
     * <pre><code>function foo($p1, $p2){
     * //Do something with $p1 and $p2
     * }
     * $caller = new SmartCaller("foo", "param1", "param2");
     * $caller->call(); // will call foo("param1", "param2");
     * 
     * class Bar {
     *   static function func($p1, $p2) {
     *     //Do something with $p1 and $p2
     *   }
     * }
     * $caller = new SmartCaller(array("Bar", "foo"), "param1", "param2");
     * $caller->call(); // will call Bar::func("param1", "param2");
     *
     * $caller = new SmartCaller("Hello world");
     * $caller->show(); // echo "Hello world"
     * </code></pre>
     * 
     * This class very usefull in conjunction with add_action function. This will 
     * allow you add some params to your action handler.
     * 
     * <pre><code>add_action('some_action', array(new SmartCaller("Hello world"), "show"));
     * 
     * function foo($p1, $p2){
     * //Do something with $p1 and $p2
     * }
     * add_action('some_action', array(new SmartCaller("foo", "param1", "param2"), "call"));
     * </code></pre>
     * 
     * @package    SmartBuilder
     * @version    @package_version@
     */
    class SmartCaller {

        /**
         *
         * @var String param to use in function
         */
        private $params;

        /**
         * First param should be a callback function if you want to use call method.
         * 
         * @param callback $param
         */
        public function __construct() {
            $this->params = func_get_args();
        }

        /**
         * Call callback function with params
         */
        public function call() {
            call_user_func_array(array_shift($this->params), $this->params);
        }

        /**
         * Echo params
         */
        public function show() {
            echo $this->params[0];
        }

    }

}