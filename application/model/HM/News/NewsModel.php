<?php
class HM_News_NewsModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Activity_ActivityModel::ACTIVITY_NEWS;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        return Zend_Registry::get('config')->url->base.'images/events/redmond_test.png';

    }

    public function isExternalExecuting() {
        return true;
    }

    public function getExecuteUrl() {
        return "http://www.yandex.ru";
    }

    public function getResultsUrl($options = array())
    {

    }

    public function getFilteredMessage()
    {
        return strip_tags($this->message);
    }

    public function getCut()
    {
        $mPos = strpos($this->message, '<!--more-->');
        if($mPos === false) {
            $mPos = strpos($this->message, '<!-- pagebreak -->');
        }
        $body = $this->message;
        if($mPos !== false) $body = substr($this->message, 0, $mPos);
        return stripslashes($body);
    }
	
	
	
	
	public function isHasInnerLink()
	{
		if(
			mb_stripos($this->message, 'href="http://sdo.rgsu.net') !== false
			||
			mb_stripos($this->message, 'href="https://sdo.rgsu.net') !== false
			||
			mb_stripos($this->message, 'href="sdo.rgsu.net') !== false
		){
			return true;
		}
		return false;
	}
	
	public function isHasAnyLink()
	{
		if(
			mb_stripos($this->message, 'href="http://') !== false
			||
			mb_stripos($this->message, 'href="https://') !== false
			||
			mb_stripos($this->message, 'href="') !== false
		){
			return true;
		}
		return false;
	}
	
	public function isVideoNews()
	{
		$announce = $this->clearString($this->announce);
		
		if( mb_stripos($announce, 'ЛекционныйМатериал(Видео)') !== false ){
			return true;
		}
		return false;
	}
	
	public function isHasModuleNumberLandmark($number)
	{
		$announce = $this->clearString($this->announce);
		$message  = $this->clearString($this->message);
		
		if(
			mb_stripos ($announce, 'РубежныйКонтроль'.$number) 			!== false	||	mb_stripos ($message, 'РубежныйКонтроль'.$number)			!== false
			||
			mb_stripos ($announce, 'РубежныйКонтрольКРазделу'.$number)	!== false	||	mb_stripos ($message, 'РубежныйКонтрольКРазделу'.$number)	!== false			
		){
			return true;
		}
		return false;
	}
	
	public function isHasModuleNumberTask($number)
	{
		$announce = $this->clearString($this->announce);
		$message  = $this->clearString($this->message);
		
		if(
			mb_stripos ($announce, 'ПрактическоеЗадание'.$number) 			!== false	||	mb_stripos ($message, 'ПрактическоеЗадание'.$number)			!== false
			||
			mb_stripos ($announce, 'ПрактическоеЗаданиеКРазделу'.$number)	!== false	||	mb_stripos ($message, 'ПрактическоеЗаданиеКРазделу'.$number)	!== false
			||
			mb_stripos ($announce, 'ПрактическиеЗадания'.$number) 			!== false	||	mb_stripos ($message, 'ПрактическиеЗадания'.$number)			!== false
			||
			mb_stripos ($announce, 'ПрактическиеЗаданияКРазделу'.$number)	!== false	||	mb_stripos ($message, 'ПрактическиеЗаданияКРазделу'.$number)	!== false
			||
			mb_stripos ($announce, 'ЗаданиеКРазделу'.$number) 				!== false	||	mb_stripos ($message, 'ЗаданиеКРазделу'.$number)				!== false
			||
			mb_stripos ($announce, 'ЗаданияКРазделу'.$number) 				!== false	||	mb_stripos ($message, 'ЗаданияКРазделу'.$number)				!== false
		){
			return true;
		}
		return false;
	}
	
	public function clearString($str)
	{
		$str	= strip_tags($str); 
		$str 	= preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str);
		$str	= str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    ',"&nbsp;", " ", '.'), '', $str);
		$str	= str_replace(" ",'',$str);
		return $str;
	}
	
	
	
}