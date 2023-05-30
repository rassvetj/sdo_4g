<?php
/* Usage
 Grab some XML data, either from a file, URL, etc. however you want. Assume storage in $strYourXML;

 $objXML = new xml2Array();
 $arrOutput = $objXML->parse($strYourXML);
 print_r($arrOutput); //print it out, or do whatever!
  
*/

$GLOBALS['bad_chr'] = array("\x00" => "chr(0)", "\x01" => "chr(1)", "\x02" => "chr(2)", "\x03" => "chr(3)", "\x04" => "chr(4)", "\x05" => "chr(5)", "\x06" => "chr(6)", "\x07" => "chr(7)", "\x08" => "chr(8)", "\x09" => "chr(9)", "\x0a" => "chr(10)", "\x0b" => "chr(11)", "\x0c" => "chr(12)", "\x0d" => "chr(13)", "\x0e" => "chr(14)", "\x0f" => "chr(15)", "\x10" => "chr(16)", "\x11" => "chr(17)", "\x12" => "chr(18)", "\x13" => "chr(19)", "\x14" => "chr(20)", "\x15" => "chr(21)", "\x16" => "chr(22)", "\x17" => "chr(23)", "\x18" => "chr(24)", "\x19" => "chr(25)", "\x1a" => "chr(26)", "\x1b" => "chr(27)", "\x1c" => "chr(28)", "\x1d" => "chr(29)", "\x1e" => "chr(30)", "\x1f" => "chr(31)");

class xml2Array {
   
   var $arrOutput = array();
   var $resParser;
   var $strXmlData;
   var $encoding = 'UTF-8';
   
   function parse($strInputXML,$encoding='UTF-8') {
   
           $this->encoding = $encoding; 
           $this->resParser = xml_parser_create('');
           xml_set_object($this->resParser,$this);
           xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");
           
           xml_set_character_data_handler($this->resParser, "tagData");
            
           $this->strXmlData = xml_parse($this->resParser, $strInputXML);
           if(!$this->strXmlData) {
               return '';
           }
                           
           xml_parser_free($this->resParser);
           
           return $this->arrOutput;
   }
   
   function tagOpen($parser, $name, $attrs) {
       $tag=array("name"=>$name,"attrs"=>$attrs); 
       array_push($this->arrOutput,$tag);
   }
   
   function tagData($parser, $tagData) {
       if(trim($tagData)) {
           if(isset($this->arrOutput[count($this->arrOutput)-1]['tagData'])) {
               $this->arrOutput[count($this->arrOutput)-1]['tagData'] .= iconv($this->encoding,$GLOBALS['controller']->lang_controller->lang_current->encoding,$tagData);
           } else {
               $this->arrOutput[count($this->arrOutput)-1]['tagData'] = iconv($this->encoding,$GLOBALS['controller']->lang_controller->lang_current->encoding,$tagData);
           }
       }
   }
   
   function tagClosed($parser, $name) {
       $this->arrOutput[count($this->arrOutput)-2]['children'][] = $this->arrOutput[count($this->arrOutput)-1];
       array_pop($this->arrOutput);
   }
}
?>