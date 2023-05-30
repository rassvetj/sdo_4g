<?php

$pathParts = pathinfo( $fName );

if( $pathParts["extension"]=="xml")
{
	header("Content-type: application/xml");
	header("Content-Disposition: attachment; filename=".$pathParts["basename"]);
   readfile($fName);
}

if( $pathParts["extension"]=="tar")
{
	header("Content-type: application/tar");
	header("Content-Disposition: attachment; filename=".$pathParts["basename"]);
   readfile($fName);
}


?>