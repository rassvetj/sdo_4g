<?php
/*
 * xml parser for PHP4
 *
 * (C) Copyright 2002 andy 
 *
 * $Id: xml.class.php4,v 1.1 2005/04/01 16:03:08 dimon Exp $
 *
 */

class xml {
// public
   var $cName  = "xml";
   var $cVer   = "0.61"; 

// privat
   var $xml_parser   = 0;

// file pointer
   var $fp     = false;

// data Structure Values Array and Structure Tags Array
   var $values = array();
   var $tags   = array();

// error String
   var $errorString= "";

// error Code
   var $errorLine = "";
   var $errorColumn= "";

// error 
   var $error     = "";
   var $errorShow = false;

// constructor
   function xml() {
   $this->xml_parser = xml_parser_create();
   }// end xml 

// privet functions
   function parseIntoStruct($data) {
   $data = str_replace("./Files/", "/courses/course{$_GET['cid']}/Files/", $data);
   if (!xml_parse_into_struct($this->xml_parser,$data,$this->values,$this->tags)) {
      $this->parseError();
      }
   }// end parseIntoStruct

   function parseIntoString($data) {
   if (!xml_parse($this->xml_parser, $data, feof($this->fp))) {
      $this->parseError();
      }
   }// end parseIntoString

   function parseError() {
      $this->error=xml_get_error_code($this->xml_parser);
      $this->errorString=xml_error_string($this->error);
      $this->errorLine=xml_get_current_line_number($this->xml_parser);
      $this->errorColumn=xml_get_current_column_number($this->xml_parser);
   }// end parseError

   function noMagic($data) {
   if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) $data=stripslashes($data);
   return $data;
   }// end noMagic

   function startElement($parser, $tag, $attributes) { 
   }// end startElement

   function cData($parser, $cdata) {
   }// end cData

   function endElement($parser, $tag) {
   }// end endElement

   function defaultHandler($parser, $data) {
   }// end defaultHandler

// public functions
   function setErrorShow() {
   $this->errorShow = true;
   }// end setErrorShow

   function setInputFile($file) {
   if (!($this->fp = @fopen($file, "r"))) {
      $this->fp=false;
      }
    return true;
   }// end setInputFile

   function setObject() {
   xml_set_object($this->xml_parser, &$this);
   }// end setObject

   function setElementHnadler($startElement = "startElement", $endElement = "endElement") {
   xml_set_element_handler($this->xml_parser, $startElement, $endElement);
   }// end setElementHnadler

   function seh($startElement = "startElement", $endElement = "endElement") {
   $this->setElementHnadler($startElement, $endElement);
   }// end seh

   function setCharacterDataHandler($cData = "cData") {
   xml_set_character_data_handler($this->xml_parser, $cData);
   }// end  setCharacterDataHandler

   function scdh($cData = "cData") {
   $this->setCharacterDataHandler($cData);
   }// end scdh

   function setDefaultHandler($defaultHandler = "defaultHandler") {
   xml_set_default_handler($this->xml_parser, $defaultHandler);
   }// end setDefaultHandler

   function sdh($defaultHandler = "defaultHandler") {
   $this->setDefaultHandler($defaultHandler);
   }// end sdh

   function setSkipWhite() {
   xml_parser_set_option($this->xml_parser,XML_OPTION_SKIP_WHITE,1);
   }// end setSkipWhite

   function ssw() {
   $this->setSkipWhite();
   }// end ssw

   function setCaseFolding() {
   xml_parser_set_option($this->xml_parser,XML_OPTION_CASE_FOLDING,1);
   }// end setCaseFolding

   function scf() {
   $this->setCaseFolding();
   }// end scf

   function parse() { 
   if ($this->fp) {
      while (($data = fread($this->fp, 4096)) && !$this->error) {
              $data = $this->noMagic($data);
                $this->parseIntoString($data);
            }
      if ($this->error && $this->errorShow) $this->showError();
      }
   }// end parse

   function parseStruct($val = array(), $tag = array()) {
   $data = "";
   if ($this->fp) {
         while (!feof ($this->fp)) 
            $data .= fread($this->fp, 4096);
         $data = $this->noMagic($data);
         $this->parseIntoStruct($data);

         $val = $this->getValues();
         $tag = $this->getTags();

         if ($this->error && $this->errorShow) $this->showError();
         }
   }// end parseStruct

   function getValues() {
    return $this->values;
   }// end getsTags

   function getTags() {
    return $this->tags;
   }// end getsTags 

   function getcVer() {
      return $this->cVer;
   }// end getcVer

   function getcName() {
      return $this->cName;
   }// end getcName

   function showError() {
    printf("XML error %s : %s at line %d and column %d <br>", $this->error, $this->errorString, $this->errorLine,$this->errorColumn);
   }// end showError

   function getError() {
      return $this->$errorShow;
   }

// destructor
   function _xml() {
   xml_parser_free($this->xml_parser);
   fclose($this->fp);
   }// end _xml
}
?>