<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/classes/ToolTip.class.php');

   class Smarty_els extends Smarty {
                   function Smarty_els() {

                           $this->left_delimiter  = "{?";
                           $this->right_delimiter = "?}";

                           $this->template_dir = $_SERVER['DOCUMENT_ROOT']."/template/smarty";
                           $this->compile_dir = APPLICATION_PATH . "/../data/smarty/templates_c";
                           $this->config_dir = $_SERVER['DOCUMENT_ROOT']."/smarty/configs";
                           $this->cache_dir = APPLICATION_PATH . "/../data/smarty/cache";
                           $this->caching = false;

                           $this->assign('tooltip', new ToolTip());
                           $this->assign('encoding', $GLOBALS['controller']->lang_controller->lang_current->encoding);
                           $this->assign('sitepath', $GLOBALS['sitepath']);
                           $this->assign('perm', $GLOBALS['s']['perm']);
                   }

                   function set_template_dir($template_dir) {
                           $this->template_dir = $template_dir;
                   }

                   function alterDirs($application){
                   		if (in_array($application, array('at', 'cms', 'sis'))){
                           $this->template_dir = $_SERVER['DOCUMENT_ROOT']."/{$application}/template/smarty";
                           $this->compile_dir = $_SERVER['DOCUMENT_ROOT']."/{$application}/smarty/templates_c";
                   		}
                   }
   }
?>