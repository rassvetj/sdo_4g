<?php
require_once('1.php');

$badNames = array(
    'chathistory' => 'CHATHISTORY', 
    'courses' => 'Courses', 
    'courses_stat' => 'Courses_stat', 
    'eventtools' => 'EventTools', 
    'knigi' => 'Knigi',
    'options' => 'OPTIONS',
    'people' => 'People',
    'scheduleid' => 'scheduleID',
    'students' => 'Students',
    'teachers' => 'Teachers',
    'testcontent' => 'TestContent',
    'testtitle' => 'TestTitle'
);

//$adodb_field_names = fopen('adodb_field_names.inc.php', 'w');
fwrite($adodb_field_names, "<?php\n\$_arrFieldNames = array(\n\t");

function make_string($table_name, $table)
{
	$fields = implode(', ', $table);
	$string = $GLOBALS['adodb']->Quote($table_name).'=>array('.$fields.')';
	return $string;
}

$tables = array();
$tables_sql = sql('SHOW TABLES');

while($table_name = sqlget($tables_sql))
{
	$table_name = array_shift($table_name);	
    if (isset($badNames[strtolower($table_name)])) $table_name = $badNames[strtolower($table_name)];
	$table = array();
	$columns_sql = sql('SHOW COLUMNS FROM '.$table_name.';');
	
	while($column = sqlget($columns_sql))
		$table[] = $GLOBALS['adodb']->Quote($column['Field']);	
	
	$tables[] = make_string($table_name, $table);
	//fwrite($adodb_field_names, make_string($table_name, $table));
}

$tables_string = implode(", \n\t", $tables);

fwrite($adodb_field_names, $tables_string);
fwrite($adodb_field_names, "\n);\n?>");
fclose($adodb_field_names);
echo 'done<pre>';
echo '</pre>';