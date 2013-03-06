<?php 
if (!defined('MONK_VERSION')) exit('Access is no allowed.');

class MONKInput{
	
	/**
	 * 通过服务器传送过来的pathinfo
	 *
	 * @var array
	 */
	var $pathinfo				= '';
	/**
	 * 通过类型安全监测的$_GET
	 *
	 * @var array
	 */
	var $gets					= array();
	/**
	 * 通过类型安全监测的$_POST
	 *
	 * @var array
	 */
	var $posts					= array();
	/**
	 * 通过类型安全监测的$_COOKIE
	 *
	 * @var array
	 */
	var $cookies				= array();
    /**
	 * 通过类型安全监测的$_SESSION
	 *
	 * @var array
	 */
	var $sessions				= array();
	/**
	 * 通过类型安全监测的$_SERVER
	 *
	 * @var array
	 */
	var $servers				= array();
	/**
	 * 系统内$_GET的默认前缀
	 *
	 * @var string
	 */
	var $get_prefix				= 'get_';
	/**
	 * 系统内$_POST的默认前缀
	 *
	 * @var string
	 */
	var $post_prefix			= 'post_';
	/**
	 * 系统内$_COOKIE的默认前缀
	 *
	 * @var string
	 */
	var $cookie_prefix			= 'cookie_';
    /**
	 * 系统内$_SESSION的默认前缀
	 *
	 * @var string
	 */
	var $session_prefix			= 'session_';
	/**
	 * 系统内$_SERVER的默认前缀
	 *
	 * @var string
	 */
	var $server_prefix			= 'server_';
	/**
	 * 当前用户的IP地址
	 *
	 * @var string
	 */
	var $ip_address					= FALSE;
	/**
	 * 当前用户使用的浏览器 user agent
	 *
	 * @var string
	 */
	var $user_agent						= FALSE;
	/*
	* 优先做平台检测
	*/


	/*
	* 构造函数
	*/
	public function __construct($option = null){
        if(MONK::getConfig('input_param_validate')){
            $this->gets = $this->posts = $this->cookies = $this->sessions = $this->servers = array();
        }else{
            $this->get_prefix = $this->post_prefix = $this->cookie_prefix = $this->server_prefix = '';
            $this->gets	    = $_GET;
            $this->posts	= $_POST;
            $this->cookies	= $_COOKIE;
            $this->servers	= $_SERVER;

        }
		
		if(!empty($option['url_method'])) $this->url_method = $option['url_method'];
		if(!empty($option['server'])) $this->servers($option['server']);
	}

	public function pathinfo(){
		return $this->server('PATH_INFO');
	}

	/*
	* 判断是否命令行模式
	*/
	public function is_cli()
	{
		return (php_sapi_name() == 'cli') or defined('STDIN');
	}
	/*
	* 判断是否ajax request
	*/
	public function is_ajax()
	{
		return ($this->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest') ||
					 ($this->server('x-requested-with') === 'XMLHttpRequest');
	}

	/*
	* 是否post request
	*/
	public function is_post(){
		return ($this->server('REQUEST_METHOD') === 'POST');
	}

	/*
	* 获取当前 website url
	*/
	public function website_url(){
		return $this->http_or_s() . '://' . $this->server('SERVER_NAME');
	}

	private function http_or_s(){
		$scheme = $this->server('HTTPS');
		return (empty($scheme)||($scheme=='off'))?'http':'https';
	}

	/*
	* 当前页地址
	*/
	public function raw_url(){
		return $this->website_url() . $this->server('REQUEST_URI');
	}

	/*
	*	获取来源页的地址
	*/
	public function referer(){
		return $this->server('HTTP_REFERER');
	}

    public function I($op, $types){
        if(MONK::getConfig('input_param_validate'))
            call_user_func_array(array($this,$op.'s'), array($types));
    }

	/*
	* $types = array(
	* 'name'	=> PARAM_STRING,
	*	'content'	=> array('func'=>PARAM_STRING,'argv'=>PARAM_TEXT),
	*	'sex'	=> array('func'=>PARAM_STRING)
	* );
	*/
	private function gets($types){
		foreach($types as $key=>$type){
			if(!isset($_GET[$key])) continue;
			$this->gets[$this->get_prefix.$key] = validator::get_param_by_type($_GET[$key],isset($type['func'])?$type['func']:$type,isset($type['argv'])?$type['argv']:'');
		}
	}

	private function posts($types){
		foreach($types as $key=>$type){
			if(!isset($_POST[$key])) continue;
			$this->posts[$this->post_prefix.$key] = validator::get_param_by_type($_POST[$key],isset($type['func'])?$type['func']:$type,isset($type['argv'])?$type['argv']:'');
		}
	}

	private function cookies($types){
		foreach($types as $key=>$type){
			if(!isset($_COOKIE[$key])) continue;
			$this->cookies[$this->cookie_prefix.$key] = validator::get_param_by_type($_COOKIE[$key],isset($type['func'])?$type['func']:$type,isset($type['argv'])?$type['argv']:'');
		}
	}

    private function sessions($types){
		foreach($types as $key=>$type){
			if(!isset($_SESSION[$key])) continue;
			$this->sessions[$this->session_prefix.$key] = validator::get_param_by_type($_SESSION[$key],isset($type['func'])?$type['func']:$type,isset($type['argv'])?$type['argv']:'');
		}
	}

	private function servers($types){
		foreach($types as $key=>$type){
			if(!isset($_SERVER[$key])) continue;
			$this->servers[$this->server_prefix.$key] = validator::get_param_by_type($_SERVER[$key],isset($type['func'])?$type['func']:$type,isset($type['argv'])?$type['argv']:'');
		}
	}

	public function get($key){
		return isset($this->gets[$this->get_prefix.$key])?$this->gets[$this->get_prefix.$key]:'';
	}

	public function post($key){
		return isset($this->posts[$this->post_prefix.$key])?$this->posts[$this->post_prefix.$key]:'';
	}

	public function cookie($key){
		return isset($this->cookies[$this->cookie_prefix.$key])?$this->cookies[$this->cookie_prefix.$key]:'';
	}

    public function session($key){
		return isset($this->sessions[$this->session_prefix.$key])?$this->sessions[$this->session_prefix.$key]:'';
	}

	public function server($key){
		return isset($this->servers[$this->server_prefix.$key])?$this->servers[$this->server_prefix.$key]:'';
	}


	/**
	* 获取IP地址
	*
	* @access	public
	* @return	string
	*/
	function ip_address()
	{
		if ($this->ip_address !== FALSE)
		{
			return $this->ip_address;
		}

		if (MONK::getConfig('proxy_ips') != '' && $this->server('HTTP_X_FORWARDED_FOR') && $this->server('REMOTE_ADDR'))
		{
			$proxies = preg_split('/[\s,]/', MONK::getConfig('proxy_ips'), -1, PREG_SPLIT_NO_EMPTY);
			$proxies = is_array($proxies) ? $proxies : array($proxies);

			$this->ip_address = in_array($_SERVER['REMOTE_ADDR'], $proxies) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('REMOTE_ADDR') AND $this->server('HTTP_CLIENT_IP'))
		{
			$this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('REMOTE_ADDR'))
		{
			$this->ip_address = $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('HTTP_CLIENT_IP'))
		{
			$this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('HTTP_X_FORWARDED_FOR'))
		{
			$this->ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($this->ip_address === FALSE)
		{
			$this->ip_address = '0.0.0.0';
			return $this->ip_address;
		}

		if (strpos($this->ip_address, ',') !== FALSE)
		{
			$x = explode(',', $this->ip_address);
			$this->ip_address = trim(end($x));
		}

		if ( ! $this->valid_ip($this->ip_address))
		{
			$this->ip_address = '0.0.0.0';
		}

		return $this->ip_address;
	}

	// --------------------------------------------------------------------

	/**
	* User Agent
	*
	* @access	public
	* @return	string
	*/
	function user_agent()
	{
		if ($this->user_agent !== FALSE)
		{
			return $this->user_agent;
		}

		$this->user_agent = ( ! isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];

		return $this->user_agent;
	}
}

