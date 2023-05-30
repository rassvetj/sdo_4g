
sess='<?=$sess?>'
asess='<?=$asess?>'
sessf='<?=$sessf?>'
asessf='<?=$asessf?>'

function wopen(url,name,x,y) {
if (x==undefined) x=790;
if (y==undefined) y=575;
if (name==undefined) name="name"+x+y;
window.open(url,name,"toolbar=0,location=0,directories=0,status=1,menubar=0,"+
"scrollbars=1,resizable=1,width="+x+",height="+y);
}



