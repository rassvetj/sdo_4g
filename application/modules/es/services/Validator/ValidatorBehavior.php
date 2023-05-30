<?php
/**
 *
 * @author slava
 */
interface Es_Service_Validator_ValidatorBehavior {
    
    const VALIDATOR_NOTZERO = 'notZero';
    const VALIDATOR_NOTNULL = 'notNull';
    const VALIDATOR_ARRAY = 'array';
    const VALIDATOR_INTEGER = 'integer';
    const VALIDATOR_DOUBLE = 'double';
    
    public function getValidatorCallback($param = null, $exceptionMessage = null);
    
}

?>
