<?php 
// header('Content-Type: text/html; charset=windows-1251');

   $connOptions = array("UID"=>"", "PWD"=>"", "Database"=>"sdo");
   $conn = sqlsrv_connect( "srv-sql12-n1", $connOptions );
   if( $conn === false ) {
        die( print_r( sqlsrv_errors(), true));
   }


$query_check_user="SELECT TOP 1 [mid_external]   
  FROM [sdo].[dbo].[People] where MID='5119'";

$result_user=sqlsrv_query($conn,$query_check_user) ;

$row_check_user = sqlsrv_fetch_array( $result_user, SQLSRV_FETCH_ASSOC);

//$userId=iconv("utf-8", "windows-1251", $row_check_user['mid_external']);

$userId=$row_check_user['mid_external'];



$query="SELECT UopInfoID
      ,StudyCode
      ,DocNum
      ,CONVERT(nvarchar(30), Date, 104) as DocDate
      ,NumPop
      ,Type
      ,Ball
      ,Vid
      ,Disciplina  FROM sdo.dbo.UopInfoStud where StudyCode=(SELECT TOP 1 [mid_external]   
  FROM [sdo].[dbo].[People] where mid_external='".$userId."') order by Date";
//echo $query;
$result=sqlsrv_query($conn,$query) 
    or die("Can't query \"$query\"\n"); 
echo "<table>";

while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC) ) {
     echo "<tr>
		<td>".iconv("windows-1251", "utf-8",$row['Disciplina'])." (".iconv("windows-1251", "utf-8",$row['Type']).")</td>
		<td>".iconv("windows-1251", "utf-8",$row['Ball'])."</td>
		<td>".iconv("windows-1251", "utf-8",$row['NumPop'])."</td>
		<td>".iconv("windows-1251", "utf-8",$row['DocNum'])."</td>
		<td>".iconv("windows-1251", "utf-8",$row['DocDate'])."</td>

</tr>";
}
echo "</table>";
?>