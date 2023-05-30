<?php

class HM_Resource_Adapter_Docx extends HM_Resource_Adapter_Abstract
{
    protected $_file;
    
    public function __construct($params)
    {
        $this->_file = $params['file'];
    }

	function readFile() {
	    $archiveFile = $this->_file;
	    $contentFile = 'word/document.xml';
		// Создаёт "реинкарнацию" zip-архива...
		$zip = new ZipArchive ();
		// И пытаемся открыть переданный zip-файл
		if ($zip->open ( $archiveFile )) {
			// В случае успеха ищем в архиве файл с данными
			if (($index = $zip->locateName ( $contentFile )) !== false) {
				// Если находим, то читаем его в строку
				$content = $zip->getFromIndex ( $index );
				// Закрываем zip-архив, он нам больше не нужен
				$zip->close ();
				
				// После этого подгружаем все entity и по возможности include'ы других файлов
				// Проглатываем ошибки и предупреждения
				$xml = DOMDocument::loadXML ( $content, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING );
				// После чего возвращаем данные без XML-тегов форматирования
				print strip_tags ( $xml->saveXML () );
			}
			$zip->close ();
		}
		// Если что-то пошло не так, возвращаем пустую строку
		return "";
	}
    
}