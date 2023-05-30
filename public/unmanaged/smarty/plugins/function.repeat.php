<?php
function smarty_function_repeat($params, &$smarty)
{

    if (!isset($params['string'])) {
        $smarty->trigger_error("repeat: missing 'string' parameter");
        return;
    }

    if(!isset($params['count'])) {
        $params['count'] = 1;
    }

    return str_repeat($params['string'],$params['count']);

}
?>