<?php
class HM_Config_Ini extends Zend_Config_Ini
{
    
    public function __construct($filename, $section = null, $options = false, $devfilename)
    {
        $parseDevConfig = (is_array($options) && false !== $options['parseDevConfig']) || !is_array($options);
        $allowModifications = false;
        if (is_bool($options)) {
            $allowModifications = $options;
            $options = array('allowModifications' => true);
        } elseif (is_array($options)) {
            if (isset($options['allowModifications'])) {
                $allowModifications = (bool) $options['allowModifications'];
            }
            $options['allowModifications'] = true;
        } else {
            $options = array('allowModifications' => true);
        }
        
        parent::__construct($filename, $section, $options);
        
        if ($parseDevConfig && !empty($devfilename) && is_file($devfilename)) {
            if ($this->areAllSectionsLoaded()) {
                /** @see Zend_Config_Exception */
                require_once 'Zend/Config/Exception.php';
                throw new Zend_Config_Exception('Development config merging is not implemented when section == null');
            } else {
                $devConfig = new Zend_Config_Ini($devfilename, null, $options);
                // Get config inheritance chain
                $currentSection = $this->getSectionName();
                $extends = $this->getExtends();
                $configs = array();
                do {
                    if ($devConfig->get($currentSection) instanceof Zend_Config) {
                        $configs[] = $devConfig->get($currentSection);
                    }
                } while ($currentSection = $extends[$currentSection]);
                $configs = array_reverse($configs);
                
                foreach ($configs as $config) {
                    $this->merge($config); 
                }
            }
        }
        
        if (!$allowModifications) {
            $this->setReadOnly();
        }
    }
    
}