<?php
class HM_Question_Theme_ThemeModel extends HM_Model_Abstract
{    
    public function getQuestionsByThemes()
    {
        if (strlen($this->questions)) {
            return unserialize($this->questions);
        }
        return array();
    }

}