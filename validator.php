<?php
if (!defined('MONK_VERSION')) exit('Access is no allowed.');

// 逻辑过滤
define('PARAM_STRING',  0x00000001);
define('PARAM_INT',     0x00000002); // 默认为无符号整数
define('PARAM_UINT',    0x00000002); // 无符号整数，必须是最常用的整数类型
define('PARAM_SINT',    0x00000004); // 有符号整数
define('PARAM_FLOAT',   0x00000008);
define('PARAM_BOOL',    0x00000010);
define('PARAM_HEX',     0x00000020);
define('PARAM_EXISTS',  0x00000040); // 只验证是否设置了该参数，并且得出一个布尔值
define('PARAM_ARRAY',   0x00000080);
define('PARAM_RAW',     0x00000100); // 不进行任何处理，这是危险的行为

// 防毒过滤器以及选项
define('PARAM_STRIPTAGS', 0x00001000); // 调用 strip_tags, 只适合于字符串
define('PARAM_HASHVAR',   0x00002000); // user facing 变量是一个hash值, param_ 必须在登录后调用
define('PARAM_MD5',       0x00004000); // md5 变量, 值必须匹配 url hash, param_ 必须在登录后调用
define('PARAM_ERROR',     0x00008000); // 当有一个错误的时候，进行错误回调，而不是发送到用户首页
define('PARAM_ALLOW_A',   0x00010000); // 当调用 strip_tags 时，允许 href 链接,<a>
define('PARAM_ALLOW_B',   0x00020000); // 当调用 strip_tags 时，允许 bold 链接,<b>
define('PARAM_USERID',    0x00040000); // 对数字的用户 ID 进行验证
define('PARAM_OBJID',     0x00080000); // 对数字的对象 ID 进行验证

define('PARAM_DATETIME',  0x00100000); // DATETIME 类型
define('PARAM_EMAIL',     0x00200000); // EMAIL 类型
define('PARAM_IPV4',      0x00400000); // ipv4 类型
define('PARAM_DOMAIN',    0x00800000); // 域名类型
define('PARAM_AUTO_INCREMENT',    0x01000000); // 自增字段
define('PARAM_MOBILE',    0x02000000); // 手机类型

// 类型域
define('PARAM_TEXT',    PARAM_STRING ^ PARAM_STRIPTAGS);
define('PARAM_ID',      PARAM_INT);
define('PARAM_UID',     PARAM_INT ^ PARAM_USERID);
define('PARAM_TID',     PARAM_INT ^ PARAM_OBJID);
define('PARAM_URLMD5',  PARAM_STRING ^ PARAM_MD5);
define('PARAM_NULLOK',  PARAM_INT ^ PARAM_SINT ^ PARAM_FLOAT ^ PARAM_BOOL ^ PARAM_HEX);

class validator{
  /*
  * 1，$_GET,$_POST,$_COOKIE,$_SERVER等值的验证依赖函数
  * 2，数据库字段验证
  * 3，也可以独立使用
  */

  /**
   * 类型监测函数识别数组
   *
   * @var array
   */
  public static $function_array = array(
    PARAM_STRING  => 'get_param_string',
    PARAM_UINT    => 'get_param_uint',
    PARAM_SINT    => 'get_param_sint',
    PARAM_FLOAT   => 'get_param_float',
    PARAM_BOOL    => 'get_param_bool',
    PARAM_HEX     => 'get_param_hex',
    PARAM_EXISTS  => 'get_param_exists',
    PARAM_ARRAY   => 'get_param_array',
    PARAM_RAW     => 'get_param_raw',
    PARAM_HASHVAR => 'get_param_hashvar',
    PARAM_ERROR   => 'get_param_error',
    PARAM_NULLOK  => 'get_param_null',
    PARAM_DATETIME=> 'get_param_datetime',
    PARAM_EMAIL   => 'get_param_email',
    PARAM_IPV4    => 'get_param_ipv4',
    PARAM_DOMAIN  => 'get_param_domain',
    PARAM_MOBILE  => 'get_param_mobile',

  );
  public static function get_param_exists($value){
    return isset($value);
  }

  public static function get_param_hashvar($value){
    //用于验证的哈希值
  }

  public static function get_param_raw($value){
    return $value;
  }

  //禁止二维数组，支持数组内的单一数组类型的值检测
  public static function get_param_array($arr,$argv = PARAM_ARRAY){
    if(is_array($arr)){
      foreach ($arr as $key => $r){
          if ($ret = self::get_param_by_type($r, ($argv & ~PARAM_ARRAY)))
          {  
              $arr_r[$key] = $ret;
          }else{
              return false;
          }
      }
      return $arr_r;
    }else{
      throw new Exception('检验的变量不是数组,变量类型为`'.gettype($arr).'`',CORE_VALIDATOR_EC_NOT_ARRAY);
    }
  }

  public static function get_param_by_type($value,$funckey,$argv = ''){
    $func = self::$function_array[$funckey];
    if(empty($argv)) return self::$func($value);
    return self::$func($value,$argv);
  }

  public static function get_param_null($value){
    if($value == '') return null;
  }

  public static function get_param_uint($value,$argv = PARAM_UINT){
    if(ctype_digit($value) || is_int($value)){
      if($argv & PARAM_OBJID){
        //验证是否对象ID,PARAM_TID
      }
      if($argv & PARAM_USERID){
        //验证是否用户ID,PARAM_UID
      }
      return intval($value);
    }else{
      if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') !== false && preg_match('/[0-9]+[0-9a-f]{8}$/', $value) == 1) {
        exit;// 处理ie7 beta2问题
      }
      throw new Exception('检验的变量不是正整数,变量值为`'.$value.'`',CORE_VALIDATOR_EC_NOT_UINT);
    }
  }


  public static function get_param_sint($value){
    if(ctype_digit($value) || is_int($value)){
      return intval($value);
    }else{
      if($value[0] == '-' && ctype_digit(substr($value, 1))){
        return intval($value);
      }else{
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') !== false && preg_match('/[0-9]+[0-9a-f]{8}$/', $value) == 1) {
          exit;// 处理ie7 beta2问题
        }
        throw new Exception('检验的变量不是有符号整数,变量值为`'.$value.'`',CORE_VALIDATOR_EC_NOT_SINT);
      }
    }
  }

  public static function get_param_float($value){
    if(preg_match('/^[0-9\.]*$/i', $value)){
        return floatval($value);
    }else{
        throw new Exception('检验的变量不是浮点数,变量值为`'.$value.'`',CORE_VALIDATOR_EC_NOT_FLOAT);
    }
  }

  public static function get_param_bool($value){
    switch (strtolower($value)) {
      case '0':
      case '1':
        settype($value,'bool');
        return $value;
        break;
      case 'true':
      case 'on':
      case 'yes':
        return true;
        break;
      case 'false':
      case 'off':
      case 'no':
        return false;
        break;
      default:
        throw new Exception('检验的变量不是布尔型,变量值为`'.$value.'`',CORE_VALIDATOR_EC_NOT_BOOL);
        break;
    } 
  }

  public static function get_param_hex($value){
    if(ctype_xdigit($value)){
      return intval(hexdec($value));
    }else{
      throw new Exception('检验的变量不是16进制数,变量值为`'.$value.'`',CORE_VALIDATOR_EC_NOT_HEX);
    }
  }

  public static function get_param_string($str,$argv = PARAM_STRING){
    if($argv & PARAM_MD5){
      //检验url_md5,PARAM_URLMD5
    }
    $str = noslashes($str);

    if($argv & PARAM_STRIPTAGS){
      $allowtags = '';
      if($argv & PARAM_ALLOW_A){
        $allowtags .= '<a>';
      }
      if($argv & PARAM_ALLOW_B){
        $allowtags .= '<b>';
      }
      $str = strip_tags($str, $allowtags);
      // 实体保护
      if ($allowtags && strpos($str, '=') !== false) {  // 有没有实体需要保护?
        // 过滤大多数xss实体
        $exprs = array('/( on[a-z]{1,}|style|class|id|target)="(.*?)"/i',
                 '/( on[a-z]{1,}|style|class|id|target)=\'(.*?)\'/i',
                 '/( on[a-z]{1,}|style|class|id|target)=(.*?)( |>)/i',
                 '/([a-z]{1,})="(( |\t)*?)(javascript|vbscript|about):(.*?)"/i',
                 '/([a-z]{1,})=\'(( |\t)*?)(javascript|vbscript|about):(.*?)\'/i',
                 '/([a-z]{1,})=(( |\t)*?)(javascript|vbscript|about):(.*?)( |>)/i',
                );

        $reps = array('', '', '$3', '$1=""', '$1=""', '$1=""$6');
        $str = preg_replace($exprs, $reps, $str);
      }
    }
    // 过滤\r字符
    $str = str_replace("\r","",$str);
    return strval($str);
  }

  public static function get_param_datetime($str){
      self::get_param_string($str, PARAM_STRING);
      $test = @strtotime($str);
      if($test !== -1 && $test !== false)
          return $test;
      throw new Exception('检验的变量不是有效的日期类型,变量值为`'.$str.'`',CORE_VALIDATOR_EC_NOT_DATETIME);
  }

  public static function get_param_email($str){
      self::get_param_string($str, PARAM_STRING);
      if(preg_match('/^[A-Za-z0-9]+([._\-\+]*[A-Za-z0-9]+)*@([A-Za-z0-9]+[-A-Za-z0-9]*[A-Za-z0-9]+\.)+[A-Za-z0-9]+$/', $str)) 
          return $str;
      throw new Exception('检验的变量不是有效的邮件类型,变量值为`'.$str.'`',CORE_VALIDATOR_EC_NOT_EMAIL);
  }

  public static function get_param_ipv4($str){
      self::get_param_string($str, PARAM_STRING);
      $test = ip2long($str);
      if($test !== -1 && $test !== false) return $test;
      throw new Exception('检验的变量不是有效的IP地址类型,变量值为`'.$str.'`',CORE_VALIDATOR_EC_NOT_IPV4);
  }

  public static function get_param_domain($str){
      self::get_param_string($str, PARAM_STRING);
      if(preg_match('/[a-z0-9\.]+/i', $str)) return $str;
      throw new Exception('检验的变量不是有效的域名类型,变量值为`'.$str.'`',CORE_VALIDATOR_EC_NOT_DOMAIN);
  }

  public static function get_param_mobile($mobilephone){
      $exp = "/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[012356789]{1}[0-9]{8}$|14[57]{1}[0-9]$/"; 
      if(strlen($mobilephone) == 11 && preg_match($exp,$mobilephone)) return $mobilephone; 
      throw new Exception('检验的变量不是有效的手机号码,变量值为`'.$str.'`',CORE_VALIDATOR_EC_NOT_MOBILE);
  }
}
