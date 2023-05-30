<?php

/**
 * Набор классов для работы с языками и gettext
 * Чтобы включить тот или иной язык нужно:
 * 1. убедиться, что он defined и есть в $lang_locales, $lang_aliases, $lang_encodings; если нет - добавить;
 * 2. включить его в $langs_available;
 * [3]. если нужно - указать как $lang_default;
 *
 * @author lex
 * @package defaultPackage
 */


//define ("KZ", 3);
//define ("EN", 2);
//define ("RU", 1);
//define ("TRANSLIT", 15);

//$ini = parse_ini_file(APPLICATION_PATH.'/settings/config.ini');
$ini = $GLOBALS['ini'];

$lang_locales = $lang_aliases = $lang_encodings = $lang_encodings_syn = $langs_available = array();

if (isset($ini['languages'])) {
    foreach($ini['languages'] as $langId => $langParam) {
        define($langId, $langId);
        $lang_locales[$langId] = $langParam['locale'];
        $lang_aliases[$langId] = $langParam['name'];
        $lang_encodings[$langId] = 'UTF-8';
        $lang_encodings_syn[$langId] = 'utf8';
        $langs_available[]     = $langId;
    }
}

//$lang_locales = array(TRANSLIT => 'ru_TRANSLIT', EN => 'en_US', RU => 'ru_RU', KZ => 'kz_KZ');
//$lang_aliases = array(TRANSLIT => 'translit', EN => 'english', RU => 'русский', KZ => _('казахский'));
//$lang_encodings = array(TRANSLIT => 'UTF-8', EN => 'UTF-8', RU => 'UTF-8', KZ => 'UTF-8');
//$lang_encodings_syn = array(TRANSLIT => 'utf8', EN => 'utf8', RU => 'utf8', KZ => 'utf8');

$lang_default = array_search($ini['resources']['locale']['default'], $lang_locales);

//$langs_available = array(RU);
//$langs_available = array(EN, RU);

class LangController {

    var $lang_current;
    var $langs;

    function initialize() {
        foreach ($GLOBALS['langs_available'] as $id){
            $lang = new Lang();
            $lang->initialize($id);
            $this->langs[$id] = $lang;
            if ($lang->current) {
                $this->lang_current = &$this->langs[$id];
            }
        }
        define("DIR_LANG", $this->lang_current->dir);
    }

    function _define_langs(){

    }
}

class Lang {
    var $id;
    var $title;
    var $dir;
    var $url;
    var $locale;
    var $encoding;
    var $current;

    function initialize($id){
        if (in_array($id, $GLOBALS['langs_available'])) {
            $this->id = $id;
            $this->title = $GLOBALS['lang_aliases'][$this->id];
            $this->locale = $GLOBALS['lang_locales'][$this->id];

//          $this->encoding = 'UTF-8';
            $this->encoding = $GLOBALS['lang_encodings'][$this->id];
        }
        if (Lang::_get_curretnt_lang() == $id) {
            $this->current = true;
//            $this->initialize_current();
//            $this->_bind_current();
        }
        if ($id == $GLOBALS['lang_default']){
            $this->dir = '';
			if (file_exists($_SERVER['DOCUMENT_ROOT']."/locale/" . (string)$this->locale . '/els_locale')) {
			    $this->dir = "/locale/" . (string)$this->locale . '/els_locale';
			}
        } else {
			if (file_exists($_SERVER['DOCUMENT_ROOT']."/locale/" . (string)$this->locale . '/els_locale')) {
				$this->dir = "/locale/" . (string)$this->locale . '/els_locale';
			}
        }
    }

    function _get_curretnt_lang(){

        if (isset($_GET['lang'])) {
            $value = (in_array($_GET['lang'], $GLOBALS['langs_available'])) ? $_GET['lang'] : $GLOBALS['lang_default'];
            $GLOBALS['controller']->persistent_vars->set('lang', $_GET['lang']);
//          $GLOBALS['controller']->persistent_vars->set('lang', $_GET['lang'], PERSISTENT_VAR_USE_COOKIE);
//          $GLOBALS['controller']->persistent_vars->destroy('page_id');
        } elseif ($lang = $this->_get_user_lang()) {
            $value = (in_array($lang, $GLOBALS['langs_available'])) ? $lang : $GLOBALS['lang_default'];
            $GLOBALS['controller']->persistent_vars->set('lang', $lang);
        } elseif ($lang = $_COOKIE['hmlang']) {
            $value = (in_array($_COOKIE['hmlang'], $GLOBALS['langs_available'])) ? $_COOKIE['hmlang'] : $GLOBALS['lang_default'];
            $GLOBALS['controller']->persistent_vars->set('lang', $_COOKIE['hmlang']);
        } elseif ($lang = $GLOBALS['controller']->persistent_vars->get('lang')) {
            $value = (in_array($lang, $GLOBALS['langs_available'])) ? $lang : $GLOBALS['lang_default'];
        } elseif ($GLOBALS['ini']['resources']['locale']['force']) {
            $languages = $GLOBALS['ini']['languages'];
            foreach($languages as $lang => $langLocale) {
                if (strtolower($GLOBALS['ini']['resources']['locale']['default']) == strtolower($langLocale['locale'])) {
                    $value = $lang;
                    break;
                }
            }
        } else {
            require_once('Zend/Locale.php');
            $l = new Zend_Locale();
            $accepted = $l->getBrowser();
            if (is_array($accepted) && count($accepted)) {
                $languages = $GLOBALS['ini']['languages'];
                foreach($accepted as $acceptedLocale => $weight) {
                    foreach($languages as $lang => $langLocale) {
                        if (strtolower($acceptedLocale) == strtolower($langLocale['locale'])) {
                            $value = $lang;
                            break 2;
                        }
                    }
                }
            }
            if (!isset($value)) {
            $value = $GLOBALS['lang_default'];
            }
        }
        return $value;
    }

    function _get_user_lang()
    {
        if ($_SESSION['s']['mid'] > 0) {
            $sql = "SELECT lang FROM People WHERE MID = '".(int) $_SESSION['s']['mid']."'";
            $res = sql($sql);
            if ($row = sqlget($res)) {
                return $row['lang'];
            }
        }

        return false;
    }

    function initialize_current(){
        putenv("LANG={$this->locale}");

//        ВНИМАНИЕ!
//        С закомментированым setlocale может не работать локализация
//        С раскомментированным - ошибка с insert'ом чисел с плавающей точкой в БД (напр., при тестировании)
//        Похоже, зависит от каких-то настроек сервера; под виндами всё хорошо
//
//        setlocale(LC_ALL,$this->locale.'.'.$GLOBALS['lang_encodings_syn'][$this->id]);
    }

    function _bind_current(){
        $domain = 'messages';
        bindtextdomain ($domain, $_SERVER['DOCUMENT_ROOT'] . "/locale");
        textdomain ($domain);
        bind_textdomain_codeset($domain, $this->locale);
    }
}
?>