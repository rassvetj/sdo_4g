<?php

class HM_View_Helper_HmBootstrap extends HM_View_Helper_Abstract
{
    const COOKIE_DEBUG_NAME = 'hm-dev-debug-enabled';

    protected static $_needInit = true;

    public function hmBootstrap()
    {
        if (self::$_needInit) {
            self::_initParams();
        }

        return $this;
    }

    protected static $debug = false;
    protected static $frontDebug = false;
    protected static $baseUrl = '/hm';
    protected static $build = false;
    protected static $buildDate = false;

    protected static $moduleJsList = array(
        'hm' => 'hm.min.js',
    );

    protected static function _initParams()
    {
        self::$debug = (bool) (int) Zend_Registry::get('config')->debug;

        if (self::$debug) {
            self::$frontDebug = isset($_COOKIE[self::COOKIE_DEBUG_NAME]) && $_COOKIE[self::COOKIE_DEBUG_NAME] === '1';
        }

        $versionInfo = HM_View_FrontendVersion::getVersionInfo();

        self::$build     = self::$frontDebug ? time() : $versionInfo['build'];
        self::$buildDate = (self::$frontDebug ? time() : $versionInfo['timestamp']) * 1000;
    }

    protected static function compileCss($theme = 'default')
    {
        include_once APPLICATION_PATH.'/../tools/frontend/manager/init.php';

        \frontend\HM::init(false);

        $projectCode = \frontend\HM::getProject();

        new \frontend\module\sass\service\SassCompiler($projectCode, $theme, 'DEVELOPMENT', true);

    }

    protected static function injectStyleSheet($type = 'screen')
    {
        if (self::$frontDebug) {
            $url = self::$baseUrl.'/css/debug/themes/default/'.$type.'.css?v='.self::$build;
        } else {
            $url = self::$baseUrl.'/css/themes/default/'.$type.'.css?v='.self::$build;
        }

        $id = 'hm-core';

        if ($type !== 'screen') {
            $id .= '-'.$type;
        }

        $id .= '-stylesheet';

        switch ($type) {
            case 'print':
                $media = 'print';
                break;
            default:
                $media = 'all';
        }

        return '<link href="'.$url.'" rel="stylesheet" id="'.$id.'" media="'.$media.'">';
    }

    public function getCss()
    {
        if (self::$frontDebug) {
            self::compileCss();
        }

        $result  = self::injectStyleSheet('screen');
        $result .= '<!--[if IE]>';
        $result .= self::injectStyleSheet('ie');
        $result .= '<![endif]-->';
        $result .= self::injectStyleSheet('print');

        return $result;

    }

    protected $jsDebugViaFullBuild = true;

    public function getJS()
    {
        $build     = self::$build;
        $buildDate = self::$buildDate;

        $baseUrl = self::$baseUrl;

        $result = '<script>'.
                        'window.hm = window.hm || {}; '.
                        'hm.dict = hm.dict || {}; '.
                        'hm.isDebug = '.json_encode(self::$frontDebug).'; '.
                        'hm.debugAllowed = '.json_encode(self::$debug).'; '.
                        'hm.basePath = '.json_encode($baseUrl).'; '.
                        'hm.build = '.$build.'; '.
                        'hm.buildDate = new Date('.$buildDate.');'.
                        'hm.appClass = "hm.core.Application";'.
                        'hm.serverInitTime = '.time().' * 1000;'.
                        'hm.clientInitTime = Date.now();'.
                   '</script>';

        if (self::$frontDebug) {

            if (!$this->jsDebugViaFullBuild) {
                // подгрузка в реальном времени
                $classes = array(
                    'HM',
                    'hm.core.ClassManager',
                    'hm.core.Class',
                    'hm.core.DOM',
                    'hm.core.PopupManager',
                    'hm.core.HardwareDetect',
                    'hm.core.Date',
                    'hm.core.Url',
                    'hm.core.BaseService',
                    'hm.core.Application'
                );

                $result .= self::injectScript('/js/lib/handlebars-v1.3.0.js?v='.$build);

                foreach ($classes as $className) {
                    $result .= self::injectScript('/dev_tools/js/get/index?class='.rawurlencode($className).'&amp;v='.$build);
                }
            } else {

                // дебаг одним файлом проекта
                $result .= self::injectScript('/dev_tools/js/get/full?v='.$build);
            }

        } else {
            $files = array();

            foreach (self::$moduleJsList as $moduleFileName) {
                $files[] = '/js/'.$moduleFileName;
            }

            foreach ($files as $fileName) {
                $result .= self::injectScript($baseUrl.$fileName.'?v='.$build);
            }

        }

        $result .= '<script>'.
                         'HM.init();'.
                   '</script>';


        return $result;
    }

    protected static function injectScript($fileName)
    {
        return '<script src="'.$fileName.'"></script>';
    }

}