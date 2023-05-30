<?php
class Webinar_Library_Category_Service extends Library_Category_Service 
{
    protected static $_instance;

    const WEBINAR_LIBRARY_CATEGORY_ID   = 'webinars';
    const WEBINAR_LIBRARY_CATEGORY_NAME = 'Вебинары';
    
    /**
     * @return Webinar_Library_Category_Service
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    
    public function insertIfNotExists($data)
    {
        if ($data['catid']) {
            if (!$this->get($data['catid'])) {
            	$data['name'] = _(self::WEBINAR_LIBRARY_CATEGORY_NAME);
            	return $this->insert($data);
            } else {
            	return $data['catid'];
            }
        }
    }    
}