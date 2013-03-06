<?php
if (!defined('MONK_VERSION')) exit('Access is no allowed.');

class controller{
    /**/
    private $_view;
    private $_action;
    /**/

    final public function initBase(){
        $this->_view = MONK::getSingleton('view');
        $this->_pageTitle = '';
    }

    public function run($actionName){
        if(empty($actionName)) $actionName = MONK::getConfig('action');
        if($this->beforeAction()){
            $actions = $this->actions();
            if(isset($actions[$actionName])){
                include($actions[$actionName]);
                $action = MONK::getSingleton(
                    MONK::getConfig('app').'_Controller_'.MONK::getConfig('controller').'_Action_'.$actionName
                );
                return $action->run();
            }else{
                if(method_exists($this, 'action'.$actionName)){
                    $actionName = 'action'.$actionName;
                    return $this->$actionName();
                }  
                else
                    throw new Exception('当前控制器不存在方法`'.'action'.$actionName.'`或`'.MONK::getConfig('app').'_Controller_'.MONK::getConfig('controller').'_Action_'.$actionName.'`类',CORE_CONTROLLER_EC_NO_ACTION);
            }
        }

        
    }

    /* 
    * 'actionName1'  => 'path/actionName1Class',
    * 'actionName2'  => 'path/actionName2Class'
    */
    public function actions(){
        return array();
    }

    public function getAction(){
        return $this->_action;
    }

    public function setAction($action){
        $this->_action = $action;
    }

    protected function beforeAction(){
        return true;
    }

    protected function afterAction(){
        return true;
    }

    public function getController(){
        return $this->_controller;
    }

    public function getApp(){
        return $this->_app;
    }

    public function widget(){}

    //重定向到新的控制器或者新的方法
    public function forward(){
        if($this->beforeForward()){
            //todo
            $this->afterForward();
        }
    }

    protected function beforeForward(){
        return true;
    }

    protected function afterForward(){
        return true;
    }

    public function refresh(){
        return array(
            'method' => 'refresh'
        );
    }

    public function assign($key, $val=null){
        $this->_view->assign($key,$val);
    }

    public function redirect($url){
        return array(
            'method' => 'redirect',
            'url'    => $url
        );
    }

    public function json($param = array(), $callback = '', $ContentType = 'application/json'){
        header("Content-Type: " . $ContentType . "; charset=utf-8");
        if(empty($callback)){
            echo(json_encode($param));
        }else{
            echo($callback . '(' . json_encode($param) . ')');
        }
        ob_flush();
        flush();
        exit;
    }

    public function render($param = array()){
        $this->beforeRender();
        $this->_view->_view($param);
        $this->afterRender();
    }

    protected function beforeRender(){
        $controllerParam = array(
            'controller' => MONK::getConfig('controller'),
            'action' => MONK::getConfig('action')
        );
        $this->assign($controllerParam);
    }

    protected function afterRender(){
        return true;
    }

    public function init(){}

    public function __destruct(){}

    public function _setType($types, $op = 'get'){
        call_user_func_array(array(MONK::$_input,'I'), array($op, $types));
    }
    
    public function _get($key){
        return MONK::$_input->get($key);
    }

    public function _post($key){
        return MONK::$_input->post($key);
    }

    public function _cookie($key){
        return MONK::$_input->cookie($key);
    }

    public function _session($key){
        return MONK::$_input->session($key);
    }

    public function _server($key){
        return MONK::$_input->server($key);
    }
}