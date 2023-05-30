<?php
class HM_View_Helper_UserCourses extends Zend_View_Helper_Abstract
{

    private static $_userCourses = array();

    public function userCourses($userId, $role, $separator = ', ', $table ='Course')
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        if (!$serviceContainer->hasService($role)) {
            throw new HM_Exception(_("Не найден сервис: $role"));
        }
        
        if (!count(self::$_userCourses)) {
            $collection = $serviceContainer->getService($role)->fetchAllDependence(
                $table
            );
          
            if (count($collection)) {
                foreach($collection as $item) {
                    if ($item->getCourse()) {
                        if (isset($item->MID)) {
                            $mid = $item->MID;
                        }
                        if (isset($item->mid)) {
                            $mid = $item->mid;
                        }
                        
                        if ($table == 'Course')
                        {
                            self::$_userCourses{$mid}[] = $item->getCourse()->Title;
                        } elseif ($table == 'Subject')
                        {
                            self::$_userCourses{$mid}[] = $item->getCourse()->name;
                        }
                    }
                }
            }
        }

        $return = array();

        if (isset(self::$_userCourses[$userId])) {
            $return = self::$_userCourses[$userId];
        }

        $return = join($separator, $return);
        return $return;
    }
}