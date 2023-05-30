<?php
class User_Service extends Object_Service {
    static protected $_instance;
    
    public function __construct() {
        $this->_table = new User_Table();
    }
    
    /**
     * @return Webinar_Service
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function get($userId) {
        if ($user = $this->_table->find($userId)) {
            if ($user->count()) {
                return $user->current();
            }
        }
    }
}