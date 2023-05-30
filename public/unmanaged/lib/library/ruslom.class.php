<?php

require_once("lib/classes/xml2array.class.php");

define('RUSLOM_METAFILE', 'RUSLOMdescription.xml');
define('RUSLOM_PREVIEWFILE','preview.png');
define('RUSLOM_DLP_MAX_UPLOAD_SIZE',100000000); // ~100Mb
define('RUSLOM_RUBRICATOR','Рубрикатор СОИП');

$RUSLOM_languages = array(
'ru' => 'русский',
'ru-win' => 'русский (cp1251)',
'ru-koi' => 'русский (koi8-r)',
'ru-alt' => 'русский (alt)',
'en' => 'английский',
'de' => 'немецкий',
'it' => 'итальянский',
'fr' => 'французский',
'es' => 'испанский'
);

$RUSLOM_structures = array(
'atomic' => 'атомарный',
'collection' => 'коллекция',
'contents' => 'оглавление',
);

$RUSLOM_roles = array(
'author' => 'автор',
'published' => 'издатель',
'unknown' => 'неизвестно',
'provider' => 'исполнитель контракта'
);

$RUSLOM_vacabulars = array(
'LOMv1.0',
'LOMv.1.0'
);

class CRusLom extends CBook {
    
    var $errors = array();
    var $ruslom_metadata;
    
    function CRusLom($metadata) {
        return parent::CBook($metadata);
    }
    
    /**
    * @param file $dlp
    */
    function parse_dlp($dlp) {
        if ($dlp['size'] > RUSLOM_DLP_MAX_UPLOAD_SIZE) 
            $this->errors[] = "Размер файла превышает порог";          
        if (!move_uploaded_file($dlp['tmp_name'], $GLOBALS['tmpdir']."/".$dlp['name']))
            $this->errors[] = "Нет файла учебных материалов ".$dlp['name'];
        if (!count($this->errors)) {
            $dlp_dir = $GLOBALS['wwf']."/library/".(int) $this->bid;
            if ($this->_unzip_dlp($GLOBALS['tmpdir'].'/'.$dlp['name'],$dlp_dir)) {
                $ruslom_metafile = $dlp_dir.'/'.RUSLOM_METAFILE;
                $this->parse_metafile($dlp_dir);        
            }
        }
        
        if (!count($this->errors)) return true; else return false;
    }
    
    /**
    * @param file $dlp
    */
    function parse_standalone_metafile($ruslom_metafile) {
        if ($ruslom_metafile['size'] > RUSLOM_DLP_MAX_UPLOAD_SIZE) 
            $this->errors[] = "Размер файла превышает порог";          
        if (!move_uploaded_file($ruslom_metafile['tmp_name'], $GLOBALS['tmpdir']."/".$ruslom_metafile['name']))
            $this->errors[] = "Нет файла учебных материалов ".$ruslom_metafile['name'];
        if (!count($this->errors)) $this->parse_metafile($GLOBALS['tmpdir']."/".$ruslom_metafile['name']);
        
        if (!count($this->errors)) return true; else return false;
    }        

    /**
    * @param string $dlp_dir
    */
    function parse_metafile($ruslom_metafile) {
        if (is_file($ruslom_metafile)) {
            
            $ruslom_xml = file_get_contents($ruslom_metafile);
            $objXML = new xml2Array();
            $ruslom_metadata = $objXML->parse($ruslom_xml);
            unset($ruslom_xml);
            $this->ruslom_metadata = $this->_parse_metadata($ruslom_metadata, $objects);
            //pr($this->ruslom_metadata);
            @unlink($ruslom_metafile);
        }
        
        return $this->ruslom_metadata;
    }
    
    function _parse_metadata($blocks,$ns=false) {
        static $parents = array();
        
        if (count($blocks) > 0) {
            foreach ($blocks as $block) {
                if ($ns) $this->_cut_namespace($block['name']);
                switch($block['name']) {
                    case 'RDF:RDF':
                        $objects = $this->_parse_metadata($block['children'],$ns);                        
                    break;                    
                    case 'RDF:DESCRIPTION':
                        $objects = $this->_parse_metadata($block['children'],$ns);
                    break;
                    case 'LOM':
                        $objects = $this->_parse_metadata($block['children'],$ns);
                    break;
                    // GENERAL
                    case 'GENERAL':
                        $objects['general'] = $this->_parse_metadata($block['children'],$ns);
                    break;
                    case 'IDENTIFIER':
                        $objects['identifier'] = $this->_parse_metadata($block['children'],$ns);
                    break;
                    case 'CATALOG':
                        $objects['catalog'] = $block['tagData'];
                    break;
                    case 'ENTRY':
                        $objects['entry'] = $block['tagData'];
                    break;
                    case 'TITLE':
                        $objects['title'] = $this->_parse_string($block['children'],$ns);
                    break;                    
                    case 'LANGUAGE':
                        $objects['language'] = $block['tagData'];
                    break;
                    case 'DESCRIPTION':
                        $objects['description'] = $this->_parse_string($block['children'],$ns);
                    break;
                    case 'KEYWORD':
                        $objects['keywords'][] = $this->_parse_string($block['children'],$ns);
                    break;                    
                    case 'COVERAGE':
                        $objects['coverages'][] = $this->_parse_string($block['children'],$ns);
                    break;
                    case 'STRUCTURE':
                        $objects['structure'] = $this->_parse_vocabular($block['children'],$ns);                        
                    break;                    
                    case 'AGGREGATIONLEVEL':
                        $objects['aggregationlevel'] = $this->_parse_vocabular($block['children'],$ns);
                    break;                    
                    // LIFECYCLE
                    case 'LIFECYCLE':
                        $objects['lifecycle'] = $this->_parse_metadata($block['children'],$ns);
                    break;
                    case 'VERSION':
                        $objects['version'] = $this->_parse_string($block['children'],$ns);
                    break;
                    case 'CONTRIBUTE':
                        $objects['contribute'][] = $this->_parse_contribute($block['children'],$ns);
                    break;                    
                    // TECHNICAL
                    case 'TECHNICAL':
                        $objects['technical'] = $this->_parse_metadata($block['children'],$ns);
                    break;
                    case 'FORMAT':
                        $objects['format'][] = $block['tagData'];
                    break;
                    case 'SIZE':
                        $objects['size'] = $block['tagData'];
                    break;
                    case 'REQUIREMENT':
                        $objects['requirement'][] = $this->_parse_requirement($block['children'],$ns);
                    break;                    
                    case 'OTHERPLATFORMRECUREMENTS':
                        $objects['otherplatformrecurements'] = $this->_parse_string($block['children'],$ns);
                    break;                    
                    case 'LOCATION':
                        $objects['location'][] = $block['tagData'];
                    break;                    
                    // RIGHTS
                    case 'RIGHTS':
                        $objects['rights'] = $this->_parse_metadata($block['children'],$ns);
                    break;
                    case 'COST':
                        $objects['cost'] = $this->_parse_vocabular($block['children'],$ns);
                    break;
                    case 'COPYRIGHTANDOTHERRESTRICTIONS':
                        $objects['copyrightandotherrestrictions'] = $this->_parse_vocabular($block['children'],$ns);
                    break;
                    // CLASSIFICATION
                    case 'CLASSIFICATION':
                        $objects['classification'][] = $this->_parse_metadata($block['children'],$ns);
                    break;
                    case 'TAXONPATH':
                        $objects['taxonpath'] = $this->_parse_taxonpath($block['children'],$ns);
                    break;
                }        
            }
        }        
        
        return $objects;            
    }
    
    function _parse_taxonpath($blocks,$ns=false) {
        if (is_array($blocks) && count($blocks)) {            
            foreach($blocks as $block) {                                
                if ($ns) $this->_cut_namespace($block['name']);
                switch ($block['name']) {
                    case 'SOURCE':
                        $ret['source'] = $this->_parse_string($block['children'],$ns);
                    break;
                    case 'ID':
                        $ret['id'] = $block['tagData'];
                    break;
                    case 'ENTRY':
                        $ret['entry'] = $this->_parse_string($block['children'],$ns);
                    break;
                    case 'TAXON':
                        $ret['taxon'][] = $this->_parse_taxonpath($block['children'],$ns);
                    break;
                }                
            }            
        }        
        return $ret;
    }
    
    function _parse_requirement($blocks,$ns=false) {
        if (is_array($blocks) && count($blocks)) {            
            foreach($blocks as $block) {                                
                if ($ns) $this->_cut_namespace($block['name']);
                switch ($block['name']) {
                    case 'ORCOMPOSITE':
                        $ret['orcomposite'] = $this->_parse_requirement($block['children'],$ns);
                    break;
                    case 'TYPE':
                        $ret['type'] = $this->_parse_vocabular($block['children'],$ns);
                    break;
                    case 'NAME':
                        $ret['name'] = $this->_parse_vocabular($block['children'],$ns);
                    break;
                    case 'MINIMUMVERSION':
                        $ret['minimumversion'] = $this->_parse_vocabular($block['children'],$ns);
                    break;
                    case 'MAXIMUMVERSION':
                        $ret['maximumversion'] = $this->_parse_vocabular($block['children'],$ns);
                    break;                
                }                
            }            
        }        
        return $ret;                
    }
    
    function _parse_contribute($blocks,$ns=false) {
        if (is_array($blocks) && count($blocks)) {            
            foreach($blocks as $block) {                                
                if ($ns) $this->_cut_namespace($block['name']);
                switch ($block['name']) {
                    case 'ROLE':
                        $ret['role'] = $this->_parse_vocabular($block['children'],$ns);
                    break;
                    case 'ENTITY':
                        $ret['entity'][] = $this->_parse_contribute($block['children'],$ns);
                    break;
                    case 'DATE':
                        $ret['date'] = $this->_parse_contribute($block['children'],$ns);
                    break;
                    case 'VCARD':
                        $ret = $block['tagData'];
                    break;
                    case 'DATETIME':
                        $ret = $block['tagData'];
                    break;
                }                
            }            
        }
        if (is_array($ret['entity']) && count($ret['entity'])) $ret['entity'] = join(', ',$ret['entity']);
        return $ret;        
    }
    
    function _parse_vocabular($blocks,$ns=false) {                
        if (is_array($blocks)) {            
            foreach($blocks as $block) {                                
                if ($ns) $this->_cut_namespace($block['name']);
                switch($block['name']) {
                    case 'SOURCE':
                        $ret['source'] = $block['tagData'];
                    break;
                    case 'VALUE':  
                        $ret['value'] = $block['tagData'];
                    break;                                
                }                                
            }            
        }
        return $ret['value'];        
    }        
    
    function _parse_string($blocks,$ns=false) {
        if (is_array($blocks)) {                
            foreach($blocks as $block) {                            
                if ($ns) $this->_cut_namespace($block['name']);
                switch($block['name']) {                    
                    case 'STRING':
                        $lang = 'unknown';
                        if (isset($block['attrs']['LANGUAGE'])) $lang = $block['attrs']['LANGUAGE'];
                        $string[$lang] = addslashes($block['tagData']);
                    break;                    
                }        
            }        
        }
        return $this->_get_string($string);
    }
    
    function _get_string($string) {
        if (is_array($string) && count($string)) {
            foreach($GLOBALS['RUSLOM_languages'] as $k=>$v) 
                if (isset($string[$k])) return $string[$k];
        }        
    }
    
    function _cut_namespace(&$elementName) {
        if (strstr($elementName,':')!==false) 
            $elementName = substr($elementName,strpos($elementName,':')+1);
    }
    
    /**
    * @param string $dlp
    * @param string $dest
    */
    function _unzip_dlp($dlp, $dest) {
        $cwd = getcwd(); // current work dir
        if (!file_exists($dest)) mkdirs($dest);
        chdir($dest);     
                
        $path_parts = pathinfo($dlp);
        if (strtolower($path_parts["extension"])!="zip") {                                                
            $this->errors[] = "Неверный формат файла учебных материалов";
            return false;
        }
        
        $zip = zip_open($dlp);
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
                    if(!file_exists($eName)) @mkdirs($eName);
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
    
    function apply_metadata($resource_id) {
        $ret = false;
        if (($resource_id>0) && is_array($this->ruslom_metadata) && count($this->metadata)) {
            $this->ruslom_metadata['lifecycle']['contribute'] = 
                $this->_reform_contribute($this->ruslom_metadata['lifecycle']['contribute']);
            $this->ruslom_metadata['classification'] = 
                $this->_reform_classification($this->ruslom_metadata['classification']);
            $this->_set_metadata_fields();
            $this->updateItem();    
            $ret = true;
        }
        return $ret;
    }
    
    function _reform_taxonpath($taxonpath) {
        if (is_array($taxonpath) && count($taxonpath)) {
            foreach($taxonpath as $k=>$v) {
                if (isset($v['id'])) $ret = trim($v['id']);
                //if (isset($v['entry'])) $ret['entry'] = trim($v['entry']);
                if (is_array($v['taxon'])) $ret = $this->_reform_taxonpath($v['taxon']);
            }
        }
        return $ret;
    }
    
    function _reform_classification($classification) {
        if (is_array($classification) && count($classification)) {
            foreach($classification as $k=>$v) {
                if (is_array($v['taxonpath']) && count($v['taxonpath']) && ($v['taxonpath']['source']==RUSLOM_RUBRICATOR)) 
                    $ret[] = $this->_reform_taxonpath($v);
            }
        }
        return $ret;
    }
    
    function _reform_contribute($contribute) {
        if (is_array($contribute) && count($contribute)) {
            foreach($contribute as $v) {
                $ret[trim($v['role'])]['names'] .= $this->_reform_vcard($v['entity'],$v['role']).' ';
                $ret[trim($v['role'])]['date'] = $v['date'];
            }            
        }
        //pr($ret);
        //die();
        return $ret;
    }
    
    function _reform_vcard($vcard,$role='author') {        
        $card = new CVCard($vcard);
        switch($role) {
            case 'publisher':
                $ret = $card->get_param('ORG');
            break;
            default:
                $ret = $card->get_param('FN');
                if (empty($ret)) $ret = $card->get_param('N');
            break;
        }        
        return $ret;        
    }
    
    function _set_metadata_fields() {
        $this->is_active_version = 1;
        $this->cats = $this->_get_classification($this->ruslom_metadata['classification']);
        $this->title = $this->ruslom_metadata['general']['title'];
        $this->author = $this->ruslom_metadata['lifecycle']['contribute']['author']['names'];
        $this->description = $this->ruslom_metadata['general']['description'];
        $this->keywords = is_array($this->ruslom_metadata['general']['keywords']) ? join(' ',$this->ruslom_metadata['general']['keywords']) : '';
        $this->publisher = $this->ruslom_metadata['lifecycle']['contribute']['publisher']['names'];
        $this->publish_date = $this->ruslom_metadata['lifecycle']['contribute']['publisher']['date'];
        $this->location = $this->ruslom_metadata['technical']['location'][0];
    }
    
    function _get_classification($classification) {
        if (is_array($classification) && count($classification)) {
            $sql = "SELECT catid FROM library_categories WHERE catid IN ('".join("','",$classification)."')";
            $res = sql($sql);
            while($row = sqlget($res)) $ret[] = $row['catid'];
        }
        return $ret;
    }
    
}

$vcard_params = array(
'CATEGORIES','SORT-STRING','NICKNAME','PROFILE','BEGIN','SOURCE','PRODID','MAILER','LABEL','TITLE','PHOTO','AGENT','EMAIL',
'NAME','BDAY','ROLE','LOGO','NOTE','ADR','TEL','REV','GEO','ORG','END','FN','TZ','N'
);
class CVCard {
    
    var $vcard;
    
    function CVCard($vcard) {
        $vcard = preg_replace('/BEGIN:VCARD/i','',$vcard);
//        $vcard = preg_replace('/END:VCARD/i','',$vcard);
        $this->vcard = $vcard;
    }
    
    function get_param($name) {
        $pattern = "/$name:(.*?)(?:".join('|',$GLOBALS['vcard_params'])."):/is";
        //pr($pattern);
        if (preg_match($pattern,$this->vcard,$matches)) {            
            $value = $this->_parse_param($name,$matches[1]);
            return $value;
        } 
    }
    
    function _parse_param($name,$value) {
        $value = trim($value);
        //$value = substr($value,0,strrpos($value,' '));
        $value = trim($value);
        switch($name) {
            case 'ORG':
                $tmp = explode(';',$value);
                $value = join(', ',$tmp);
            break;
            case 'N': // Family Name, Given Name, Additional Names, Honorific Prefixes, and Honorific Suffixes
                $tmp = explode(';',$value);                
                $value = join(', ',$tmp);
            break;
        }        
        return $value;        
    }
    
}

//$ruslom = new CRusLom(array());
//$ruslom->parse_metafile($GLOBALS['wwf'].'/RUSLOMdescription.xml');

?>