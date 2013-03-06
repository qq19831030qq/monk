<?php
define('MONK_VERSION', '1.0');

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('MONK_ROOT', dirname(dirname(dirname(__FILE__))).DS);
define('MONK_LIB', MONK_ROOT.'lib'.DS);
define('MONK_CONF', MONK_ROOT.'conf'.DS);
define('MONK_APP', MONK_ROOT.'app'.DS);
set_include_path(get_include_path().PS.MONK_LIB.PS.MONK_CONF);

include(MONK_LIB.'core/interface.php');
include(MONK_LIB.'core/function.php');
include(MONK_LIB.'core/error.php');
include(MONK_LIB.'core/validator.php');
include(MONK_LIB.'core/input.php');
include(MONK_LIB.'core/router.php');

class MONK{
    public static $_config = array();
    public static $_autoload = array();
    public static $_object = array();
    public static $_cache = array();
    public static $_input = null;
    public static $_router = null;
    //是否是post
    private static $_isPost = false;
    //是否是ajax
    private static $_isAjax = false;
    //存储引入的JS文件
    private static $_js = array();
    //存储引入的CSS文件
    private static $_css = array();
    
    private static function init(){
        spl_autoload_register('self::autoload');
        self::$_config = include(MONK_CONF.'config.php');
        self::$_autoload = include(MONK_CONF.'autoload_class.php');
        set_error_handler(array('MONK','_error'), E_ALL);
        set_exception_handler(array('MONK','_exception'));
        self::session_init();
        self::$_input = new MONKInput(array(
			'server' => MONK::getConfig('allowed_server_param')
		));
        self::$_router = new MONKRouterUri(MONK::getConfig('url_method'), include(MONK_CONF.'route.php'));

        if($pathArray = self::$_router->parse_uri(self::$_input->pathinfo())){
            self::setConfig('app', ucfirst(strtolower($pathArray['app'])));
            self::setConfig('controller', ucfirst(strtolower($pathArray['controller'])));
            self::setConfig('action', ucfirst(strtolower($pathArray['action'])));
            $controllerfile = MONK_APP . self::getConfig('app') .'/Controller/'. self::getConfig('controller') .'.php';
            if(is_file($controllerfile)){
                include($controllerfile);
                $controller = self::getSingleton(
                    self::getConfig('app').'_Controller_'.self::getConfig('controller')
                );
                $return = $controller->initBase();
                self::returnDispatch($return);
                $return = $controller->init();
                self::returnDispatch($return);
                $action_subfix = '';
                if(self::$_input->is_ajax()) $action_subfix .= '_AJAX';
                if(self::$_input->is_post()) $action_subfix .= '_POST';
                $return = $controller->run(self::getConfig('action').$action_subfix);
                self::returnDispatch($return);
                
            }else{
                throw new Exception('不存在文件`'.$controllerfile.'`', CORE_BOOTSTRAP_EC_NO_CONTROLLER);
            }
        }else{
            throw new Exception('',CORE_BOOTSTRAP_EC_NO_PATH_ARRAY);
        }
    }

    private static function returnDispatch($return){
        if(!empty($return['method'])){
            switch($return['method']){
                case 'redirect':
                    if(!empty($return['url'])) self::redirect($return['url']);
                    else throw new Exception('跳转网址不存在，文件为'.$controllerfile.'`，方法为`'.self::getConfig('action').$action_subfix.'`，返回内容为`'.dump($return).'`', CORE_BOOTSTRAP_EC_NO_URL);
                case 'refresh':
                    self::redirect(self::$_input->raw_url());
            }
        }
    }
    
    //SESSION管理并初始化
    private static function session_init(){
        session_save_path(self::getConfig('session_path'));
        session_name(self::getConfig('session_name'));
        session_start();
    }

    public static function block($block_class){
        return self::getSingleton($block_class);
    }

    public static function widget($widget_class){
        return self::getSingleton($widget_class);
    }
    
    public static function run(){
        header('Content-Type: text/html;charset=utf8');
        self::init();
    } 

    public static function getSingleton($classname){
        if(!self::isRegistered($classname)){
            if(class_exists($classname))
                self::register($classname,new $classname());
        }
        return self::registry($classname);
    }

    //
    public static function mergeConfig($app_config){
        //过滤禁止的key
        $app_config = array_diff_key($app_config,array_flip(MONK::getConfig('deny_app_config')));
        //合并数组
        self::$_config = $app_config + self::$_config;
    }
    
    public static function getConfig($key){
        if(isset(self::$_config[$key]))
            return self::$_config[$key];
        else
            throw new Exception('不存在变量为`'.$key.'`的配置',CORE_BOOTSTRAP_EC_CONFIG_NOT_EXISTS);
    } 
    
    public static function setConfig($key, $value){
        self::$_config[$key] = $value;
    }
    
    public static function isRegistered($key){
        return isset(self::$_object[$key]);
    }
    
    public static function register($key, $object){
        if (!is_object($object))
        {
            throw new Exception('当前键`'.$key.'`注册的不是对象，变量类型为`'.gettype($object).'`',CORE_BOOTSTRAP_EC_REGISTER_NOT_OBJECT);
        }
		if(!isset(self::$_object[$key])){
			self::$_object[$key] = $object;
		}else{
            throw new Exception('已经存在键`'.$key.'`的对象，请不要重复注册',CORE_BOOTSTRAP_EC_REGISTER_HAS_KEY);
		}
    }

    public static function registry($key){
        if (isset(self::$_object[$key]) && is_object(self::$_object[$key])) {
            return self::$_object[$key];
        }else{
            throw new Exception('未注册键`'.$key.'`对应的对象',CORE_BOOTSTRAP_EC_CANNOT_REGISTRY);
        }
    }
    
    
    //自动加载类 
    private static function autoload($classname){
        if(isset(self::$_autoload[$classname])){
            if(is_file(self::$_autoload[$classname])) include(self::$_autoload[$classname]);
        }elseif ($class_path = self::parse_class($classname)) {
            if(is_file(MONK_APP.$class_path.'.php'))
                include(MONK_APP.$class_path.'.php');
        }elseif(is_file(MONK_APP.MONK::getConfig('app').DS.MONK::getConfig('app_lib_name').DS.$classname.'.php')){
            include(MONK_APP.MONK::getConfig('app').DS.MONK::getConfig('app_lib_name').DS.$classname.'.php');
        }else{
            try{
                include($classname);
            }catch(Exception $e){
                throw new Exception('类`'.$classname.'`不存在',CORE_BOOTSTRAP_EC_CLASS_FILE_NOT_EXISTS);
            }
        }
    }

    //类路径解析
    private static function parse_class($classname){
        if(strpos($classname, '_')){
            return str_replace('_', DS, $classname);
        }
        return false;
    }
    
    //需要加载多语言
    public static function __($str){
        return $str;
    }

    /*
    * 路由说明
    * 表示当前应用，控制器，方法
    */
    public static function _url($uri = '*/*', $additional = array(), $type = ARGV_DEFAULT){
        $option = array();
        $uriArray = explode('/', $uri);
        $option['controller'] = ($uriArray[0] == '*')?MONK::getConfig('controller'):$uriArray[0];
        $option['action'] = ($uriArray[1] == '*')?MONK::getConfig('action'):$uriArray[1];
        $option += $additional;
        return MONK::$_router->url($option,$type);
    }

    public static function redirect($url){
        header("Location:" . $url);
        exit;
    }

    public static function _error($errno, $errstr, $errfile, $errline){
        Error::logError(
            CORE_BOOTSTRAP_EC_SYSTEM_ERROR,
            ERROR_SHOW,
            array(
                'level'     =>  $errno,
                'message'   =>  $errstr,
                'file'      =>  $errfile,
                'line'      =>  $errline
            )
        );
    }

    public static function _exception($e){
        Error::logError(
            $e->getCode(),
            ERROR_SHOW,
            array(
                '报错文件'  =>  $e->getFile(),
                '行'        =>  $e->getLine(),
                '详情'      =>  $e->getMessage(),
            )
        );
    }

    public static function include_js($id, $file, $ifMerge = true, $debug = false, $mergeName = ''){
        if($ifMerge) {
            //
            if(!isset(self::$_js[$id])) self::$_js[$id] = $file;
        }else{
            if($debug) return $file.'?'.time();
        }
        return $file;
    }

    public static function include_css($id, $file, $ifMerge = true, $debug = false, $mergeName = ''){
        if($ifMerge) {
            //
            if(!isset(self::$_css[$id])) self::$_css[$id] = $file;
        }else{
            if($debug) return $file.'?'.time();
        }
        return $file;
    }
}

