<?php

class HM_View_Helper_ConcatBreadCrumbs extends HM_View_Helper_Abstract
{
    public function concatBreadCrumbs()
    {

    	$this->view->headScript()->appendFile($this->view->serverUrl('/js/content-modules/breadcrumbs.js'));
        $content = '';

        /*
        if (null !== $this->view->getUnmanagedNavigation()) {
            $content  .=
                $this->view
                    ->navigation()
                    ->breadcrumbs($this->view->getUnmanagedNavigation())
                    ->setMinDepth(0)
                    ->__toString();
        }
        */


       $separator = '&nbsp;<span class="separator">&#0155;</span>&nbsp;';

        if (null !== $this->view->getSubjectNavigation()) {
            $content  .=
                $this->view
                    ->navigation()
                    ->breadcrumbs($this->view->getSubjectNavigation())
                    ->setView($this->view)
                    ->setLinkLast(true)
                    ->setPartial('breadcrumb.tpl')
                    ->setMinDepth(0)
                    ->setSeparator($separator)
                    ->__toString();
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();

        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());


        if (!in_array($page, array(
            'subject-index-index',
            'course-index-index',
            'user-edit-card'
        ))) {

            if (null !== $this->view->getContextNavigation()) {
                $content  .=
                    $this->view
                        ->navigation()
                        ->breadcrumbs($this->view->getContextNavigation())
                        ->setView($this->view)
                        ->setSeparator($separator)
                        ->setPartial('breadcrumb.tpl')
                        ->setLinkLast(true)
                        ->setMinDepth(0)
                        ->setMaxDepth(1)
                        ->__toString();
            }
        }
        // Mega hack
        $content = str_replace($separator . '<a></a><a></a>', '', $content);

        return $content;
    }

    /*
     * Some example of translation actions.xml into xml which we can use in HM_Config_Xml
     *
     *
     * public function prepareActions()
    {
        $xmlString = $GLOBALS['domxml_object']->dump_mem(true);


        function setAllow ($str)
        {
            $array = explode(',', $str);
            $res = array();
            foreach ($array as $val) {
                if (substr($val, 0, 1) !== '~') {
                    $res[] = $val;
                }
            }
            return implode(',', $res);
        }
        function setDeny ($str)
        {
            $array = explode(',', $str);
            $res = array();
            foreach ($array as $val) {
                if (substr($val, 0, 1) == '~') {
                    $res[] = substr($val, 1);
                }
            }
            return implode(',', $res);
        }

        function randInt(){
            return mt_rand(1, 99999999);
        }

        function getModule($str){
            $ss = explode('/', $str);
            return $ss[0] == "" ? 'index' : $ss[0];
        }
        function getController($str){
            $ss = explode('/', $str);
            return $ss[1] == "" ? 'index' : $ss[1];
        }
        function getAction($str){
            $ss = explode('/', $str);
            return $ss[2] == "" ? 'index' : $ss[2];
        }

        function getResource($str){
            return sprintf('mca:%s:%s:%s', getModule($str), getController($str), getAction($str));
        }

        $xml = new DOMDocument;
        $xml->loadXML($xmlString);

        $xsl = new DOMDocument;
        $xsl->load(APPLICATION_PATH . '/settings/actionsTransform.xsl');

        // Configure the transformer
        $proc = new XSLTProcessor;
        $proc->registerPHPFunctions();
        $proc->importStyleSheet($xsl); // attach the xsl rules
        $string = $proc->transformToXML($xml);
        $string = str_replace('<pages/>','', $string);
        $string = str_replace('<allow/>','', $string);
        $string = str_replace('<deny/>','', $string);
        $string = preg_replace('#<pages>[\\s]+</pages>#iS','', $string);
        try{
            $config = new HM_Config_Xml($string, 'nav');
            $container = new HM_Navigation($config, Zend_Registry::get('serviceContainer')->getService('Acl'), null);
        } catch (Exception $e){
            //print $e->getMessage();
        }
        return $container;
    }*/



}