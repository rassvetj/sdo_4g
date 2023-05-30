<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 12.04.12
 * Time: 14:29
 * To change this template use File | Settings | File Templates.
 */

class HM_Form_Modifier_Readonly extends HM_Form_Modifier_BaseTypePractice
{
    /**
     * @return array
     */
    protected function _getActions()
    {
        if (count($elements = $this->getForm()->getElements())) {
            foreach ($elements as $element) {
                $element->setAttrib('disabled', true);
            }
        }
    }
}