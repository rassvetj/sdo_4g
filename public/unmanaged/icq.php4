<?

   flush();

   // Ї®«п ­ ¤® § Ї®«­Ёвм
   $from='Kovalenko Pavel ';
   $fromemail="pavel@hypermethod.com";
   $subject='fromhyper';
   $to='71419411';  // <-- ­®¬Ґа. (“ўҐаҐ­, зв® Єв®-­Ёвм ­Ґ ¤®Ј ¤ Ґвбп :-)
   $body='Knopkodav =)';

   $submit='Send Message';        // don't edit
   $ref="http://wwp.icq.com/$to"; // don't edit


   // д®а¬Ёа®ў ­ЁҐ § Ј®«®ўЄ 
   $PostData=
   "from=".urlencode($from)."&".
   "fromemail=".urlencode($fromemail)."&".
   "subject=".urlencode($subject)."&".
   "body=".urlencode($body)."&".
   "to=".urlencode($to)."&".
   "submit=".urlencode($submit);
  
   $len=strlen($PostData);
  
   $nn="\r\n";
   $zapros=
"POST /scripts/WWPMsg.dll HTTP/1.0".$nn.
"Referer: $ref".$nn.
"Content-Type: application/x-www-form-urlencoded".$nn.
"Content-Length: $len".$nn.
"Host: wwp.icq.com".$nn.
"Accept: */*".$nn.
"Accept-Encoding: gzip, deflate".$nn.
"Connection: Keep-Alive".$nn.
"User-Agent: Mozilla/4.0 (compatible; MSIE 5.01; Windows NT)".$nn.
"".$nn.
"$PostData";

   echo $zapros."\n\n-------------\n\n\n";
   flush();

   // ®вЄалў Ґ¬ б®ЄҐв Ё и«Ґ¬ § Ј®«®ў®Є
   $fp = fsockopen("wwp.icq.com", 80, &$errno, &$errstr, 30);
   if(!$fp) { print "$errstr ($errno)<br>\n"; exit; }


   // ¤«п ­ Ј«п¤­®бвЁ ўлў®¤Ё¬ § Ј®«®ў®Є ®вўҐв  Ё бва ­Ёжг ­  нЄа ­
   fputs($fp,$zapros);
   print fgets($fp,20048);
   fclose($fp);

?>
