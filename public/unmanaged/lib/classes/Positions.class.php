<?php

define('DIR_SYNCHRONIZE', 'positions_import');
define('SYNCHRONIZE_MODE_UPLOAD', 'upload');
// уникальное поле оргединицы - должно иметь одинаковое значение в БД и в файле синхронизации
define('SYNCHRONIZE_MODE_FILESYSTEM', 'filesystem');
define('FIELD_UNIQUE_STRUCTURE', 'soid');
// уникальное поле уч. записи
define('FIELD_UNIQUE_USER', 'MID');
define('DEFAULT_FIELD_UNIQUE_STRUCTURE', 'soid');
define('DEFAULT_FIELD_UNIQUE_USER','MID');
define('ROOT', 'root');
define('DELIMITER_ITEMS', "\\");
define('TYPE_ITEM', 2);
define('TYPE_POSITION', 0);
define('TYPE_LEADER', 1);
define('STRUCTURE_EXISTING', 'existing');
define('STRUCTURE_NEW', 'new');
define('STRUCTURES_EQUAL', 0);
define('STRUCTURES_NOT_EQUAL', 1);
define('EMPTY_QUERY', 'SELECT 1');

// во внешнем файле д.б. определены классы, определяющие конкретный формат файлов и перечень атрибутов для синхронизации
//require_once('lib/classes/PositionsCustom.class.php');
$_update_sequence = array('StructureUpdatePersonDelete', 'StructureUpdatePersonAdd', 'StructureUpdatePersonAttribute', 'StructureUpdateDelete', 'StructureUpdateAdd', 'StructureUpdateAttribute',  'StructureUpdatePosition');

/**
 * Класс для отображения шагов синхронизации. 
 * Умеет отображать текуший шаг в соответствии с выбранным типом синхронизации
 *
 */
class SynchronizeView {

    var $title;

    function display(){
        $tpl = new Smarty_els();
        $tpl->assign_by_ref('this', $GLOBALS['sc']);
        $tpl->assign('progressId',md5($_SESSION['s']['mid'].session_id()));
        switch($GLOBALS['sc']->step) {
            case 0:
                $tpl->assign('progressTitle',_('Анализ структуры организации'));
            break;
            case 1:
                $tpl->assign('progressTitle',_('Синхронизация структуры организации'));
            break;
        }
        $tpl->assign('progressAction',_('Обработка данных'));
        $tpl->assign('okbutton', okbutton());
        $GLOBALS['controller']->setHeader($this->title);
        $GLOBALS['controller']->setContent($tpl->fetch(DIR_SYNCHRONIZE . "/" . $GLOBALS['sc']->mode . "/step" . $GLOBALS['sc']->step . ".inc.tpl"));
        $GLOBALS['controller']->terminate();
    }
    
}

/**
 * Базовый класс контроллера.
 * Варианты синхронизации с различным способом ввода данных наследуют от него
 *
 */
class SynchronizeController {

    var $mode;
    var $step;
    var $view;
    var $model;
    var $log;
    var $number_of_steps;
    
    function initialize(){
        $this->view = new SynchronizeView();
        $this->_set_step();
        $GLOBALS['_person_meta_fields'] = get_meta_fields();        
    }
    
    function _set_step(){
        $step = (integer)$_REQUEST['step'];
        if (isset($step) && ($step < $this->number_of_steps) && ($step > 0)) {
            $this->step = $step;
        } else {
            $this->step = 0;
        }
    }
    
    /**
     * Выполняет текущий шаг синхронизации.
     * (контроллер сам знает, какой шаг текущий).
     *
     */
    function execute(){
        $execute_method = "executeStep" . $this->step;
        $this->$execute_method();
        $step = $this->step + 1;
        $this->view->title = "Синхронизация структуры организации: шаг {$step}";
        $this->view->display();
    }
    
    function _get_structure_new(){}
    
    /**
     * Шаг 0. Показать форму upload
     *
     */
    function executeStep0(){}

    /**
     * Шаг 1. Определить (если нужно, показать) различия 
     *
     * @return int 
     */
    function executeStep1(){
        $GLOBALS['progress'] = new CProgressBar($_POST['progressId']);
        $GLOBALS['progress']->setAction(_('Анализ'));
        
        $total = 5;
        $increase = 100/$total;
        
        $GLOBALS['progress']->setIncrease($increase);
        $this->set_model();
        $GLOBALS['progress']->increase();
        $this->model->initialize();
        $GLOBALS['progress']->increase();
        $this->model->get_structure_existing();
        $GLOBALS['progress']->increase();
        if (!$this->model->get_structure_new()) return;
        $this->model->analyze_people();
        $GLOBALS['progress']->increase();
        $this->model->analyze($this->model->structure_new, $this->model->structure_existing);
        $GLOBALS['progress']->increase();

        $GLOBALS['progress']->saveProgress(-1);
        $GLOBALS['progress']->unlink();        
        
        if (!count($this->model->updates)) {
            $GLOBALS['controller']->setMessage('Структура не изменилась', JS_GO_URL, 'positions.php');
            return STRUCTURES_EQUAL;
        } else {
            $GLOBALS['controller']->persistent_vars->set('structure_updates', $this->model->updates);
            return STRUCTURES_NOT_EQUAL;
        }
    }
    
    /**
     * Внести подтвержденные изменения.
     *
     */
    function executeStep2(){
        $GLOBALS['progress'] = new CProgressBar($_POST['progressId']);
        $GLOBALS['progress']->setAction(_('Инициализация'));
        
        $this->set_model();
        $this->model->initialize();
        $updates = $GLOBALS['controller']->persistent_vars->get('structure_updates');

        $total = 1;
        $total += count($GLOBALS['_update_sequence']);
        if ($total > 0) {
            $increase = 100/$total;
        }
        
        $GLOBALS['progress']->setAction(_('Синхронизация структуры организации'));
        $GLOBALS['progress']->setIncrease($increase);
            
        if (is_array($updates) && count($updates)) {
            $result = true;
            foreach ($GLOBALS['_update_sequence'] as $update_type) {
                if (is_array($update_group = $updates[$update_type])){
                    $enabled = $_POST['ch_' . strtolower($update_type)];
                    foreach ($update_group as $update) {
                        if (!in_array($update->attributes[(strpos($update_type, 'Person')) ? FIELD_UNIQUE_USER : FIELD_UNIQUE_STRUCTURE], $enabled)) continue;
                        $queries = (!is_array($tmp = $update->get_query())) ? array($tmp) : $tmp;
                            foreach ($queries as $query) {
//                          $this->log[] = $query;
                            if (!sql($query)){
                                $result = false;
                            } 
                        }
                    }
                }
                $GLOBALS['progress']->increase();                                                
            }            
            if ($result) {
                $GLOBALS['controller']->setMessage("Синхронизация выполнена успешно", JS_GO_URL, 'positions.php?page_id=m0701');
            } else {
                $GLOBALS['controller']->setMessage("Произошли ошибки. Синхронизация не выполнена", JS_GO_URL, 'positions.php?page_id=m0701');
            }
        }
        
        $GLOBALS['progress']->increase();
        
        $this->_clear_structure();
        
        $GLOBALS['progress']->saveProgress(-1);    
        $GLOBALS['progress']->unlink();        
    }
    
    /**
     * Инициализирует модель данных.
     * здесь StructureModelCustom - класс, соответствующий конкретному формату файла синхронизации
     *
     */
    function set_model(){
//      $this->model = new StructureModelCustom();
    }
    
    /**
     * Удаляет элементы структуры, у которых не найден родитель 
     *
     */
    function _clear_structure(){
        if ($result = $this->_delete_unused_items()) $this->_clear_structure();
    }
    
    function _delete_unused_items(){
        $query = "
            SELECT 
              structure_of_organ.soid
            FROM
              structure_of_organ
              LEFT OUTER JOIN structure_of_organ structure_of_organ1 ON (structure_of_organ.owner_soid = structure_of_organ1.soid)
            WHERE
              (structure_of_organ1.soid IS NULL) AND 
              (structure_of_organ.owner_soid)       
        ";
        $res = sql($query);
        $arr = array();
        while ($row = sqlget($res)) {
            $arr[] = $row['soid'];
        }
        if (count($arr)){
            sql("DELETE FROM structure_of_organ WHERE soid IN ('" . implode("', '", $arr) . "')");
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Должен быть переопределен с учетом способа ввода данных
     *
     */
    function get_resource(){}
}

/**
 * Конроллер, используемый в случае чтения с диска
 * 
 */
class SynchronizeControllerFilesystem extends SynchronizeController {
    
}

/**
 * Конроллер, используемый в случае upload'а файла со структурой
 *
 */
class SynchronizeControllerUpload extends SynchronizeController {

    function initialize(){
        $this->mode = SYNCHRONIZE_MODE_UPLOAD;
        $this->number_of_steps = 3;
        parent::initialize();
    }

    function get_resource(){
        if (is_uploaded_file($tmp = $_FILES['structure_new']['tmp_name']) && $f = fopen($tmp, "r")){
            fclose($f);
            return $tmp;
        } else {
            return false;
        }
    }
}

/**
 * Модель данных. 
 * Различные форматы файлов синхронизации наследуют от этого класса.
 *
 */
class StructureModel{

    /**
     * Модель структуры, существующей в СДО на момент синхронизации
     *
     * @var object StructureItem
     */
    var $structure_existing;
    
    /**
     * Модель структуры согласно загружаемому файлу
     *
     * @var object StructureItem
     */
    var $structure_new;
    
    /**
     * Найденные различия
     *
     * @var StructureUpdate
     */
    var $updates;
    
    /**
     * Список пиплов, существующий в СДО на момент синхронизации 
     *
     * @var unknown_type
     */
    var $people;
    
    /**
     * Список пиплов согласно загружаемому файлу
     *
     * @var unknown_type
     */
    var $people_new;
    
    function initialize($display=false){
        $this->_set_people($display);
        $this->updates = array();
    }
    
    function _set_people($display=false){
        $field_unique_user = ($display ? DEFAULT_FIELD_UNIQUE_USER : FIELD_UNIQUE_USER);
        $this->people = array();
        $query = "
            SELECT 
              People.*, " . 
              $GLOBALS['adodb']->Concat('People.LastName', "' '", 'People.FirstName', "' '", 'People.Patronymic') . " as person_name 
            FROM People
        ";
        $res = sql($query);
        while ($row = sqlget($res)) {
            $person = new Person();
            $person->initialize($row);
            $this->people[$row[$field_unique_user]] = $person;
        }
    }
    
    /**
     * Строит модель существующей структуры
     *
     */
    function get_structure_existing(){
        $arr = $this->_get_structure_existing_arr();
        $this->structure_existing = new StructureItem();
//      возможен только 1 root
        if (isset($arr[ROOT])){
            $this->structure_existing->initialize_recursively(array_shift($arr[ROOT]), &$arr);
        }
    }   
    
    /**
     * Строит и одновременно выводит модель существующей структуры
     *
     */
    function display_structure_existing(){
        $arr = $this->_get_structure_existing_arr(true);
        $this->structure_existing = new StructureItem();
//      возможен только 1 root
        if (isset($arr[ROOT])){
            $this->structure_existing->display_recursively(array_shift($arr[ROOT]), &$arr, true);
        }
    }   
    
    /**
     * формирует массив структуры для использования в initialize_recursively
     * формат:  уникальное поле оргединицы-родителя => массив атрибутов оргединицы-потомка ( имя => значение)
     * должен быть переопределен для поддержки конкретного формата файла синхронизации
     *
     * @param bool $display
     * @return array
     */
    function &_get_structure_existing_arr($display = false){
        $arr = array();
        $field_unique_structure = ($display ? DEFAULT_FIELD_UNIQUE_STRUCTURE : FIELD_UNIQUE_STRUCTURE);
        $field_unique_user = ($display ? DEFAULT_FIELD_UNIQUE_USER : FIELD_UNIQUE_USER);
        $query = "
            SELECT 
              structure_of_organ.*, 
              structure_of_organ_owner." . $field_unique_structure . " as owner_field_unique, 
              People." . $field_unique_user . ",  
              People.MID as people_mid 
            FROM 
              structure_of_organ
              LEFT JOIN People ON (structure_of_organ.mid = People.MID)
              LEFT JOIN structure_of_organ structure_of_organ_owner ON (structure_of_organ.owner_soid = structure_of_organ_owner.soid)
            ORDER BY " . ((dbdriver == "oci8") ? "MOD(structure_of_organ.type,2)" : "structure_of_organ.type%2" ) . " DESC, structure_of_organ.type ASC, structure_of_organ.name ASC
            ";
        $res = sql($query);
        while($row = sqlget($res)){          
            $key = (empty($row['owner_soid'])) ? ROOT : $row['owner_field_unique'];
            if ($key) {
                $arr[$key][] = $row;
            }
        }
        return $arr;
    }
    
    /**
     * Рекурсивно сравнивает два элемента структуры
     * Формирует массив updates из найденных различий
     *
     * @param StructureItem $item_new
     * @param StructureItem $item_existing
     */
    function analyze($item_new, $item_existing){
        $this->analyze_attributes(&$item_new->attributes, &$item_existing->attributes);
        if ($item_new->attributes['type'] != TYPE_ITEM) {
//          $this->analyze_position(&$item_new->person, &$item_existing->person, &$item_new->attributes);
        } elseif (is_array($item_new->children)){
            foreach ($item_new->children as $field_unique => $child) {
                if (!array_key_exists($field_unique, $item_existing->children)) {
                    $this->analyze_branch(&$child, 'add');
                } else {
                    $this->analyze(&$child, &$item_existing->children[$field_unique]);
                    unset($item_existing->children[$field_unique]);
                }
            }
        }
        if (is_array($item_existing->children)){
            foreach ($item_existing->children as $child) {
                $this->analyze_branch(&$child, 'delete');
            }
        }
    }
    
    /**
     * Стравнивает списки пиплов (существующий и новый)
     * Формирует массив updates из найденных различий
     *
     */
    function analyze_people(){
        $people_new = $this->people_new;
        foreach ($this->people as $person_existing) {
            if (!array_key_exists($person_existing->field_unique, $people_new)) {
                if (!empty($person_existing->field_unique)){
                    $update = new StructureUpdatePersonDelete();
                    $update->initialize(&$person_existing->attributes);
                    $this->updates['StructureUpdatePersonDelete'][] = $update;
                }
            } else {
                $this->analyze_attributes_people($person_existing->attributes, $people_new[$person_existing->field_unique]->attributes);
                unset($people_new[$person_existing->field_unique]);
            }
        }
        if(count($people_new)){
            foreach ($people_new as $person_new) {
                $update = new StructureUpdatePersonAdd();
                $update->initialize(&$person_new->attributes);
                $this->updates['StructureUpdatePersonAdd'][] = $update;
            }
        }
    }
    
    /**
     * Сравнивает атрибуты двух пиплов. 
     * Сравнение происходит только по полям, перечисленным в _person_attributes_optional и метаданных (REGISTRATION_FORM)
     * Формирует массив updates из найденных различий
     *
     * @param array $attributes_existing
     * @param array $attributes_new
     */
    function analyze_attributes_people($attributes_existing, $attributes_new){
        $attributes_comparing = array_merge($GLOBALS['_person_attributes_optional'], $GLOBALS['_person_meta_fields']);
        foreach ($attributes_comparing as $key) {
            if ($attributes_existing[$key] != $attributes_new[$key]) {
                $update = new StructureUpdatePersonAttribute();
                $update->initialize($attributes_new);
                $update->key = $key;
                $update->existing = $attributes_existing[$key];
                $this->updates['StructureUpdatePersonAttribute'][] = $update;
            }
        }
    }
    
    /**
     * Рекурсивно формирует массив updates из добавляемой/удаляемой ветки организации
     * (если добавляется/удаляется не leaf, со всеми подчиненными нужно выполнить то же действие)
     *
     * @param StructureItem $item   удаляемы узел
     * @param string        $update_action  зачения add|delete
     */
    function analyze_branch($item, $update_action){
        $class = 'StructureUpdate' . ucfirst($update_action);
        $update = new $class();
        $update->initialize($item->attributes);
        $this->updates[$class][] = $update;
        if (is_array($item->children)) {
            foreach ($item->children as $child) {
                $this->analyze_branch(&$child, $update_action);
            }
        }
        if ($update_action == 'add'){
            $this->analyze_position(&$item->person, null, &$item->attributes);      
        }
    }
    
    /**
     * Сравнивает человека в должности. если есть изменение - добавляет в масив updates
     *
     * @param Person    $person_new
     * @param Person    $person_existing
     * @param array     $attributes_new 
     */
    function analyze_position($person_new, $person_existing, $attributes_new){
        if ($person_new->field_unique != $person_existing->field_unique){
            $update = new StructureUpdatePosition();
            $update->initialize(&$attributes_new);
            $update->person_existing = $person_existing->field_unique;
            $this->updates['StructureUpdatePosition'][] = $update;
        }
    }
    
    /**
     * Сравнивает атрибуты орг.единиц и должностей. если есть изменение - добавляет в масив updates
     *
     * @param array $attributes_new
     * @param array $attributes_existing
     */
    function analyze_attributes($attributes_new, $attributes_existing){
        foreach ($GLOBALS['_structure_attributes_optional'] as $key => $value) {
            if ($attributes_existing[$key] != $attributes_new[$key]) {
                $update = new StructureUpdateAttribute();
                $update->initialize($attributes_new);
                $update->key = $key;
                $update->existing = $attributes_existing[$key];
                $this->updates['StructureUpdateAttribute'][] = $update;
            }
        }
    }
}

/**
 * Элемент структуры организации.
 * Должен быть переопределен для конкретного 
 *
 */
class StructureItem {
    
    /**
     * Атрибуты из базы (или импортируемого файла)
     *
     * @var array
     */
    var $attributes;
    
    /**
     * Параметры отображеня. определяются на основе $attributes.
     *
     * @var unknown_type
     */
    var $display_attributes;
    
    /**
     * Массив подчиненных StructureItem
     *
     * @var array
     */
    var $children;
    
    /**
     *  Рекурсивно инициализирует элемент структуры организации.
     *
     * @param array $item   Массив атрибутов оргединицы (строка глобального массива оргединиц)
     * @param array $items  Глобальный массив оргединиц
     */
    function initialize_recursively($item, $items){
        $this->attributes = $item;
        if (is_array($items[$this->attributes[FIELD_UNIQUE_STRUCTURE]])){
            foreach ($items[$this->attributes[FIELD_UNIQUE_STRUCTURE]] as $item) {
                $class = ($item['type'] == TYPE_ITEM) ? get_class($this) : get_class($this) . 'Position';
                $child = new $class();
                $child->initialize_recursively($item, &$items);
                $this->children[$child->attributes[FIELD_UNIQUE_STRUCTURE]] = $child;
            }
        }
    }
    
    /**
     * Рекурсивно инициализирует и отображает элемент структуры организации.
     *
     * @param array $item   Массив атрибутов оргединицы (строка глобального массива оргединиц)
     * @param array $items  Глобальный массив оргединиц
     */
    function display_recursively($item, $items, $display=false){
        $this->attributes = $item;
        $this->set_display_attributes();
        if ($this->display_attributes['filtered']) {
            $this->display();
        }
        if (is_array($items[$this->attributes[DEFAULT_FIELD_UNIQUE_STRUCTURE]])){
            $i = 1;
            foreach ($items[$this->attributes[DEFAULT_FIELD_UNIQUE_STRUCTURE]] as $item) {
                $class = ($item['type'] == TYPE_ITEM) ? get_class($this) : get_class($this) . 'Position';
                $child = new $class();
                $child->display_attributes['id'] = $this->display_attributes['id'] . "_" . $i++;
                if ($this->display_attributes['filtered']) {
                    $child->display_attributes['nesting'] = $this->display_attributes['nesting']+1;
                }
                $child->display_recursively($item, &$items, $display);
                $this->children[$child->attributes[DEFAULT_FIELD_UNIQUE_STRUCTURE]] = $child;
            }
        }
    }
    
    /**
     * Определяет атрибуты для отображения оргединицы
     *
     */
    function set_display_attributes(){
        $this->display_attributes['filtered'] = $GLOBALS['soidFilter']->is_filtered($this->attributes['soid']); 
        $this->display_attributes['class'] = ($this->attributes['type'] == TYPE_ITEM) ? 'item' : 'position';    
        $this->display_attributes['checked'] = (isset($_POST['che'][$this->attributes['soid']])) ? 'checked' : '';  
        if (!isset($this->display_attributes['id'])) $this->display_attributes['id'] = "pos_0_1";   
        if ($this->attributes['type'] == TYPE_ITEM || isset($this->person)) $GLOBALS['js_code'][] = "ArrayMid[" . $GLOBALS['js_code_cnt']++ . "] = {$this->attributes['soid']};";
        $this->display_attributes['hidden'] = ($this->display_attributes['nesting']) ? "style='display: none;'" : "";               
        switch ($_POST['action']){
            case 4:
                if (isset($this->person) && isset($_POST['che'][$this->attributes['soid']])) {
                    $this->display_attributes['message'] = check_mid_udv_str($this->attributes['soid'], $this->person->attributes['MID']) ? "<img height=11 src='images/icons/ok.gif'/>" : "<img height=11 src='images/icons/cancel.gif'/>";
                } else {
                    $this->display_attributes['message'] = "&nbsp;";
                }
                break;
            case 5:
                if ((isset($this->person) || ($this->attributes['type'] == TYPE_ITEM)) && isset($_POST['che'][$this->attributes['soid']])) {
                    $this->display_attributes['message'] = $study_ranks[$r['soid']] = (int) get_study_rank($this->attributes['soid'], $this->attributes['type'], $this->person->attributes['MID']) . "%";
                } else {
                    $this->display_attributes['message'] = "&nbsp;";
                }
                break;
            default:
                break;
        }
    }
    
    function display(){
        $GLOBALS['position_tpl']->assign_by_ref('item', $this);
        $GLOBALS['position_tpl']->display('positions.tpl');
    }
}

/**
 * Должность структуры организации
 *
 */
class StructureItemPosition extends StructureItem {

    /**
     * Person, находящийся в этой должности
     *
     * @var unknown_type
     */
    var $person;
    
    var $display;
    
    /**
     * Инициализирует должность. 
     * рекурсии как таковой нет
     *
     * @param array $item   Массив атрибутов должности (строка глобального массива оргединиц)
     * @param array $items  Глобальный массив оргединиц
     */
    function initialize_recursively($item, $items){
        $field_unique_user = ($this->display ? DEFAULT_FIELD_UNIQUE_USER : FIELD_UNIQUE_USER);
        $this->attributes = $item;
        if ($item[$field_unique_user]) {
            $this->person = &$GLOBALS['model']->people[$item[$field_unique_user]];
        }
    }

    /**
     * Инициализирует и отображает должность
     *
     * @param array $item   Массив атрибутов  должности (строка глобального массива оргединиц)
     * @param array $items  Глобальный массив оргединиц
     */
    function display_recursively($item, $items, $display = false){
        $this->display = $display;
        $this->initialize_recursively($item, &$items);
        $this->set_display_attributes();
        if ($GLOBALS['soidFilter']->is_filtered($this->attributes['soid'])) {
            $this->display();
        }

    }
}

/**                          ©
 * Человек - это звучит гордо
 *
 */
class Person{
    
    /**
     * Массив атрибутов
     *
     * @var unknown_type
     */
    var $attributes;
    
    /**
     * Значение уникального атрибута (по которому происходит сравнение)
     *
     * @var unknown_type
     */
    var $field_unique;
    
    function initialize($arr){
        if (!empty($arr['Information'])){
            $meta_data = extract_meta($arr['Information']);
            foreach ($meta_data as $meta_item) {
                $arr[$meta_item['name']] = $meta_item['value'];
            }
        }
        $this->attributes = $arr;
        $this->field_unique = $arr[FIELD_UNIQUE_USER];
    }
}

/**
 * Базовый класс. Различные варианты изменений в структуре наследуют от него. 
 *
 */
class StructureUpdate {

    /**
     * Атрибуты измененного элемента структуры
     *
     * @var array
     */
    var $attributes;
    
    function initialize($attributes = false){
        if ($attributes) $this->attributes = $attributes;
    }
    
    /**
     * Возвращает уникальное поле для оргединицы
     *
     * @return string
     */
    function get_unique_structure(){
        return $this->attributes[FIELD_UNIQUE_STRUCTURE];   
    }
    
    /**
     * Возвращает уникальное поле для человека
     *
     * @return string
     */
    function get_unique_user_field(){
        return $this->attributes[FIELD_UNIQUE_USER];    
    }
    
    /**
     * Возвращает объект Person либо USER_NOT_FOUND 
     * ищет в модели данных контроллера
     *
     * @param $forced   string  (уникальный атрибут человека) Если нужо получить человека НЕ из $this->attributes[FIELD_UNIQUE_USER]
     * @return mixed
     */
    function get_unique_user($force = false){
        if ($this->attributes['type'] != TYPE_ITEM){
            $id = ($force !== false) ? $force : $this->attributes[FIELD_UNIQUE_USER]; 
            if (!empty($id)){
                if (isset($GLOBALS['sc']->model->people[$id]) && is_a($GLOBALS['sc']->model->people[$id], 'Person')) {
                    return $GLOBALS['sc']->model->people[$id];
                } elseif (isset($GLOBALS['sc']->model->people_new[$id]) && is_a($GLOBALS['sc']->model->people_new[$id], 'Person')) {
                        return $GLOBALS['sc']->model->people_new[$id];
                } elseif ($user = $this->get_user_unique_db()) {
                    return $user;
                } else {
                    return USER_NOT_FOUND;
                }
            } else {
                return USER_NOT_FOUND;
            }
        }
        return '';
    }
    
    /**
     * Возвращает объект Person либо USER_NOT_FOUND 
     * ищет в БД
     *
     * @return mixed
     */
    function get_user_unique_db(){
        if(!empty($this->attributes[FIELD_UNIQUE_USER])){
            $res = sql("SELECT * FROM People WHERE " . FIELD_UNIQUE_USER . "='{$this->attributes[FIELD_UNIQUE_USER]}'");
            if ($row = sqlget($res)){
                $person = new Person();
                $person->initialize($row);
                return $person;
            }
            return false;
        }
    }

    /**
     * Возвращает имя человека 
     *
     * @param $forced   string  (уникальный атрибут человека) Если нужо получить имя человека НЕ из $this->attributes[FIELD_UNIQUE_USER]
     * @return string
     */
    function get_unique_user_name($force = false){
        switch ($user = $this->get_unique_user($force)) {
            case USER_NOT_FOUND:
                return "-- пользователь не найден --";
                break;
            case USER_NOT_SET:
                return "-- вакантная должность --";
                break;
            default:
                return $user->attributes['person_name'];
                break;
        }
    }
    
    /**
     * Сериализует метаданные Person'а в атрибут Information 
     *
     */
    function prepare_meta(){
        $reg_form_items = explode(";", REGISTRATION_FORM);                                 
        $meta_information = "";
        foreach($reg_form_items as $key => $value) {
          $meta_information .= "block=".$value."~";
          $meta_information .= trim(set_metadata($this->attributes, get_posted_names($this->attributes), $value),"~");
          $meta_information .= "[~~]";
        }
        $this->attributes['Information'] = trim($meta_information, "[~~]");
        
        if (isset($GLOBALS['_person_attributes_meta']) && is_array($GLOBALS['_person_attributes_meta'])) {
            foreach($GLOBALS['_person_attributes_meta'] as $value) {
                unset($this->attributes[$value]);
            }
        }
    }

    /**
     * Возвращает soid оргединицы по его уникальному полю
     *
     * @param unknown_type $field_unique
     * @return unknown
     */
    function get_soid($field_unique){
        $query = "SELECT * FROM structure_of_organ WHERE " . FIELD_UNIQUE_STRUCTURE ."='{$field_unique}'";
        $res = sql($query);
        if ($row = sqlget($res)){
            return $row['soid'];
        }
    }
    
    /**
     * Возвращает текст SQL-запроса для фиксации данного различия
     * должен быть переопределен в потомках
     *
     */
    function get_query(){}
}

/**
 * Изменился атрибут оргединицы
 *
 */
class StructureUpdateAttribute extends StructureUpdate {
    
    var $key;
    var $existing;
    
    function get_key(){
        return $GLOBALS['_structure_attributes_aliases'][$this->key];
    }

    function get_new(){
        return $this->attributes[$this->key];
    }
    
    function get_query(){
        return "UPDATE structure_of_organ SET `{$this->key}`= ".$GLOBALS['adodb']->Quote($this->attributes[$this->key])." WHERE " . FIELD_UNIQUE_STRUCTURE . "='{$this->attributes[FIELD_UNIQUE_STRUCTURE]}'";
    }
}

/**
 * Удалена оргединица
 *
 */
class StructureUpdateDelete extends StructureUpdate {
    
    function get_query(){
        return "DELETE FROM structure_of_organ WHERE soid='{$this->attributes['soid']}'";
    }
    
}

class StructureUpdateAdd extends StructureUpdate {

    function get_query(){
        if ($this->attributes['owner_soid'] = $this->get_soid($this->attributes['owner_field_unique'])){
            unset($this->attributes['owner_field_unique']);     
            unset($this->attributes[FIELD_UNIQUE_USER]);
            
            foreach(array_keys($this->attributes) as $key) {
                $this->attributes[$key] = $GLOBALS['adodb']->Quote($this->attributes[$key]);            
            }
            
            return "INSERT INTO structure_of_organ (" . implode(", ", array_keys($this->attributes)) . ") VALUES (" . implode(", ", $this->attributes) . ")";
        }
        return EMPTY_QUERY;
    }
    
}
/**
 * Изменился человек, занимающий должность
 *
 */
class StructureUpdatePosition extends StructureUpdate {
    
    var $person_existing;
    
    function get_query(){
        $user = $this->get_unique_user();
        return "UPDATE structure_of_organ SET mid='{$user->attributes['MID']}' WHERE " . FIELD_UNIQUE_STRUCTURE ."=".$GLOBALS['adodb']->Quote($this->attributes[FIELD_UNIQUE_STRUCTURE]);
    }
}

/**
 * Удален человек
 *
 */
class StructureUpdatePersonDelete extends StructureUpdate {

    function get_query(){
        if ($user = $this->get_unique_user()){
//          $return[] =  "DELETE FROM People WHERE MID={$user->attributes['MID']}";
            $return[] =  "DELETE FROM Students WHERE MID={$user->attributes['MID']}";
            $return[] =  "DELETE FROM Teachers WHERE MID={$user->attributes['MID']}";
            $return[] =  "DELETE FROM deans WHERE MID={$user->attributes['MID']}";
            $return[] =  "DELETE FROM admins WHERE MID={$user->attributes['MID']}";
        }
        return $return;
    }
}

/**
 * Добавлен человек
 *
 */
class StructureUpdatePersonAdd extends StructureUpdate {

    function get_query(){
        $this->prepare_meta();
        foreach ($GLOBALS['_person_meta_fields'] as $field) {
            unset($this->attributes[$field]);           
        }
        unset($this->attributes['person_name']);        
        $this->attributes['Login'] = strtolower(substr($this->attributes['EMail'], 0, strpos($this->attributes['EMail'], '@')));
        $str = strtolower(substr($this->attributes['EMail'], strpos($this->attributes['EMail'], '@')+1));       
        $this->attributes['Password'] = "565491d704013245";     
        
        foreach(array_keys($this->attributes) as $key) {
            $this->attributes[$key] = $GLOBALS['adodb']->Quote($this->attributes[$key]);            
        }   
             
        return "INSERT INTO People (" . implode(", ", array_keys($this->attributes)) . ") VALUES (" . implode(", ", $this->attributes) . ")";
    }
}

/**
 * Изменены атрибуты человека
 *
 */
class StructureUpdatePersonAttribute extends StructureUpdateAttribute {
    
    function get_key(){
        return $GLOBALS['_person_attributes_aliases'][$this->key];
    }

    function get_query(){
        if (in_array($this->key, $GLOBALS['_person_meta_fields'])){
            $this->prepare_meta();
            $this->key= 'Information';
        }
        return "UPDATE People SET `{$this->key}`= ".$GLOBALS['adodb']->Quote($this->attributes[$this->key])." WHERE " . FIELD_UNIQUE_USER . "= ".$GLOBALS['adodb']->Quote($this->attributes[FIELD_UNIQUE_USER]);
    }
}
?>