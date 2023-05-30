<?php
	function date_loc2sql($str)
	{
   		 list($day,$month, $year) =split("[/.-]",$str);
	   	 return "$year-$month-$day";
	}

	function date_sql2loc($str)
	{
    	list($year,$month,$day) =split("[/.-]",$str);
	    return "$day.$month.$year";
	}
?>