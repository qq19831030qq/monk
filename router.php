<?php
if (!defined('MONK_VERSION')) exit('Access is no allowed.');
/*
 * 路由匹配规则
 * PATHINFO => 'app/controller/action[:tag1&tag2&tag3 ...]'
 * 默认url:'app/controller/action/id/1'
 *
 */
class MONKRouterUri{
    /**
     * 存储url模式
     *
     * @var string
     */
    var $url_method       = 'url_default';
    /**
     * 路由表
     *
     * @var array
     */
    var $routes           = array();


    public function __construct($url_method, $routes){
        $this->url_method = $url_method;
        $this->routes = $routes;
    }

    public function parse_uri($uri,$subfix = ''){
        //转为小写
        $uri = strtolower($uri);
		//去掉后缀
        if(!empty($subfix)) $uri = substr($uri,0,-strlen($subfix));
        //如果以/index.php结尾则去掉/
        if(substr($uri,-10) == '/index.php') $uri = substr($uri,0,-10);
		//添加默认
		$container = array(
			'app'		=> MONK::getConfig('app'),
			'controller'=> MONK::getConfig('controller'),
			'action'	=> MONK::getConfig('action')
		);
        /*
        * 优先级 全局路由表>局部路由表>常规路径处理>局部默认配置>全局默认配置
        *
        *
        */
        if($this->url_method == 'url_rewrite'){
            //静态路径匹配
            if(isset($this->routes[$uri]))
            {
                list($app,$controller,$action) = explode('/', $this->routes[$uri]);
				$container['app']		= $app;
                if(is_file(MONK_APP.$container['app'].DS.'conf/config.php'))
                    MONK::mergeConfig(include(MONK_APP.$container['app'].DS.'conf/config.php'));
				$container['controller']= $controller;
				$container['action']	= $action;
                return $container;
            }

            // 动态路径匹配
            foreach($this->routes as $key => $val)
            {
                $key = str_replace('/','\/',$key);
                if(preg_match('#^'.$key.'$#', $uri, $matches))
                {
                    $urls = explode(':', $val);
                    list($app,$controller,$action) = explode('/', $urls[0]);
					$container['app']		= $app;
                    if(is_file(MONK_APP.$container['app'].DS.'conf/config.php'))
                        MONK::mergeConfig(include(MONK_APP.$container['app'].DS.'conf/config.php'));
					$container['controller']= $controller;
					$container['action']	= $action;
                    $querys = explode('&', $urls[1]);
                    foreach ($querys as $value) {
                        $_GET[$value] = $matches[$value];
                    }
                    return $container;
                }
            }

            $middle_c = explode('/', substr($uri, 1));
            $temp_app = array_shift($middle_c);
            if(!empty($temp_app)) $container['app'] = $temp_app;
            //引入APP自定义配置表
            if(is_file(MONK_APP.$container['app'].DS.'conf/config.php')){
                MONK::mergeConfig(include(MONK_APP.$container['app'].DS.'conf/config.php'));
                $container['controller']= MONK::getConfig('controller');
                $container['action']	= MONK::getConfig('action');
            }
            //引入APP自定义路由表
            if(is_file(MONK_APP.$container['app'].DS.'conf/route.php')){
                $app_routes = include(MONK_APP.$container['app'].DS.'conf/route.php');
                if(!empty($uri))
                    $uri = strpos($uri,'/',1)?substr($uri,strpos($uri,'/',1)):'';
                //静态路径匹配
                if(isset($app_routes[$uri]))
                {
                    list($controller,$action) = explode('/', $app_routes[$uri]);
                    $container['controller']= $controller;
                    $container['action']	= $action;
                    return $container;
                }

                // 动态路径匹配
                foreach($app_routes as $key => $val)
                {
                    $key = str_replace('/','\/',$key);
                    if(preg_match('#^'.$key.'$#', $uri, $matches))
                    {
                        $urls = explode(':', $val);
                        list($controller,$action) = explode('/', $urls[0]);
                        $container['controller']= $controller;
                        $container['action']	= $action;
                        $querys = explode('&', $urls[1]);
                        foreach ($querys as $value) {
                            $_GET[$value] = $matches[$value];
                        }
                        return $container;
                    }
                }
            }
                
            
            $temp_controller = array_shift($middle_c);
            $temp_action = array_shift($middle_c);
            if(!empty($temp_controller)) $container['controller'] = $temp_controller;
            if(!empty($temp_action)) $container['action'] = $temp_action;
            unset($temp_app);
            unset($temp_controller);
            unset($temp_action);
            
            $param_count = count($middle_c);
            if($param_count%2 == 1) throw new Exception('路由参数无法对应，URI为`'.$uri.'`',CORE_ROUTER_EC_PARAM_ALIGNMENT);
            for ($i=0; $i < $param_count/2; $i++) { 
                $key = array_shift($middle_c);
                $_GET[$key] = array_shift($middle_c);
            }
			return $container;
        }elseif($this->url_method == 'url_default'){
            MONK::$_input->gets(array(
                    MONK::getConfig('app_name')           => array('func'=>PARAM_STRING),
                    MONK::getConfig('controller_name')    => array('func'=>PARAM_STRING),
                    MONK::getConfig('action_name')        => array('func'=>PARAM_STRING)
                )
            );
            parse_str($uri,$output);
            if(isset($output[MONK::getConfig('app_name')])) $container['app'] = $output[MONK::getConfig('app_name')];
            if(isset($output[MONK::getConfig('controller_name')])) $container['controller'] = $output[MONK::getConfig('controller_name')];
            if(isset($output[MONK::getConfig('action_name')])) $container['action'] = $output[MONK::getConfig('action_name')];
            return $container;
        }else{
            throw new Exception('未配置路由模式`url_method`',CORE_INPUT_EC_NO_URL_METHOD);
        }
    }

    public function url($option,$type){
        $app = strtolower(MONK::getConfig('app'));
        if(empty($option['controller']))    $option['controller'] = MONK::getConfig('controller');
        if(empty($option['action']))        $option['action'] = MONK::getConfig('action');

        $option['controller'] = strtolower($option['controller']);
        $option['action'] = strtolower($option['action']);

        if($this->url_method == 'url_rewrite'){
            //URL重写模式下
            $app_uri = $option['controller'].'/'.$option['action'];
            unset($option['controller']);
            unset($option['action']);
            $reroutes = array_flip($this->routes);
            if(!empty($option))
                $uri_comp = $app_uri.':'.implode('&',array_keys($option));
            else
                $uri_comp = $app_uri;
            //静态路径匹配
            if (isset($reroutes[$app.'/'.$uri_comp])) {
                if(empty($option)){
                    return $reroutes[$app.'/'.$uri_comp];
                }else{
                    //动态路径匹配
                    $url = $reroutes[$app.'/'.$uri_comp];
                    foreach ($option as $key=>$value) {
                        $url = str_replace('(?<'.$key.'>'.$type.')', $value, $url);
                    }
                    return $url;
                }
            }
            if(is_file(MONK_APP.$app.DS.'conf/route.php')){
                $app_routes = include(MONK_APP.$app.DS.'conf/route.php');
                $app_reroutes = array_flip($app_routes);
                //静态路径匹配
                if (isset($app_reroutes[$uri_comp])) {
                    if(empty($option)){
                        return '/'.$app.$app_reroutes[$uri_comp];
                    }else{
                        //动态路径匹配
                        $url = '/'.$app.$app_reroutes[$uri_comp];
                        foreach ($option as $key=>$value) {
                            $url = str_replace('(?<'.$key.'>[^\/]+)', $value, $url);
                        }
                        return $url;
                    }
                }
            }
            if(empty($option)){
                return '/'.$app.'/'.$app_uri;
            }else{
                $url = '/'.$app.'/'.$app_uri;
                foreach ($option as $key => $value) {
                    $url .= '/'.$key.'/'.$value;
                }
                return $url;
            }

        }elseif($this->url_method == 'url_default'){
            $url =  '?'.MONK::getConfig('app_name').'='.$option['app'].
                    '&'.MONK::getConfig('controller_name').'='.$option['controller'].
                    '&'.MONK::getConfig('action_name').'='.$option['action'];
            unset($option['app']);
            unset($option['controller']);
            unset($option['action']);
            $url .= '&'.http_build_query($option);
            return $url;
        }else{
            throw new Exception('未配置路由模式`url_method`',CORE_ROUTER_EC_NO_URL_METHOD);
        }
    }

    private function _url_rewrite(){

    }
}