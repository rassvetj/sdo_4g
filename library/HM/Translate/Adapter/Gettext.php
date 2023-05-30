<?php

class HM_Translate_Adapter_Gettext extends Zend_Translate_Adapter_Gettext {

    public $translateJsFileName = '';
    
    protected $versions = '';
    
    /**
     * Генерирует файл перевода для JS
     * 
     * @param string $locale
     */
    public function generateJsTranslate($locale)
    {
        $newVersion = $this->versions;
        
        $translatePath = APPLICATION_PATH.'/../data/cache/locale/'.$locale;
        $versionFile   = $translatePath.'/modification.txt';
        $translateFile = $translatePath.'/translate.js';
        $version = 0;
        
        // проверяем наличие директории с файлом перевода
        if (!file_exists($translatePath)) {
            mkdir($translatePath, 0777, true);
        }
        
        // проверяем наличие файла, хранящего текущую версию перевода и считываем содержимое
        if (file_exists($versionFile)) {
            $version = file_get_contents($versionFile);
        }
        
        // если версия перевода изменилась, то генерируем файл перевода
        if ($newVersion !== $version) {
            file_put_contents($versionFile, $newVersion);
        
            $content = 'window.hm = window.hm || {};
window.hm.dict = window.hm.dict || {};
window.hm.dict.translate = '.json_encode($this->_translate[$locale]).';';
        
            file_put_contents($translateFile, $content);
        }
        // сохраняем ссылку на файл перевода для вставки в head
        $this->translateJsFileName = '/js/hm/locale/'.$locale.'/translate.js?'.$newVersion;
    }
 
    /**
     * Перехватываем загрузку файла перевода для получения даты его последжнего изменения
     */
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $result = parent::_loadTranslationData($filename, $locale, $options);
        
        $newVersion = filemtime($filename);
        
        $this->versions .= "$newVersion;";

        return $result;
    }
}