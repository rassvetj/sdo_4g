function wopen(page,name,x,y)
{
   if (typeof(x)=='undefined') x=790; //600;
   if (typeof(y)=='undefined') y=575; //425;
   if (typeof(name)=='undefined') name='popup';
   w = window.open(page,name, "toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width="+x+", height="+y);
   w.focus();
}

function newImage(arg) {
 if (document.images) {
  rslt = new Image();
  rslt.src = arg;
  return rslt;
 }
}

function changeImages() {
 if (document.images && (preloadFlag == true)) {
  for (var i=0; i<changeImages.arguments.length; i+=2) {
   document[changeImages.arguments[i]].src = changeImages.arguments[i+1];
  }
 }
}

var preloadFlag = false;

function preloadImages() {
 if (document.images) {
  preloadFlag = true;
 }
}

function showcalendar() {
	var monthdays = [
		31, 28, 31, 30,
		31, 30, 31, 31,
		30, 31, 30, 31
	];
	var dayClasses = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
	todayDate = new Date();
	thisday   = todayDate.getDay();
	thismonth = todayDate.getMonth();
	thisdate  = todayDate.getDate();
	var thisyear  = todayDate.getYear() % 100;
	thisyear  = ((thisyear < 50) ? (2000 + thisyear) : (1900 + thisyear));
	if (((thisyear % 4 == 0) && !(thisyear % 100 == 0))||(thisyear % 400 == 0)) {
		monthdays[1] = 29;
	}
	startspaces = thisdate;
	while (startspaces > 7) { startspaces -= 7; }
	startspaces = thisday - startspaces;
	if (startspaces < 0) { startspaces += 7; }
	var rowspan = Math.ceil( (startspaces + monthdays[thismonth]) / 7 );
	var CalTable = [
		'<table cellpadding="0" cellspacing="0" class="w100 calendar">',
		'	<tr class="top">',
		'		<td class="left"></td>',
		'		<td class="mon">Пн</td>',
		'		<td class="tue">Вт</td>',
		'		<td class="wed">Ср</td>',
		'		<td class="thu">Чт</td>',
		'		<td class="fri">Пт</td>',
		'		<td class="sat">Сб</td>',
		'		<td class="sun">Вс</td>',
		'		<td class="right"></td>',
		'	</tr>',
		'	<tr class="middle mtop">',
		'		<td class="left" rowspan="'+ rowspan +'"></td>'
	];
	for (var s = 0; s < startspaces; s++) {
		CalTable.push('<td class="'+ dayClasses[s] +'"></td>');
	}
	var count = 1;
	var row = 1;
	while (count <= monthdays[thismonth]) {
		for (var b = startspaces; b < 7; b++) {
			CalTable.push('<td class="'+ dayClasses[b] +'"><span'+ (count == thisdate ? ' class="current-day"' : '') + '>' + (count <= monthdays[thismonth] ? count : '') +'</span></td>');
			count++;
		}
		if (row == 1) {
			CalTable.push('<td class="right" rowspan="'+ rowspan +'"></td>');
		}
		row++;
		CalTable.push('</tr>');
		if (row <= rowspan) {
			CalTable.push('<tr class="middle'+ (row == rowspan ? ' mbottom' : '') +'">');
		}
		startspaces = 0;
	}
	CalTable.push('	<tr class="bottom">');
	CalTable.push('		<td class="left"></td>');
	CalTable.push('		<td class="mon"></td>');
	CalTable.push('		<td class="tue"></td>');
	CalTable.push('		<td class="wed"></td>');
	CalTable.push('		<td class="thu"></td>');
	CalTable.push('		<td class="fri"></td>');
	CalTable.push('		<td class="sat"></td>');
	CalTable.push('		<td class="sun"></td>');
	CalTable.push('		<td class="right"></td>');
	CalTable.push('	</tr>');
	CalTable.push(' <tr style="height: 2px"><td colspan="9"></td></tr>');
	CalTable.push('	<tr class="calendar-reflection">');
	CalTable.push('		<td class="left"></td>');
	CalTable.push('		<td class="mon"></td>');
	CalTable.push('		<td class="tue"></td>');
	CalTable.push('		<td class="wed"></td>');
	CalTable.push('		<td class="thu"></td>');
	CalTable.push('		<td class="fri"></td>');
	CalTable.push('		<td class="sat"></td>');
	CalTable.push('		<td class="sun"></td>');
	CalTable.push('		<td class="right"></td>');
	CalTable.push('	</tr>');
	CalTable.push('</table>');
	return CalTable.join('\n');
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}