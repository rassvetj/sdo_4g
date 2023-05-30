<?php

require_once("lib/classes/xml2array.class.php");

$lom_voc_status = 
array(
    'draft'=>'черновой', 
    'final'=>'завершенный', 
    'revisited'=>'пересмотренный', 
    'unavailable'=>'недоступен'
);

$lom_voc_role = 
array(
    'author' => 'Автор',
    'publisher' => 'Издатель',
    'unknown' => 'Неизвестно',
    'initiator'=>'Инициатор',
    'terminator'=>'завершающий',
    'validator'=>'Утверждающее лицо',
    'editor'=>'Редактор',
    'graphical designer'=>'Дизайнер графики',
    'technical implementer'=>'Технический исполнитель',
    'content provider'=>'Контент-провайдер',
    'technical validator'=>'Техническое утверждающее лицо',
    'educational validator'=>'Педагогическое утверждающее лицо',
    'script writer'=>'Сценарист',
    'instructional designer'=>'Учебный проектировщик'
);

class CPackage extends CBook {
           
    /**
    * Constructor
    */
    function CPackage($data) {

        $ret = parent::CBook($data);
        return $ret;
        
    }
    
    /**
    * parsing learning object package
    */
    function parseLOP($zip) {
        
      global $tmpdir, $wwf;
                  
      /**
      * Проверка на превышение размера файла
      */
      if ($zip['size'] > LOP_MAX_UPLOAD_SIZE) {          
          $this->msg .= "Размер файла превышает порог<br>";
          return false;
      }
      
      /**
      * Разархивирование
      */
      if (!move_uploaded_file($zip['tmp_name'], $tmpdir."/".$zip['name'])) {
          $this->msg .= "Нет файла учебных материалов ".$zip['name']."<BR>";
          return false;
      }
      else {
      
        $pkgdir = $wwf."/library/".(int) $this->bid;
        if ($this->extractLOPzip($tmpdir."/".$zip['name'], $this->bid)) {
            
            /**
            * Занесение в БД
            */
            if ($objects = $this->parse_xml($pkgdir)) {
                            
            $first = true;
            if (count($objects->elements) > 0) {
                foreach ($objects->elements as $manifest => $organizations) {
                    foreach ($organizations as $organization => $items) {
                        foreach ($items as $identifier => $item) {

                            if (is_array($item->metadata) && count($item->metadata) && !empty($item->material)) {
                                
                                $ruslom = new CRusLom(array());
                                $ruslom->ruslom_metadata = $item->metadata;
                                $ruslom->filename = '/'.(int)$this->bid.'/'.$item->material;
                                $ruslom->is_package = 1;
                                
                                if ($first) {
                                    $ruslom->bid = $this->bid;
                                    $ruslom->apply_metadata($this->bid);
                                    $first = false;
                                } else {
                                    $ruslom->addItem();
                                    $ruslom->apply_metadata($this->bid);
                                }
                                
//                                pr($item); ////////////////////////////////////////////////////////
                                /*
                                $this->bid = $this->bid;
                                $this->is_package = 1;
                                $this->is_active_version = 1;
                                if (is_array($item->metadata_xml))
                                $this->metadata = serialize($item->metadata_xml);
                                
                                $this->title = $item->metadata['general']['title'];
                                if (empty($this->title)) $this->title = 'Unknown title';
                                
                                $this->filename = '/'.(int)$this->bid.'/'.$item->material;
                                
                                $this->keywords = $this->get_keywords($item->metadata['general']['keyword']);
                                
                                $this->author = '';
                                if (isset($item->metadata['lifecycle']['contribute']))
                                $this->author = $this->get_vCardInfo($item->metadata['lifecycle']['contribute'],'author');

                                $this->publisher = '';
                                if (isset($item->metadata['lifecycle']['contribute']))
                                $this->publisher = $this->get_vCardInfo($item->metadata['lifecycle']['contribute'],'publisher');
                                
                                $this->publish_date = $this->get_publish_date($item->metadata);
                                
                                $this->description = $item->metadata['general']['description'];
                                
                                $this->location = $item->metadata['technical']['location'][0];
                                                                                                
                                if ($first) {
                                    $this->updateItem();
                                    $first = false;
                                } else {
                                    $this->addItem();
                                }
                                */
                            
                            }                            
                    
                        }
                    }
                }
            }
            
            if ($first) {
                $this->delItem($this->bid);
                $this->msg .= "Ни одного издания не найдено<br>";
            }
            }
            
        }
        @unlink($tmpdir."/".$zip['name']);
      
      }
        
        
    }
    
    function extractLOPzip($zipName, $bid) {
        
        global $tmpdir, $wwf;
        
        $cwd = getcwd(); // current work dir        
        $destPath = $wwf."/library/".(int) $bid;
        if (!file_exists($destPath)) mkdirs($destPath);
        chdir($destPath);     
        
        $path_parts = pathinfo($zipName);
        if (strtolower($path_parts["extension"])!="zip") {
                                                
            $this->msg = "Неверный формат файла учебных материалов<br>";
            return false;
            
        }
        
        $zip = zip_open($zipName);
        if ($zip) {
            while ($zip_entry = zip_read($zip)) {
            if (zip_entry_open($zip, $zip_entry, "r")) {
                
                $fSize = zip_entry_filesize($zip_entry);
                $eName = zip_entry_name($zip_entry);
                $eName = str_replace("\\", "/", $eName);
                                        
                $pathinfo = pathinfo($eName);
                if (!file_exists($pathinfo['dirname'])) @mkdirs($pathinfo['dirname']);

                if($fSize==0) {
                    $s = dirname($eName);
                    if(!file_exists($eName)) {
                        @mkdirs($eName);
                    }
                }
                else
                {
                    @$buf=zip_entry_read($zip_entry, $fSize);
                    @$fp = fopen(to_translit($eName), "wb+");
                    @fwrite($fp,$buf);
                    @fclose($fp);
                }
                                        
                zip_entry_close($zip_entry);
            }
            }
            zip_close($zip);
        }
        
        chdir($cwd);
        
        return true;
        
    }
    
    function parse_xml($pkgdir) {
        
        $manifestfile = $pkgdir.'/imsmanifest.xml';
        
        if (is_file($manifestfile)) {
            
            $xmlstring = file_get_contents($manifestfile);
            $objXML = new xml2Array();
            $manifests = $objXML->parse($xmlstring);
            unset($xmlstring);
            
            $objects = new stdClass();
            $objects = $this->get_learning_objects($manifests, $objects);
            
            $ret = $objects;
                                                
            @unlink($manifestfile);
        } else {
            $this->msg .= "Отсутствует файл imsmanifest.xml<br>";
            return false;
        }
        
        return $ret;
        
    }
    
    function get_learning_objects($blocks,$objects) {
        
        static $parents = array();
        static $resources;
        static $manifest;
        static $organization;
        
        if (count($blocks) > 0) {
            foreach ($blocks as $block) {
                switch($block['name']) {
                    case 'MANIFEST':
                        $manifest = addslashes($block['attrs']['IDENTIFIER']);
                        $organization = '';
                        if (isset($block['attrs']['XML:BASE'])) $base = $block['attrs']['XML:BASE'];                        
                        $resources = array();
                        $resources = $this->get_resources($block['children'],$base); 
                        $objects = $this->get_learning_objects($block['children'],$objects);
                        if (count($objects->elements) <= 0) {
                            foreach ($resources as $item => $resource) {
                                if (!empty($resource['HREF'])) {
                                    $object = new stdClass();
                                    $object->identifier = $item;
                                    $object->title = $item;
                                    $object->parent = '/';
                                    $object->material = addslashes($resource['HREF']);
                                    $objects->elements[$manifest][$organization][$item] = $object;
                                }
                            }
                        }
                        
                    break;
                    case 'ORGANIZATIONS':
                        $objects = $this->get_learning_objects($block['children'],$objects);
                    break;
                    case 'ORGANIZATION':
                        $identifier = addslashes($block['attrs']['IDENTIFIER']);
                        $organization = '';
                        $objects->elements[$manifest][$organization][$identifier]->identifier = $identifier;
                        $objects->elements[$manifest][$organization][$identifier]->parent = '/';
                        $objects->elements[$manifest][$organization][$identifier]->material = '';

                        $parents = array();
                        $parent = new stdClass();
                        $parent->identifier = $identifier;
                        $parent->organization = $organization;
                        array_push($parents, $parent);
                        $organization = $identifier;
                        
                        $objects = $this->get_learning_objects($block['children'],$objects);
                        array_pop($parents);
                    break;
                    case 'ITEM':
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                            
                        $identifier = addslashes($block['attrs']['IDENTIFIER']);
                        $objects->elements[$manifest][$organization][$identifier]->identifier = $identifier;
                        $objects->elements[$manifest][$organization][$identifier]->parent = $parent->identifier;
                                                
                        //$metadata = $this->get_metadata($block['children'],array(),false);
                        //$objects->elements[$manifest][$organization][$identifier]->metadata = $metadata;
                        
                        foreach($block['children'] as $v) if ($v['name'] == 'METADATA') {
                            $ruslom = new CRusLom(array());                            
                            $metadata = $ruslom->_parse_metadata($v['children'],true);
                            $objects->elements[$manifest][$organization][$identifier]->metadata = $metadata;
                            //$objects->elements[$manifest][$organization][$identifier]->metadata_xml = $v['children'];
                        }
                        
                        if (!isset($block['attrs']['IDENTIFIERREF'])) {
                            $objects->elements[$manifest][$organization][$identifier]->material = '';
                        } else {
                            $idref = addslashes($block['attrs']['IDENTIFIERREF']);
                            $base = '';
                            if (isset($resources[$idref]['XML:BASE'])) {
                                $base = $resources[$idref]['XML:BASE'];
                            }
                            $parameters = '';
                            if (isset($block['attrs']['PARAMETERS'])) $parameters = $block['attrs']['PARAMETERS'];
                            $objects->elements[$manifest][$organization][$identifier]->material = addslashes($base.$resources[$idref]['HREF'].$parameters);
                        }
                        $parent = new stdClass();
                        $parent->identifier = $identifier;
                        $parent->organization = $organization;
                        array_push($parents, $parent);

                        $objects = $this->get_learning_objects($block['children'],$objects);
                    
                        array_pop($parents);
                                                                            
                    break;
                    case 'TITLE':
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                        $objects->elements[$manifest][$parent->organization][$parent->identifier]->title = addslashes($block['tagData']);
                    break;
                    
                }        
            }
        }        
        
        return $objects;
    }
    
    function get_resources($blocks, $base='') {
            
        foreach ($blocks as $block) {
            if ($block['name'] == 'RESOURCES') {

                if (isset($block['attrs']['XML:BASE'])) $base2 = $base.$block['attrs']['XML:BASE'];
                
                foreach ($block['children'] as $resource) {
                                        
                    if ($resource['name'] == 'RESOURCE') {
                        
                        $base3 = $base2;
                        if (isset($resource['attrs']['XML:BASE'])) $base3 = $base2.$resource['attrs']['XML:BASE'];
                        
                        $resource['attrs']['XML:BASE'] = $base3;
                        
                        $resources[addslashes($resource['attrs']['IDENTIFIER'])] = $resource['attrs'];
                        
                    }
                }
            }
        }
        return $resources;        
        
    }
    
    function get_metadata($blocks,$metadata,$parse=true) {
        
        static $type;
                
        foreach($blocks as $block) {
            
            if (strstr($block['name'],':')!==false) {
                $block['name'] = substr($block['name'],strpos($block['name'],':')+1);
            }
            
            switch($block['name']) {
                case 'METADATA':
                    $metadata = $this->get_metadata($block['children'],$metadata);
                break;
                case 'LOM':
                    $metadata = $this->get_metadata($block['children'],$metadata);
                break;
                case 'GENERAL': // GENERAL
                    $type = 'general';
                    $metadata = $this->get_metadata($block['children'],$metadata); 
                break;
                case 'TITLE':
                    if ($parse) {
                    
                        $metadata[$type]['title'] = 
                        $this->get_langstring_value_by_preferences($this->parse_langstring($block['children']));
                                                                    
                    }
                break;
                case 'LANGUAGE':
                    if ($parse) {
                        
                        $metadata[$type]['language'] = $block['tagData'];
                        
                    }
                break;
                case 'DESCRIPTION':
                    if ($parse) {
                    
                        $metadata[$type]['description'] = 
                        $this->get_langstring_value_by_preferences($this->parse_langstring($block['children']));
                                                                    
                    }
                break;
                case 'KEYWORD':
                    if ($parse) {
                    
                        $metadata[$type]['keyword'][] = 
                        $this->get_langstring_value_by_preferences($this->parse_langstring($block['children']));
                                                                    
                    }
                break;
                case 'COVERAGE':
                    if ($parse) {
                    
                        $metadata[$type]['coverage'][] = 
                        $this->get_langstring_value_by_preferences($this->parse_langstring($block['children']));
                                                                    
                    }
                break;
                case 'STRUCTURE':
                    if ($parse) {
                    
                        $metadata[$type]['structure'] = $this->parse_vocabularvalue($block['children']);
                                                                    
                    }
                break;
                case 'AGGREGATIONLEVEL':
                    if ($parse) {
                    
                        $metadata[$type]['aggregationLevel'] = $this->parse_vocabularvalue($block['children']);
                                                                    
                    }
                case 'LIFECYCLE': // LIFECYCLE
                    $type = 'lifecycle';
                    $metadata = $this->get_metadata($block['children'],$metadata); 
                break;                
                case 'VERSION':
                    if ($parse) {
                        
                        $metadata[$type]['version'] = 
                        $this->get_langstring_value_by_preferences($this->parse_langstring($block['children']));
                        
                    }
                break;
                case 'STATUS':
                    if ($parse) {
                        
                        $metadata[$type]['status'] = $this->parse_vocabularvalue($block['children']);
                        
                    }                                
                break;
                case 'CONTRIBUTE':
                    if ($parse) {
                        
                        $metadata[$type]['contribute'][] = $this->parse_contribute($block['children']);
                        
                    }
                break; 
                case 'TECHNICAL': // TECHNICAL
                    $type = 'technical';
                    $metadata = $this->get_metadata($block['children'],$metadata);
                break;
                case 'FORMAT':
                    if ($parse) {
                        $metadata[$type]['format'][] = $block['tagData'];
                    }
                break;
                case 'SIZE':
                    if ($parse) {
                        $metadata[$type]['size'] = $block['tagData'];
                    }
                break;
                case 'LOCATION':
                    if ($parse) {
                        
                        $metadata[$type]['location'][] = $block['tagData'];
                        
                    }                                                
                break;
            }
                                                
        }
        
        return $metadata;
        
    }
    
    function parse_langstring($blocks) {
                
        if (is_array($blocks)) {
        
            foreach($blocks as $block) {
            
                if (strstr($block['name'],':')!==false) {
                    $block['name'] = substr($block['name'],strpos($block['name'],':')+1);
                }
                
                switch($block['name']) {
                    
                    case 'LANGSTRING':
                        $lang = '';
                        if (isset($block['attrs']['XML:LANG'])) $lang = $block['attrs']['XML:LANG'];
                        $langstrings[$lang] = addslashes($block['tagData']);
                    break;
                    
                }
        
            }
        
        }
        
        return $langstrings;
        
    }
    
    function parse_vocabularvalue($blocks) {
                
        if (is_array($blocks)) {
            
            foreach($blocks as $block) {
                
                if (strstr($block['name'],':')!==false) {
                    $block['name'] = substr($block['name'],strpos($block['name'],':')+1);
                }
                
                switch($block['name']) {
                    case 'SOURCE':
                        $source = $this->get_langstring_value_by_preferences($this->parse_langstring($block['children']));
                    break;
                    case 'VALUE':                    
                        $value = $this->get_langstring_value_by_preferences($this->parse_langstring($block['children']));
                    break;
                
                
                }                
                
            }
            
            if (isset($source)) $ret['source'] = $source;
            if (isset($value)) $ret['value'] = $value;
            
        }
        
        return $ret;
        
    }
    
    function parse_contribute($blocks) {
        
        if (is_array($blocks) && count($blocks)) {
            
            foreach($blocks as $block) {
                
                if (strstr($block['name'],':')!==false) {
                    $block['name'] = substr($block['name'],strpos($block['name'],':')+1);
                }
                
                switch ($block['name']) {                    
                    case 'ROLE':
                        $ret['role'] = $this->parse_vocabularvalue($block['children']);
                    break;                    
                    case 'CENTITY':
                        $ret['entity'][] = $this->parse_vcard($block['children']);
                    break;
                    case 'DATE':
                        $ret['date'] = $this->parse_datetime($block['children']);
                    break;                    
                }
                
            }
            
        }
        
        return $ret;
        
    }
    
    function parse_vcard($blocks) {
                
        if (is_array($blocks) && count($blocks)) {

//            $parser = new Contact_Vcard_Parse();
            
            foreach($blocks as $block) {

                if (strstr($block['name'],':')!==false) {
                    $block['name'] = substr($block['name'],strpos($block['name'],':')+1);
                }

                switch ($block['name']) {
                    
                    case 'VCARD':
                        $ret = $block['tagData'];
                    break;
                    
                }
                
            }
            
        }
        
        return $ret;
    }
    
    function parse_datetime($blocks) {
        
        if (is_array($blocks) && count($blocks)) {
            
            foreach($blocks as $block) {
                
                if (strstr($block['name'],':')!==false) {
                    $block['name'] = substr($block['name'],strpos($block['name'],':')+1);
                }

                switch ($block['name']) {
                    
                    case 'DATETIME':
                        $ret = $block['tagData'];
                    break;

                }                                
            }            
            
        }
        
        return $ret;
        
    }

    function get_langstring_value_by_preferences($metadata) {
        
        global $lom_lang_preferences;
        
        if (is_array($lom_lang_preferences)) {            
            foreach($lom_lang_preferences as $lang) {        
                if (isset($metadata[$lang])) return $metadata[$lang];                
            }            
        }
                                        
        if (is_array($metadata)) {            
            reset($metadata);
            return current($metadata);            
        }
        
        return '';        
    }
    
    function get_keywords($metadata) {
                        
        $ret = '';
        if (isset($metadata) && is_array($metadata) 
        && count($metadata)) {
            
            foreach ($metadata as $keyword) {
                
                $ret .= " ".$keyword." ";
                
            }
                        
        }
        
        return $ret;
        
    }
    
    function get_vCardInfo($metadata,$position='author') {
        if (is_array($metadata) && count($metadata)) {
            foreach ($metadata as $v) {
                if (isset($v['role']['value']) 
                && (strtolower($v['role']['value']) == $position)) {
                    
                    if (is_array($v['entity']) && count($v['entity'])) {
                        
                        foreach ($v['entity'] as $entity) {
                            
                            $lines = $entity;
                            $lines = str_replace("\\r","",$lines);
                            $lines = explode("\\n",$lines);
                            $card = new VCard();
                            $card->parse($lines);
                                                
                            switch($position) {
                                case 'publisher':
                                    $property = $card->getProperty('ORG');
                                    if ($property) {                                    
                                        $n = $property->getComponents();
                                        $tmp = array();
                                        if ($n[0]) $tmp[] = $n[0];      // Organization Name
                                        $ret[] = join(' ',$tmp);
                                    }                                    
                                break;
                                default:
                                    $property = $card->getProperty('N');
                                    if ($property) {
                                        $n = $property->getComponents();
                                        $tmp = array();
                                        if ($n[0]) $tmp[] = $n[0];      // Familyname
                                        if ($n[3]) $tmp[] = $n[3];      // Mr.
                                        if ($n[1]) $tmp[] = $n[1];      // John
                                        if ($n[2]) $tmp[] = $n[2];      // Quinlan
                                        if ($n[4]) $tmp[] = $n[4];      // Esq.
                                        $ret[] = join(' ',$tmp);
                                    }                                    
                                break;
                            }
                    
                        }
                    }                        
                }
                
            }
                        
        }
        
        if (is_array($ret) && count($ret)) $ret = join(', ',$ret);
        
        return $ret;                
        
    }
    
    function get_publish_date($metadata) {
        
        if (is_array($metadata['lifecycle']['contribute']) 
        && count($metadata['lifecycle']['contribute'])) {
            
            foreach($metadata['lifecycle']['contribute'] as $v) {
                
                if (strtolower($v['role']['value']) == 'publisher') {
                    
                    if (!empty($v['date'])) {
                        $ret = $v['date'];
                        // обработка даты:
                        // формат YYYY[-MM[-DD[Thh[:mm[:ss[.s[TZD]]]]]]]
                        // короче берётся только год - нахрен знать какого числа книжка издалась
                        $ret = substr($ret,0,4);
                        return $ret;
                    }
                    
                }
                
            }
            
        }
        return false;
    }    
        
} 
 
?>