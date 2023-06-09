<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id: Text.php 1185 2010-05-21 17:45:20Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Bvb_Grid_Filters_Render_Text extends Bvb_Grid_Filters_Render_RenderAbstract
{

    function render ()
    {
        return '<div class="wrapFiltersInput">'.$this->getView()->formText($this->getFieldName(), urldecode($this->getDefaultValue()), $this->getAttributes()).'<span class="clearFilterSpan">&nbsp;</span></div>';
    }

}