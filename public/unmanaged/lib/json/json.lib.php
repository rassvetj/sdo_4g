<?php

/**
 * Функция проверяет, чтобы в json_callback
 * содержались только латинские символы или знак
 * подчёркивания
 */
function json_callback_function_valid($var)
{
	if (!isset($var)) { return false; }
	$strlen_var = strlen($var);
	for ($c = 0; $c < $strlen_var; ++$c)
	{
		$ord_var_c = ord($var{$c});
		switch (true) {
			case $ord_var_c == 0x5F:
			case (($ord_var_c >= 0x41) && ($ord_var_c <= 0x5A)):
			case (($ord_var_c >= 0x61) && ($ord_var_c <= 0x7A)):
			case (($ord_var_c >= 0x30) && ($ord_var_c <= 0x39)):
				continue;
				break;
			default:
				return false;
				break;
		}
	}
	return true;
}
function json_id_valid($var)
{
	if (!isset($var)) { return false; }
	$strlen_var = strlen($var);
	for ($c = 0; $c < $strlen_var; ++$c)
	{
		$ord_var_c = ord($var{$c});
		switch (true) {
			case $ord_var_c == 0x5F:
			case (($ord_var_c >= 0x41) && ($ord_var_c <= 0x5A)):
			case (($ord_var_c >= 0x61) && ($ord_var_c <= 0x7A)):
			case (($ord_var_c >= 0x30) && ($ord_var_c <= 0x39)):
			case ($ord_var_c == 0x3A):
				continue;
				break;
			default:
				return false;
				break;
		}
	}
	return true;
}

/**
 * Перебираем массив результатов поиска
 * и исключаем из результирующего массива ненужные элементы
 * а также укорачиваем названия индексов
 * Всё это нужно для того, чтобы полученный JSON был как можно
 * меньше по размеру
 */
function refactor_search_results_for_json_output($search_results,$page_number,$total_pages,$json_id,$search_string,$sort) {
	if ($search_results) {
		foreach($search_results as $k => $v) {
			$refactored_array[$k]['id'] = $v['bid'];
			$refactored_array[$k]['t'] = $v['title'];
			$refactored_array[$k]['a'] = $v['author'];
			$refactored_array[$k]['p'] = $v['publisher'];
			$refactored_array[$k]['pd'] = $v['publish_date'];
			$refactored_array[$k]['tp'] = $v['type'];
			$refactored_array[$k]['q'] = $v['quantity'];
		}
	}
	$refactored_object['sort'] = ($sort) ? $sort : 0;
	$refactored_object['loggedIn'] = true;
	$refactored_object['json_id'] = $json_id;
	$refactored_object['search_string'] = $search_string;
	$refactored_object['pageNumber'] = ceil($page_number/ITEMS_PER_PAGE) + 1;
	$refactored_object['totalPages'] = $total_pages;
	$refactored_object['itemsPerPage'] = ITEMS_PER_PAGE;
	$refactored_object['searchResults'] = ($search_results) ? $refactored_array : false;
	
	return $refactored_object;
}

function refactor_people_list_for_json_output($people_list) {
	$refactored_array[]['v'] = 0;
	$refactored_array[0]['l'] = 'не имеет значения';
	$refactored_array[0]['c'] = ' не имеет значения';
	$i = 0;
	if ($people_list) {
		foreach($people_list as $k => $v) {
			$i++;
			$refactored_array[$i]['v'] = $people_list[$k]['MID'];
			$refactored_array[$i]['l'] = $people_list[$k]['LastName'].' '.$people_list[$k]['FirstName'];
			$refactored_array[$i]['c'] = ' '.$people_list[$k]['LastName'].' '.$people_list[$k]['FirstName'];
		}
	}
	return $refactored_array;
}

?>
