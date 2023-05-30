<?php

class CProgressBar {
    var $title = '', $action = '', $headAction = '', $comments = '';
    var $id, $progress = 1, $increase = 0;
    
    function CProgressBar($id, $startup = true) {
        $id = str_replace(array('.','..','\\','/'),'',$id);
        $this->id = $id;
        
        if ($startup) {
            $this->saveProgress();
        }
    }
    
    function setTitle($title) {
        $this->title = $title;
    }
    
    function setAction($action) {
        $this->action = $action;
    }
    
    function setHeadAction($action) {
        $this->headAction = $action;
    }
    
    function setComments($comments) {
        $this->comments = $comments;
    }
    
    function getFile() {
        return $GLOBALS['wwf'].'/temp/'.$this->id.'.progress';
    }
    
    function getAddress() {
        return $GLOBALS['sitepath'].'temp/'.$this->id.'.progress';
    }
    
    function setProgress($progress) {
        $this->progress = $progress;
    }

    function setIncrease($increase) {
        $this->increase = $increase;
    }
    
    function saveProgress($progress = 0) {
        $action = $this->action;
        $this->setProgress($progress);
        if (strtolower(ini_get('default_charset')) != strtolower($GLOBALS['controller']->lang_controller->lang_current->encoding)) {
            $action = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'UTF-8',$this->action);
        }
        if ($fp = fopen($this->getFile(),'w+')) {
            fwrite($fp,
                $action.'|'.
//                $this->action . '|'.
                (int) $progress);
            fclose($fp);
            chmod($this->getFile(),0777);
        }
    }        
    
    function increase() {
        $progress = $this->progress + $this->increase;
        if ($progress < 0) $progress = 0;
        $this->saveProgress($progress);
    }
    
    function unlink() {
        @unlink($this->getFile());
    }

    function fetch() {
        $smarty = new Smarty_els();
                
        $smarty->assign('id',$this->id);
        $smarty->assign('upload',(boolean) $_GET['upload']);
        $smarty->assign('title', $this->title);
        $smarty->assign('headAction',$this->headAction);
        $smarty->assign('comments',$this->comments);
        $smarty->assign('sitepath',$GLOBALS['sitepath']);
        $smarty->assign('url',$this->getAddress());
        $smarty->assign('encoding', $GLOBALS['langController']->lang_current->encoding);
        return $smarty->fetch('progress.tpl');
    }
}

?>