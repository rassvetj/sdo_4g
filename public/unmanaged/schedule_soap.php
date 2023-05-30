<?
    require_once('1.php');

    // функция переведа результата выборки из базы elearning в UTF-8 формат для реализации передачи русских символов через nusoap
  function win2utf($s) 
  { 
    for($i = 0, $m = strlen($s); $i<$m; $i++) 
    { 
        $c=ord($s[$i]); 

        if ($c<=127) 
        {   
            $t.=chr($c); 
            continue; 
        } 
        if ($c>=192 && $c<=207) {$t.=chr(208).chr($c-48);  continue; } 
        if ($c>=208 && $c<=239) {$t.=chr(208).chr($c-48);  continue; } 
        if ($c>=240 && $c<=255) {$t.=chr(209).chr($c-112); continue; } 
        if ($c==184) { $t.=chr(209).chr(209);  continue; }; 
      if ($c==168) { $t.=chr(208).chr(129);  continue; }; 
    } 
    return $t; 
  } 

   //_myconnect();

//подключение нужной версии nusoap
if(strpos($_SERVER["REQUEST_URI"],"wsdl"))
  require_once('lib/nusoap/nusoap.php');
else
  require_once('lib/nusoap/nusoap.new.php');
  $server = new soap_server(); 
//создание wsdl схемы
  $server->configureWSDL('scheduleswsdl', 'urn:scheduleswsdl'); 
  $server->wsdl->schemaTargetNamespace = 'urn:scheduleswsdl'; 
//добавление комплексного типа "массив строк"
  $server->wsdl->addComplexType
    (
    'ArrayOfstring',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'string[]')),
    'xsd:string'
    );  
    
$server->register('schedules',                // название метода 
      array('name' => 'xsd:string','password' => 'xsd:string'),        // входные параметры 
      array('return' => 'tns:ArrayOfstring'),                        // выходные параметры
      'urn:scheduleswsdl',                      // пространство имен 
      'urn:scheduleswsdl#schedules',                // soapaction 
      'rpc',                                // стиль 
      'encoded',                            // использование 
      'return shedule tasks'            // описание 
  ); 
  

  function schedules($name,$password) {                            // $_GET['name']

    $retmas[0]="phpsesid";$i=1;//на будущее - здесь можно хранить phpsesid
     $sql = "SELECT `MID` FROM `people` WHERE `Login` = '".$name."'";   

   $r = sql($sql);
   if ($row = sqlget($r))
   {
     $sql = "SELECT `CID` FROM `students` WHERE `MID` = " .$row['MID'];
     $r = sql($sql);
     if($row = sqlget($r))
     {
     $sql = "SELECT `SHEID`, `title`, `descript`, `begin`, `end` FROM `schedule` WHERE `CID` = '".$row['CID']."'";
       $r = sql($sql);
       while($row = sqlget($r))
    {
    //добавления в масив информации об очередном задании
    array_push($retmas,base64_encode(win2utf(($row['SHEID']."#".$row['title']."#".$row['descript']."#".$row['begin']."#".$row['end']))));
    }
     }
   }
    return $retmas;
  } 
// Используем HTTP-запрос чтобы вызвать сервис.  
  $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : ''; 
  $server->service($HTTP_RAW_POST_DATA); 
