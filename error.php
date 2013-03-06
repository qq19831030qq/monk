<?php
if (!defined('MONK_VERSION')) exit('Access is no allowed.');

define('API_EC_SUCCESS', 0);


/*
 * 系统框架核心错误
 *
 */
define('CORE_BOOTSTRAP_EC_CONFIG_NOT_EXISTS', 1);
define('CORE_BOOTSTRAP_EC_REGISTER_NOT_OBJECT', 2);
define('CORE_BOOTSTRAP_EC_REGISTER_HAS_KEY', 3);
define('CORE_BOOTSTRAP_EC_NO_PATH_ARRAY', 4);
define('CORE_BOOTSTRAP_EC_NO_CONTROLLER', 5);
define('CORE_BOOTSTRAP_EC_CANNOT_REGISTRY', 6);
define('CORE_BOOTSTRAP_EC_SYSTEM_ERROR', 7);
define('CORE_BOOTSTRAP_EC_USER_EXCEPTION', 8);
define('CORE_BOOTSTRAP_EC_CLASS_FILE_NOT_EXISTS', 9);
define('CORE_BOOTSTRAP_EC_NO_METHOD', 10);
define('CORE_BOOTSTRAP_EC_NO_URL', 11);

define('CORE_ROUTER_EC_NO_URL_METHOD', 100); //非法的url_method
define('CORE_ROUTER_EC_PARAM_ALIGNMENT', 101); //url参数错误

define('CORE_CONTROLLER_EC_NO_ACTION', 200);

define('CORE_VALIDATOR_EC_NOT_UINT', 1001);
define('CORE_VALIDATOR_EC_NOT_ARRAY', 1002);
define('CORE_VALIDATOR_EC_NOT_SINT', 1003);
define('CORE_VALIDATOR_EC_NOT_FLOAT', 1004);
define('CORE_VALIDATOR_EC_NOT_BOOL', 1005);
define('CORE_VALIDATOR_EC_NOT_DATETIME', 1006);
define('CORE_VALIDATOR_EC_NOT_EMAIL', 1007);
define('CORE_VALIDATOR_EC_NOT_IPV4', 1008);
define('CORE_VALIDATOR_EC_NOT_DOMAIN', 1009);

define('CORE_DB_MYSQL_EC_SYSTEM_ERROR', 1100);
define('CORE_DB_MYSQL_EC_NO_CONNECT', 1101);
define('CORE_DB_MYSQL_EC_NON_SCALAR', 1102);
define('CORE_DB_MYSQL_EC_SQL_QUERY_PARAMETER_MISSING', 1103);

define('CORE_MODEL_EC_DB_INIT_FAILED', 1201);
define('CORE_MODEL_EC_NO_CREATE_DATA', 1202);
define('CORE_MODEL_EC_NO_UPDATE_DATA', 1203);
define('CORE_MODEL_EC_MAP_FILE_CONNOT_FOUND', 1204);

define('CORE_VIEW_EC_THEME_NOT_EXISTS', 1301);
define('CORE_VIEW_EC_VIEW_NOT_EXISTS', 1302);
define('CORE_VIEW_EC_C_VIEW_NOT_EXISTS', 1303);
define('CORE_VIEW_EC_C_VIEW_NO_META', 1304);
define('CORE_VIEW_EC_C_VIEW_NO_TYPE', 1305);
define('CORE_VIEW_EC_C_VIEW_NO_LAYOUT', 1306);

/*
 * 错误日志处理方式
 */
define('ERROR_SAVE',	0x00000001);//存入日志缓冲
define('ERROR_SHOW',	0x00000002);//自定义显示
define('ERROR_LOG',		0x00000004);//error_log() 可记录到服务器，或者发送邮件
define('LOGSYS',			0x00000010);//发送自定义日志系统

class Error{
	private static $errorContainer = array();
	private static $errorExplain = array(
		CORE_ROUTER_EC_NO_URL_METHOD            => '非法的url_method',
		CORE_ROUTER_EC_PARAM_ALIGNMENT					=> 'URL参数错误',
		CORE_BOOTSTRAP_EC_CONFIG_NOT_EXISTS     => '配置项不存在',
		CORE_BOOTSTRAP_EC_REGISTER_NOT_OBJECT   => '注册的不是对象',
		CORE_BOOTSTRAP_EC_REGISTER_HAS_KEY      => '该键已经注册',
		CORE_BOOTSTRAP_EC_NO_PATH_ARRAY      		=> '无法获取路径数组',
		CORE_BOOTSTRAP_EC_NO_CONTROLLER      		=> '无法找到控制器',
		CORE_BOOTSTRAP_EC_CANNOT_REGISTRY      	=> '无法从注册数据中取出当前键',
		CORE_BOOTSTRAP_EC_SYSTEM_ERROR		      => 'PHP脚本错误',
		CORE_BOOTSTRAP_EC_USER_EXCEPTION		    => '用户异常',
        CORE_BOOTSTRAP_EC_CLASS_FILE_NOT_EXISTS     => '该类的文件不存在',
        CORE_BOOTSTRAP_EC_NO_METHOD                 => '控制器返回类型未知',
        CORE_BOOTSTRAP_EC_NO_URL                    => '跳转网址不存在',
		CORE_CONTROLLER_EC_NO_ACTION		      	=> '未找到行动',
		CORE_VALIDATOR_EC_NOT_UINT		      		=> '验证的变量不是无符号整数',
		CORE_VALIDATOR_EC_NOT_ARRAY		      		=> '验证的变量不是数组',
		CORE_VALIDATOR_EC_NOT_SINT		      		=> '验证的变量不是有符号整数',
		CORE_VALIDATOR_EC_NOT_FLOAT		      		=> '验证的变量不是浮点数',
		CORE_VALIDATOR_EC_NOT_BOOL		      		=> '验证的变量不是布尔值',
		CORE_VALIDATOR_EC_NOT_DATETIME		      => '验证的变量不是有效日期格式',
		CORE_VALIDATOR_EC_NOT_EMAIL		      		=> '验证的变量不是邮件地址',
		CORE_VALIDATOR_EC_NOT_IPV4		      		=> '验证的变量不是IPV4',
		CORE_VALIDATOR_EC_NOT_DOMAIN		      	=> '验证的变量不是域名',

		CORE_DB_MYSQL_EC_SYSTEM_ERROR		      	=> 'MYSQL错误',
    CORE_DB_MYSQL_EC_NO_CONNECT		      	  => '连接资源不存在',
    CORE_DB_MYSQL_EC_NON_SCALAR		      	  => '参数不是标量',
    CORE_DB_MYSQL_EC_SQL_QUERY_PARAMETER_MISSING	=> '参数转换无效',

    CORE_MODEL_EC_DB_INIT_FAILED						=> '数据库初始化失败',
    CORE_MODEL_EC_NO_CREATE_DATA						=> '没有提交创建的数据',
    CORE_MODEL_EC_NO_UPDATE_DATA						=> '没有提交更新的数据',
    CORE_MODEL_EC_MAP_FILE_CONNOT_FOUND                 => '数据映射文件不存在',

    CORE_VIEW_EC_THEME_NOT_EXISTS						=> '风格不存在',
    CORE_VIEW_EC_VIEW_NOT_EXISTS						=> '模板目录不存在',
    CORE_VIEW_EC_C_VIEW_NOT_EXISTS					=> '模板编译目录不存在',
    CORE_VIEW_EC_C_VIEW_NO_META                     => '模板文件头信息编写错误',
    CORE_VIEW_EC_C_VIEW_NO_TYPE                     => '模板文件没有类型标识',
    CORE_VIEW_EC_C_VIEW_NO_LAYOUT                   => '模板文件的布局文件有误',
	);
	
	/*
	* $option = array(
	*	'dir'	=> __DIR__,
	*	'file'	=> __FILE__,
	*	'line'	=> __LINE__,
	*	'function'=> __FUNCTION__,
	*	'class' => __CLASS__,
	*	'method'=> __METHOD__,
	*	'namespace'	=> __NAMESPACE__,
	*	...更多需要存储或者显示的字段...
	* );
	*
	* $ext为$method的附加选项
	*/
	public static function logError(
			$code,
			$method = ERROR_SHOW,
			$option = array(),
			$ext = array(
					'format'	=> true
			)
	){
		//可通过计算 $code大小定义级别和类别
		if($method & ERROR_SAVE){
			self::$errorContainer[] = array(
				'code'	=> $code,
				'option'=> $option
			);
		}
		//$ext可传入格式
		if($method & ERROR_SHOW){
			if(!empty($ext['format'])){
				$html =	'<ul>'.
							'<li>code:'.$code.'</li><br />'.
							'<li>说明:'.self::$errorExplain[$code].'</li><br />';
				foreach($option as $k=>$v){
					$html.=	'<li>'.$k.':'.$v.'</li><br />';
				}
				$html .='</ul>';
				header("Content-Type: text/html; charset=utf-8");
				echo $html;
			}else{
				$str = '[code:'.$code.',说明:'.self::$errorExplain[$code];
				foreach($option as $k=>$v){
					$str.=	','.$k.':'.$v;
				}
				$str .= ']';
			}
			
		}
		if($method & ERROR_LOG){
			$str = '[code:'.$code.',说明:'.self::$errorExplain[$code];
			foreach($option as $k=>$v){
				$str.=	','.$k.':'.$v;
			}
			$str .= ']';
			$ext['type'] = (!empty($ext['type']))?$ext['type']:0;
			if($ext['type']==1){
				error_log($str, 1, (isset($ext['destination'])?$ext['destination']:''), (isset($ext['headers'])?$ext['headers']:''));
			}elseif($ext['type']==3){
				error_log($str, 3, (isset($ext['destination'])?$ext['destination']:''));
			}elseif($ext['type']==4){
				error_log($str, 4);
			}else{
				error_log($str, 0);
			}
		}
		if($method & LOGSYS){
			$logger = (!empty($ext['logger']))?$ext['logger']:'';
			$logger->log($code, self::$errorExplain[$code], $option);
		}
	}
}
