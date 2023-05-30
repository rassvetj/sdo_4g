<?php
class CWebinar
{
	
	static function getAllowedExtensions()
	{
		return array('swf', 'png', 'gif', 'jpg', 'jpeg', 'svg');
	}
	
	static function getMaterials($cid, $search) 
	{
		$list = array();
		if ($cid && strlen($search)) {
			$sql = "
			    SELECT * 
			    FROM library 
			    WHERE cid = '$cid'
			    AND title LIKE '%".substr($GLOBALS['adodb']->Quote($search), 1, -1)."%'
			    AND title LIKE '%".substr($GLOBALS['adodb']->Quote(CObject::toLower($search)), 1, -1)."%' 
                AND title LIKE '%".substr($GLOBALS['adodb']->Quote(CObject::toUpper($search)), 1, -1)."%'
                AND is_active_version = '1'
                ORDER BY title 
			";
			
			$res = sql($sql);
			while($row = sqlget($res)) {
				$filename = trim($row['filename']);
				if (in_array(strtolower(substr($filename, -3)), self::getAllowedExtensions())) {								
				    $list[$row['bid']] = $row;
				}
			}
		}
		
		return $list;
	}
	
	static function getPointMaterials($pointId)
	{
		$items = array();
		if ($pointId && (null != $pointId)) {
			$sql = "SELECT * FROM webinar_plan WHERE pointId = '$pointId' AND bid > 0";
			$res = sql($sql);
			while($row = sqlget($res)) {
				$items[$row['bid']] = $row;
			}
		}
		
		return $items;
	}
	
	static function getMaterialsOptions($cid, $search, $pointId = null) 
	{
		$options = '';
		$search = str_replace('*', '%', $search);
		$exist = self::getPointMaterials($pointId);
		$items = self::getMaterials($cid, $search);
		if (is_array($items) && count($items)) {
			foreach($items as $item) {
				if (!isset($exist[$item['bid']])) {
				    $options .= "<option value=\"{$item['bid']}\"> {$item['title']}</option>";
				}
			}
		}
		
		return $options;
	}
}