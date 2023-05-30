<?php
/**
 * Custom validator.
 *
 */

class HM_Validate_AlphaForNames extends Zend_Validate_Alpha {

    public function isValid($value) {
        $this->setAllowWhiteSpace(true);
        $value = str_replace(array("'", '(', ')', '-'), '', (string) $value);
        return parent::isValid($value);
    }
}
