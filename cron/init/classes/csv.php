<?php 
class Csv_cCron {
	
	protected $_path			= false;
	protected $_line_max_length	= 1000;
	protected $_delimiter		= ';';
	protected $_skip_rows		= 1;
	protected $_fields			= array();
	protected $_content			= false;
	

	
	public function setPath($path) {		
		$this->_path = $path;
	}
	
	public function setFields($fields) {		
		$this->_fields = (array) $fields;
	}
	
	public function getCSVContent() {		
		return $this->_content;
	}
	
	public function readCSVContent($file_name){
		if(!$file_name){
			return 'Error. Incorrect file name: '.$file_name;
		}
		
		if (($handle = fopen($this->_path.'/'.$file_name, "r")) === FALSE) {
			return 'Error. File not found on: "'.$this->_path.'/'.$file_name.'"';
		}
		
		$content = array();
		$row = 0;
		while (($data = fgetcsv($handle, $this->_line_max_length, $this->_delimiter)) !== FALSE) {			
			if($row >= $this->_skip_rows){					
				if(count($data) > 0){
					if (!(count($data) == 1 && trim($data[0]) == '')){ //--Исключаем пустую строку						
						foreach($this->_fields as $numCol => $Key){ //--$numCol - порядковый номер названия поля, оно же индекс в CSV файле.
							if(isset($data[$numCol])){
								$tt[$Key] = trim($data[$numCol]);
							} else {
								$tt[$Key] = '';
							}					
						}				
						$content[] = (object)$tt;
					}
				}
			}
			$row++;
		}
		$this->_content = $content;	
		return true;
	}	
}