<?
require_once("1.php");
require_once('news.lib.php4');
//$controller->setView('DocumentPopup');
$controller->page_id = 'infoblock';
$controller->setHelpSection('infoblock');
if (!empty($_GET['id'])){
	$query = "SELECT * FROM news2 WHERE nID='{$_GET['id']}' AND `show`='1'";
	$res = sql($query);
	if ($row = sqlget($res)){
		$controller->setHeader($row['Title']);
		$controller->captureFromOb(CONTENT);
		echo <<<E0D
        	<div class="card-info-block">
        		{$row['message']}
        			<div class="clear-both"></div>
        	</div>
E0D;
		$controller->captureStop(CONTENT);
		$controller->terminate();
		exit();
	}
}
$controller->setMessage("Инфорамационный блок не найден", JS_GO_BACK);
$controller->terminate();
exit();
?>