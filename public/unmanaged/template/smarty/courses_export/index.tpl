<script language="JavaScript">
<!--

var j=0;
var d = new Date();
var files = new Array();

while(d.getDay()!=1) {
    d.setTime(d.getTime() - 11*60*60*1000);
}

d.setUTCHours(0,0,0,0);

{?if $data.schedules?}
    {?foreach from=$data.schedules item=i?}
files[j++] = '{?$i?}';
    {?/foreach?}

{?/if?}

	for(j=0; j<files.length; j++) {
    if (files[j] <= d.getTime() + 60*60*1000 && files[j] >= d.getTime() - 60*60*1000) {
    	window.location.href = 'index_'+files[j]+'.html';
    }
}

if (files.length) {
	if (d.getTime() > files[files.length-1]) {
	    window.location.href = 'index_'+files[files.length-1]+'.html';
	}

	if (d.getTime() < files[0]) {
	    window.location.href = 'index_'+files[0]+'.html';
	}
}

//-->
</script>
