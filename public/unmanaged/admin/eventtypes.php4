<?php
	require_once('dir_set.inc.php4');
	require_once('event.lib.php4');	

	$words=array();

	istest();

//	if ($dean && $make=="move" && $nID) show_hide_news($nID);
//	if ($dean && $make=="remove" && $nID) remove_news($nID);
	

    $html=show_tb(1);

	$allcontent=loadtmpl("news-main.html");
	$newsheader=loadtmpl("all-cHeader.html");
	$newsimages=loadtmpl("all-images.html");
	$newswords=loadwords("news-words.html");
	$newssort=loadtmpl("news-sort.html");

//	$newsadd=($dean) ? loadtmpl("news-add.html") : "";

//	$allnews=show_news_table();

	$words['PAGENAME']=$newswords[0];
	$words['edit']=$newswords[1];
	$words['delete']=$newswords[2];
	$words['show']=$newswords[3];
	$words['hide']=$newswords[4];
	$words['sort']=$newswords[9];
	$words['byID']=$newswords[10];
	$words['byDate']=$newswords[11];
	$words['addn']=$newswords[12];
	$words['nall']=$newswords[13];	
	$words['PAGESTATIC']=loadtmpl("news-static.html");


    $allcontent=str_replace("[NEWS-HEADER]",$newsheader,$allcontent);
    $allcontent=str_replace("[NEWS-FULL]",$allnews,$allcontent);
    $allcontent=str_replace("[NEWS-IMAGES]",$newsimages,$allcontent);

    $allcontent=str_replace("[NEWS-ADD]",$newsadd,$allcontent);
    $allcontent=str_replace("[NEWS-SORT]",$newssort,$allcontent);


	$html=str_replace("[ALL-CONTENT]",$allcontent,$html);

	printtmpl($html);
?>