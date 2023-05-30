<?
define("MAX_VB_BATCH", 150);

$file_xml_step0 = "c:\\!mdb2els\\src\\data.xml";
$file_xml_step1 = "c:\\!mdb2els\\step2_result\\data.xml";
$file_vb = "c:\\!mdb2els\\src\\data.bas";
$file_vb_result_str = "c:\\!mdb2els\\step1_bas\\data";

$f_vb = fopen($file_vb, 'r');
$str_vb = fread($f_vb,  filesize($file_vb));

$tagnames = array('question', 'reply1', 'reply2', 'reply3', 'reply4', 'reply5');
$i = 0;

$domxml_object = domxml_open_file($file_xml_step0);		
foreach ($tagnames as $tagname) {
	foreach ($nodes = $domxml_object->get_elements_by_tagname($tagname) as $node) {
		$str = $node->get_content();
		if (strpos($str, "{\\rtf1") === 0) {
			$filename = "c:\\!mdb2els\\step1_rtf\\{$i}.rtf";
			$f = fopen($filename, 'w');
			fwrite($f, $str);
			fclose($f);
			
			$newnode =& $domxml_object->create_element($tagname);
			$newnode->set_content("~" . $i++ . "~");
			$node->replace_node($newnode);
		}
	}
}
$domxml_object->dump_file($file_xml_step1);

$k = 0;
while ($i > 0){
	$str_vb_iterations = '';
	for ($j = 0; $j < min($i, MAX_VB_BATCH); $j++){
		$str_vb_iterations .= str_replace('[number]', $j, $str_vb);
	}
	$arr_vb_iterations[++$k] = $str_vb_iterations;
	$i -= MAX_VB_BATCH;
}

foreach ($arr_vb_iterations as $key => $str_vb_iterations) {
	$str_vb_all = "Sub m()\r\n{$str_vb_iterations}End Sub";
	$file_vb_result = $file_vb_result_str . "-" . $key . ".bas";
	$f_vb_result = fopen($file_vb_result, 'w');
	fwrite($f_vb_result, $str_vb_all);
	fclose($f_vb_result);
}
?>