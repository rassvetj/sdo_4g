<?php

/**
 * Общее API для разработки фронтэнда и для получения
 */
class HM_View_FrontendVersion {

    protected static function getVersionFileName()
    {
        if (!defined('FRONTEND_DIR_ROOT')) {
            $rootDir = realpath(APPLICATION_PATH.'/../');
        } else {
            $rootDir = FRONTEND_DIR_ROOT;
        }
        return $rootDir.'/data/frontend/version.json';
    }

    public static function getCurrentBuild()
    {
        $versionInfo = self::getVersionInfo();

        return $versionInfo['build'];
    }

    public static function getVersionInfo()
    {
        $buildFileName = self::getVersionFileName();

        if (file_exists($buildFileName)) {
            $versionInfo = json_decode(file_get_contents($buildFileName), true);
        } else {
            $versionInfo = array(
                'build'     => 0,
                'timestamp' => time()
            );
        }

        return $versionInfo;
    }

    public static function updateVersion()
    {
        $versionInfo = self::getVersionInfo();
        $versionFileName = self::getVersionFileName();

        $versionInfo['build']++;
        $versionInfo['timestamp'] = time();

        $versionPath = dirname($versionFileName);

        if (!file_exists($versionPath)) {
            mkdir($versionPath, 0777, true);
            chmod($versionPath, 0777);
        }

        file_put_contents($versionFileName, json_encode($versionInfo));
    }
}