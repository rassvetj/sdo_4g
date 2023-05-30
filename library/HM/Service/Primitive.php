<?php

/**
 * Primitive service class without model, mapper and table objects
 *
 * @author tutrinov
 */
class HM_Service_Primitive extends HM_Service_Abstract {
    
    public function __construct($mapperClass = null, $modelClass = null, $adapterClass = null) {
        return $this;
    }
    
}