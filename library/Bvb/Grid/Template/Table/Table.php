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
 * @version    $Id: Table.php 1178 2010-05-21 14:04:32Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Bvb_Grid_Template_Table_Table
{

    public $hasExtraRow = 0;

    public $hasFilters = 1;

    public $i;

    public $insideLoop;

    public $options;

    public $export;

    static public $counter = 1;


    public function globalStart ($cols = 0)
    {
        $html = "<table cellspacing=\"0\">";
        //if ($cols > 0) {
        //    $html .= str_repeat('<col>', $cols);
        //}
        //$html .= '<col width="1%"/><col width="*"/><col width="1%"/><col width="1%"/><col width="1%"/><col width="1%"/>';
        return $html;
    }


    public function globalEnd ()
    {
        return "</table>";
    }


    public function extra ()
    {
        return "<tr><td class=\"querySupport\" colspan=\"{$this->options['colspan']}\"><div style=\"text-align:right;\">{{value}}</div></td></tr>";
    }


    public function titlesStart ()
    {
        return "<thead><tr>";
    }


    public function titlesEnd ()
    {
        return "</tr></thead>";
    }


    public function titlesLoop ()
    {
        return "<th class=\"{{class}}{{title}}\">{{value}}</th>";
    }


    public function filtersStart ()
    {
        return "<tbody><tr class=\"filters_tr\">";
    }


    public function filtersEnd ()
    {
        return "</tr></tbody>";
    }


    public function noResults ()
    {
//        return "<div  style=\"padding:1em;text-align:center;\">{{value}}</div>";
        return "<td class=\"no-result\" colspan=\"{$this->options['colspan']}\">{{value}}</td>";
    }

    public function noResults2 ()
    {
//        return "<div  style=\"padding:1em;text-align:center;\">{{value}}</div>";
        return "<td class=\"no-result\" colspan=\"{$this->options['colspan']}\">{{value}}</td>";
    }
    

    public function filtersLoop ()
    {
        return "<td class=\"filters_td\" >{{value}}</td>";
    }


    public function hRow ($values)
    {
        return "<td  colspan=\"{$this->options['colspan']}\" class=\"hbar\"><div>{{value}}</div></td>";
    }


    public function loopStart ($class)
    {
        $this->i ++;
        $this->insideLoop = 1;

        if ( strlen($class) > 0 ) {
            $class = " class='$class' ";
        }

        $autoId = sprintf("autogenerated-grid-row-id-%d", self::$counter++);
        return "<tr id=\"$autoId\" $class>";
    }


    public function loopEnd ()
    {
        return "</tr>";
    }


    public function formMessage ($ok = false)
    {

        if ( $ok ) {
            $class = "";
        } else {
            $class = "_red";
        }
        return "<div class=\"alerta$class\">{{value}}</div>";
    }


    public function loopLoop ()
    {
        return "<td class=\"{{class}} \" style=\"{{style}}\" >{{value}}</td>";
    }


    public function sqlExpStart ()
    {
        return "<tr>";
    }


    public function sqlExpEnd ()
    {
        return "</tr>";
    }


    public function sqlExpLoop ()
    {
        return "<td class=\"sum {{class}}\">{{value}}</td>";
    }


    public function pagination ()
    {
        //<div class=\"paginatinExport\">" . $this->export . "</div>
        return "<tfoot><tr><td class=\"bottom-grid {{has-pagination}} {{has-massActions}} ".(strlen($this->export) > 0 ? 'has-export' : '')."\" colspan=\"{$this->options['colspan']}\">
        <div class='pagination'><div class='page-numbers'>{{pagination}}</div><div class='perpage'>{{perPage}}</div><div class='page-select'>{{pageSelect}}</div></div><div class='massActions mass-actions'>{{massActions}}</div><div class='export'>" .  $this->export . "</div>
        </td></tr></tfoot>";
    }


    public function images ($url)
    {
        return array('asc' => "<span><img src=\"" . $url . "arrow_up.gif\" border=\"0\"></span>", 'desc' => "<span><img src=\"" . $url . "arrow_down.gif\" border=\"0\"></span>", 'delete' => "<img src=\"" . $url . "delete.png\" border=\"0\">", 'detail' => "<img src=\"" . $url . "detail.png\" border=\"0\">", 'edit' => "<img src=\"" . $url . "edit.png\"  border=\"0\">");
    }


    public function detail ()
    {
        return "<tr><td class='detailLeft'>{{field}}</td><td class='detailRight'>{{value}}</td></tr>";
    }


    public function detailEnd ()
    {
        return "<tr><td colspan='2'><a href='{{url}}'>{{return}}</a></td></tr>";
    }


    public function detailDelete ()
    {
        return "<tr><td colspan='2'>{{button}}</td></tr>";
    }

	/**
	 * Задание кнопок экспортирования данных в таблице
	 * 
	 * @param array		Массив кнопок с параметрами
	 * @param unused	Более не используется, оставлено для совместимости
	 * @param string	Базовый url экспорта
	 * @param string	Grid Id
	 * @return string	HTML-код для вставки в шаблон
	 */
	public function export (array $exportDeploy, $images, $url, $gridId)
	{
		$captions['print']	= _('Распечатать');
		$captions['word']	= 'Word';
		$captions['excel']	= 'Excel';
		
		$elementHTML = '<input %s />';
		$elementsDelimiter = "\n";
		$script = 'window.open(\'%s\'); return false;';
		
		$elementDefaults = array(	// Параметры по умолчанию для каждого нового элемента:
			'newWindow'	=> true,	// Открывать в новом окне
			'cssClass'	=> false,	// Присвоить имя класса
			'id'		=> false,	// Присвоить id
			'caption'	=> 'Export'	// Название элемента
		);
		$defaultAttribs = array(	// Атрибуты элементов по умолчанию
			'type'		=> 'submit',
			'name'		=> 'button',
		);
		
		if(strpos($url, '.')){
			$url = preg_replace('/([0-9]{2})\.([0-9]{2})\.([0-9]{4})/', '$1_$2_$3', $url);
		}
		
		$elements = array();
		$url = array_map('rawurlencode', explode('/', $url));
		
		foreach($exportDeploy as $element){
			$elementUrl = $url;
    	 	$elementAttribs = $defaultAttribs;
			$element = array_merge($elementDefaults, $element);
			
			if($element['newWindow']) $elementAttribs['target'] = '_blank';
			if($element['cssClass']) $elementAttribs['class'] = $element['cssClass'];
			if($element['id']) $elementAttribs['id'] = $element['id'];
			
			$elementUrl[] = rawurlencode('_exportTo'.$gridId);
			$elementUrl[] = rawurlencode($element['caption']);
			$elementUrl = implode('/', $elementUrl);
			
			$elementAttribs['onclick'] = sprintf($script, $elementUrl);
			$elementAttribs['value'] = $captions[$element['caption']];
			
			$attribsString = array();
			foreach($elementAttribs as $attrib => $value){
				$attribsString[] = $attrib.'="'.htmlspecialchars($value).'"';
			}
			$elements[] = sprintf($elementHTML, implode(' ', $attribsString));
		}
		
		$this->exportWith = 25 * count($exportDeploy);
		$this->paginationWith = 630 + ( 10 - count($exportDeploy) ) * 20;		
		
		return $this->export = implode($elementsDelimiter, $elements);
	}
    
    
    public function export_old_with_bug ($exportDeploy, $images, $url, $gridId) // содержит баг в генерации url
    {
        $captions['print'] = _('Распечатать');
        $captions['word'] = 'Word';
        $captions['excel'] = 'Excel';
        $exp = '';
        foreach ( $exportDeploy as $export ) {
            $export['newWindow'] = isset($export['newWindow']) ? $export['newWindow'] : true;
            $class = isset($export['cssClass']) ? 'class="' . $export['cssClass'] . '"' : '';

            $blank = $export['newWindow'] == false ? '' : "target='_blank'";

            if ( strlen($images)>1) {
                $export['img'] = $images . $export['caption'] . '.gif';
            }
            if (false !== strpos($url, '.')) {
                $url = preg_replace('/([0-9]{2})\.([0-9]{2})\.([0-9]{4})/', '$1_$2_$3', $url);
            }
            $url = explode('/', "$url/_exportTo$gridId/{$export['caption']}");
            foreach ($url as $key => $value) {
                $url[$key] = rawurlencode($value);
            }
            $url = implode('/', $url);
            $js_url = Zend_Json::encode($url);
            $onclick = htmlspecialchars("window.open($js_url); return false;");
            $value = htmlspecialchars($captions[$export['caption']]);
            $exp .= "<input type='submit' name='button' id='grid-print' onclick='$onclick' value='$value'>";
            /*
            if ( isset($export['img']) ) {
                $exp .= "<a title='{$export['caption'] }' $class $blank href='$url/_exportTo$gridId/{$export['caption']}'><img alt='{$export['caption']}' src='{$export ['img']}' border='0'></a>";
            } else {
                $exp .= "<a title='{$export['caption'] }'  $class $blank href='$url/_exportTo$gridId/{$export['caption']}'>" . $export['caption'] . "</a>";
            } */
        }

        $this->exportWith = 25 * count($exportDeploy);
        $this->paginationWith = 630+ ( 10 - count($exportDeploy) ) * 20;

       $this->export = $exp;

        return $exp;
    }

}

