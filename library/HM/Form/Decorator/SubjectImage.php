<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Decorator_Abstract */
require_once 'Zend/Form/Decorator/Abstract.php';

/**
 * Zend_Form_Decorator_Errors
 *
 * Any options passed will be used as HTML attributes of the ul tag for the errors.
 * 
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Errors.php 8064 2008-02-16 10:58:39Z thomas $
 */
class HM_Form_Decorator_SubjectImage extends Zend_Form_Decorator_File
{
    /**
     * Render errors
     * 
     * @param  string $content 
     * @return string
     */
    public function render($content)
    {    
        $element = $this->getElement();
        
        $subject = $element->subject;
        
        $view = new Zend_View();

        if(method_exists($subject, 'getIcon')){
            $url = $subject->getIcon() ;

            if($url === false){
                  return $content . '<br/>';
            }else{
                  return $content . '<br/><img src ="' . $url .'"/>';
            }
        }else{
            return $content . '<br/>';
        }
        
    }
}
