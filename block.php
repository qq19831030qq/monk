<?php
if (!defined('MONK_VERSION')) exit('Access is no allowed.');

/*
* 区块类似于控制器
* 区块的数据来自于model
* 区块的视图 views
*
*
*
*/
class block{
    //
    public function render($param = array()){
        $view = MONK::getSingleton('view');
        $this->beforeRender();
        $view->_blockView($param);
        $this->afterRender();
    }

    protected function beforeRender(){
        return true;
    }

    protected function afterRender(){
        return true;
    }
}

