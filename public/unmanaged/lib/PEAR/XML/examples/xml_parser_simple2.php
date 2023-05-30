<?PHP
/**
 * example for XML_Parser_Simple
 *
 * $Id: xml_parser_simple2.php,v 1.1.2.2 2009/10/29 08:34:14 cvsup Exp $
 *
 * @author      Stephan Schmidt <schst@php-tools.net>
 * @package     XML_Parser
 * @subpackage  Examples
 */

/**
 * require the parser
 */
$strPath = ini_get("include_path");
ini_set("include_path", $strPath.";".$_SERVER['DOCUMENT_ROOT']."/lib/PEAR");
 
require_once 'XML/Parser/Simple.php';

class myParser2 extends XML_Parser_Simple
{
    function myParser()
    {
        $this->XML_Parser_Simple();
    }

   /**
    * handle the category element
    *
    * The element will be handled, once it's closed
    *
    * @access   private
    * @param    string      name of the element
    * @param    array       attributes of the element
    * @param    string      character data of the element
    */
    function handleElement_category($name, $attribs, $data)
    {
        printf( 'Category is %s<br />', $data );
    }

   /**
    * handle the name element
    *
    * The element will be handled, once it's closed
    *
    * @access   private
    * @param    string      name of the element
    * @param    array       attributes of the element
    * @param    string      character data of the element
    */
    function handleElement_name($name, $attribs, $data)
    {
        printf( 'Name is %s<br />', $data );
    }
}

$p = &new myParser2();
$result = $p->setInputFile('xml_parser_simple2.xml');
$p->setMode('func');
$result = $p->parse();
?>