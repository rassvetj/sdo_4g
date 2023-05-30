<?php
require_once($GLOBALS['wwf'].'/lib/sajax/Sajax.php');

/**
 * Enter description here...
 *
 * @author Yuri Novitsky (c) 2006
 */
class CSajaxWrapper {
    /**
     * Sajax Wrapper
     *
     * @param array $export
     * @return string sajax javascript
     */
    function init($export) {
        sajax_init();            
        if (is_array($export) && count($export)) {
            foreach ($export as $v) {
                sajax_export($v); // funcitions must exists in func.lib.php4 (example)
            }
        }
        sajax_handle_client_request();
        return sajax_get_javascript();
    }
}
?>