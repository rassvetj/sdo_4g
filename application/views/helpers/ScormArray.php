<?php
class HM_View_Helper_ScormArray extends Zend_View_Helper_Abstract
{
    public function scormArray($version, HM_Scorm_Track_Data_DataModel $data, $elementName, $children)
    {
        $content = '';
        // reconstitute comments_from_learner and comments_from_lms
        $current = '';
        $current_subelement = '';
        $current_sub = '';
        $count = 0;
        $count_sub = 0;

        // filter out the ones we want
        $element_list = array();
        foreach($data->getValues() as $element => $value){
            if (substr($element,0,strlen($elementName)) == $elementName) {
                $element_list[$element] = $value;
            }
        }

        // sort elements in .n array order
        uksort($element_list, array($this, "elementCompare"));

        // generate JavaScript
        foreach($element_list as $element => $value){
            if ($version == 'scorm_13') {
                $element = preg_replace('/\.(\d+)\./', ".N\$1.", $element);
                preg_match('/\.(N\d+)\./', $element, $matches);
            } else {
                $element = preg_replace('/\.(\d+)\./', "_\$1.", $element);
                preg_match('/\_(\d+)\./', $element, $matches);
            }
            if (count($matches) > 0 && $current != $matches[1]) {
                if ($count_sub > 0) {
                    $content .=  '    '.$elementName.'.'.$current.'.'.$current_subelement.'._count = '.$count_sub.";\n";
                }
                $current = $matches[1];
                $count++;
                $current_subelement = '';
                $current_sub = '';
                $count_sub = 0;
                $end = strpos($element,$matches[1])+strlen($matches[1]);
                $subelement = substr($element,0,$end);
                $content .=  '    '.$subelement." = {};\n";
                // now add the children
                foreach ($children as $child) {
                    $content .=  '    '.$subelement.".".$child." = {};\n";
                    $content .=  '    '.$subelement.".".$child."._children = ".$child."_children;\n";
                }
            }

            // now - flesh out the second level elements if there are any
            if ($version == 'scorm_13') {
                $element = preg_replace('/(.*?\.N\d+\..*?)\.(\d+)\./', "\$1.N\$2.", $element);
                preg_match('/.*?\.N\d+\.(.*?)\.(N\d+)\./', $element, $matches);
            } else {
                $element = preg_replace('/(.*?\_\d+\..*?)\.(\d+)\./', "\$1_\$2.", $element);
                preg_match('/.*?\_\d+\.(.*?)\_(\d+)\./', $element, $matches);
            }

            // check the sub element type
            if (count($matches) > 0 && $current_subelement != $matches[1]) {
                if ($count_sub > 0) {
                    $content .=  '    '.$elementName.'.'.$current.'.'.$current_subelement.'._count = '.$count_sub.";\n";
                }
                $current_subelement = $matches[1];
                $current_sub = '';
                $count_sub = 0;
                $end = strpos($element,$matches[1])+strlen($matches[1]);
                $subelement = substr($element,0,$end);
                $content .=  '    '.$subelement." = {};\n";
            }

            // now check the subelement subscript
            if (count($matches) > 0 && $current_sub != $matches[2]) {
                $current_sub = $matches[2];
                $count_sub++;
                $end = strrpos($element,$matches[2])+strlen($matches[2]);
                $subelement = substr($element,0,$end);
                $content .=  '    '.$subelement." = {};\n";
            }

            $content .=  '    '.$element.' = \''.$value."';\n";
        }
        if ($count_sub > 0) {
            $content .=  '    '.$elementName.'.'.$current.'.'.$current_subelement.'._count = '.$count_sub.";\n";
        }
        if ($count > 0) {
            $content .=  '    '.$elementName.'._count = '.$count.";\n";
        }

        return $content;
        
    }

    function elementCompare($a, $b) {
        preg_match('/.*?(\d+)\./', $a, $matches);
        $left = intval($matches[1]);
        preg_match('/.?(\d+)\./', $b, $matches);
        $right = intval($matches[1]);
        if ($left < $right) {
            return -1; // smaller
        } elseif ($left > $right) {
            return 1;  // bigger
        } else {
            // look for a second level qualifier eg cmi.interactions_0.correct_responses_0.pattern
            if (preg_match('/.*?(\d+)\.(.*?)\.(\d+)\./', $a, $matches)) {
                $leftterm = intval($matches[2]);
                $left = intval($matches[3]);
                if (preg_match('/.*?(\d+)\.(.*?)\.(\d+)\./', $b, $matches)) {
                    $rightterm = intval($matches[2]);
                    $right = intval($matches[3]);
                    if ($leftterm < $rightterm) {
                        return -1; // smaller
                    } elseif ($leftterm > $rightterm) {
                        return 1;  // bigger
                    } else {
                        if ($left < $right) {
                            return -1; // smaller
                        } elseif ($left > $right) {
                            return 1;  // bigger
                        }
                    }
                }
            }
            // fall back for no second level matches or second level matches are equal
            return 0;  // equal to
        }
    }

}

