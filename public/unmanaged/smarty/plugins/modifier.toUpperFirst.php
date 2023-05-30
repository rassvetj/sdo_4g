<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty toUpperFirst modifier plugin
 *
 * Type:     modifier
 * Name:     toUpperFirst
 * @author   developer
 * @param string
 * @return integer
 */
function smarty_modifier_toUpperFirst($string)
{
    // find periods with a word before but not after.
    return CObject::toUpperFirst($string);
}

?>
