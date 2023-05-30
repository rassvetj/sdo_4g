<?php
class Library 
{
    static function getAuth($namespace) 
    {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session($namespace));
        return $auth;
    }
    
    static function getUserId() {
/*        $user = Library::getAuth('default')->getIdentity();
        return $user['user_id'];*/
        return Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        
    }
    
    static function mkDirIfNotExists($dir, $mode = 0777) {
    	if (!file_exists($dir)) {
    		@mkdir($dir);
    		@chmod($dir, $mode);
    	}
    }
    
    static function streamCopy($url, $file)
	{
	   $url_parsed = parse_url($url);
	
	   $host = $url_parsed["host"];
	   if ($url == '' || $host == '') {
	       return false;
	   }
	   $port = $url_parsed['port'];
	   $path = (empty($url_parsed["path"]) ? '/' : $url_parsed["path"]);
	   $path.= (!empty($url_parsed["query"]) ? '?'.$url_parsed["query"] : '');
	   $out = "GET $path HTTP/1.0\r\nHost: $host\r\nConnection: Close\r\n\r\n";
       if (!$port) $port = 80;
	   $fp = fsockopen($host, $port, $errno, $errstr, 30);
	   if ($fp) {
		   fwrite($fp, $out);
		   $headers = '';
		   $content = '';
		   $buf = '';
		   $isBody = false;
		   while (!feof($fp) and !$isBody) {
		          $buf = fgets($fp, 1024);
		          if ($buf == "\r\n" ) {$isBody = true;}
		          else{$headers .= $buf;}
		   }
		   $file1 = fopen($file, 'wb');
		   if ($file1) {		  
		       $bytes = @stream_copy_to_stream($fp,$file1);
		   }
		   fclose($fp);
	   }
	   
	   return $bytes;
	}
	    
}