<?php
require_once('1.php');
require_once('test.inc.php');

switch ($_GET['action']) {
	case 'import':
		$file_xml_step1 = "c:\\!mdb2els\\step1.xml";
		$tagnames = array('question', 'reply1', 'reply2', 'reply3', 'reply4', 'reply5');
		$i = 0;
		$domxml_object = domxml_open_file($file_xml_step1);
		foreach ($questions = $domxml_object->get_elements_by_tagname('test_contents') as $question) {
			$arr = array();
			foreach ($tagnames as $tagname) {
				$node = array_shift($question->get_elements_by_tagname($tagname));
				$arr[$tagname] = mysql_escape_string(get_content_custom($node));
			}
		
			$sql = sprintf(
		    "INSERT INTO list (kod, qtype, qdata, adata, qtema, balmin, balmax, last, timelimit) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %s)",
		    ($strCode = newQuestion($_GET['CID'])),
		    '1',
		    "{$arr['question']}{$brtag}1{$brtag}{$arr['reply1']}{$brtag}2{$brtag}{$arr['reply2']}{$brtag}3{$brtag}{$arr['reply3']}{$brtag}4{$brtag}{$arr['reply4']}{$brtag}5{$brtag}{$arr['reply5']}",
		    "1",
		    '',
		    0,
		    1,
		    time(),
		    'NULL'
		    );
		//	        echo $sql."<hr>";
		//	        exit();
		    sql($sql);
		}
		break;
	default:
		$str = "<form name='form1' method='post' action='' enctype='multipart/form-data'>Укажите файл (.zip):&nbsp;<input type='file' name='textfield'><br><br><input type='submit' name='Submit' value='Отправить'></form>";
		$controller->captureFromReturn(CONTENT, $str);
	break;
	
}

function get_content_custom($node){
	$str = $node->get_content();
	if (strpos($str, '~') === 0) {
		list(,$i,) = explode('~', $str);
		$filename = "c:\\!mdb2els\\step1_result\\{$i}.htm";
		$f_htm = fopen($filename, 'r');
		$str = fread($f_htm, filesize($filename));
		$str = strip_tags($str, '<IMG>');
		$str = str_replace('<IMG ', '<IMG align="absmiddle"', $str);
		$str = str_replace('SRC="Image', 'SRC="COURSES/course' . $_GET['CID'] . '/TESTS/Image', $str);
		$str = str_replace('\n', '<br>', $str);
		$str = trim($str);
	} else {
		$str = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding ,$str);
	}
	return $str;	
}
?>