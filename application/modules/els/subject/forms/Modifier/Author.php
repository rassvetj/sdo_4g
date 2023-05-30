<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 12.04.12
 * Time: 14:29
 * To change this template use File | Settings | File Templates.
 */

class HM_Form_Modifier_Author extends HM_Form_Modifier
{
    /**
     * @return array
     */
    protected function _getActions()
    {
        return array(array('name' => 'name', 'type' => 'remove'));
    }


}