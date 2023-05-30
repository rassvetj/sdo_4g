<?
/**
 * Здесь определяются метаданные, специфичные для проекта.
 *
 * @author lex
 * @package els
 */

/**
 *          SELECT_TEXT
 *  
 *          $data[$i]['name']  = $type;
            $data[$i]['type']  = "select_text";
            $data[$i]['size'] = 3;
            $data[$i]['func'] = 'metadata_test';
            $data[$i]['title'] = "";
            $data[$i]['value'] = "";
            $i++;
 * 
 */

$GLOBALS['application_metadata'] = array(
_('Текстовое поле')=>'add_info',
_('Контакты')=>'contacts',
_('Почтовый адрес')=>'address_postal',
_('Паспортные данные')=>'passport',
_('Дата рождения')=>'dateB');

?>