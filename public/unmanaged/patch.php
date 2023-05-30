<?
require_once("1.php");
/*$q = "
        UPDATE
          `scheduleid`
        SET
          toolParams = CONCAT(toolParams, 'formula_id=6;')
        WHERE
          toolParams LIKE '%tests_testID=88;%' AND
          toolParams NOT LIKE '%formula_id=6;%'
";*/

$q = "
        UPDATE
          `scheduleid`
        SET
          toolParams = ".$adodb->Concat("toolParams", "formula_id=6")."
        WHERE
          toolParams LIKE '%tests_testID=88;%' AND
          toolParams NOT LIKE '%formula_id=6;%'
";

if ($r = sql($q)) echo "updating formula: ok<br>";
?>