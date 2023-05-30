dom = (document.getElementById)? true : false;
nn4 = (document.layers)? true : false;
ie4 = (!dom && document.all)? true : false;

function setGray(elemId) {
 if (dom)
  document.getElementById(elemId).style.color = "#AAAAAA";
 else
  if (ie4)
   document.all[elemId].style.color = "#AAAAAA";
  else
   alert('Ваш браузер не позволяет менять свойство display для элемента: '+elemId)
}

function putElem(elemId) {
 if (dom)
  document.getElementById(elemId).style.display = "block";
 else
  if (ie4)
   document.all[elemId].style.display = "block";
  else
   alert('Ваш браузер не позволяет менять свойство display для элемента: '+elemId)
}

function putElement(elemId, method) {

 method = (document.all) ? 'block' : method;

 if (dom)
  document.getElementById(elemId).style.display = method;
 else
  if (ie4)
   document.all[elemId].style.display = method;
  else
   alert('Ваш браузер не позволяет менять свойство display для элемента: '+elemId)
}

function removeElem(elemId) {
 if (dom)
  document.getElementById(elemId).style.display = "none";
 else
  if (ie4)
   document.all[elemId].style.display = "none";
  else
   alert('Ваш браузер не позволяет менять свойство display для элемента: '+elemId)
}

function putElemInline(elemId) {
 if (dom)
  document.getElementById(elemId).style.display = "inline";
 else
  if (ie4)
   document.all[elemId].style.display = "inline";
  else
   alert('Ваш браузер не позволяет менять свойство display для элемента: '+elemId)
}

function showHideTr(elemId, show) {
	tr_obj = document.getElementById(elemId);
	var ua = navigator.userAgent.toLowerCase();
	if (show) {
		if (ua.indexOf("msie") != -1 && ua.indexOf("opera") == -1 && ua.indexOf("webtv") == -1) {
			tr_obj.style.display = 'block';
		} else {
			tr_obj.style.display = 'table-row';
		}
	} else {
		tr_obj.style.display = 'none';
	}
}

function new_mod_make(elemId)
{
  if (elemId=='1') document.add.make.value='add_new_podmod';
  if (elemId=='2') document.add.make.value='add_new_podmod';
  if (elemId=='3') document.add.make.value='indexing';
  if (elemId=='5') document.add.make.value='add_test';
  if (elemId=='4') document.add.make.value='add_forum';

}
function putLine(n)
{ putElem('taskfull'+n);
  removeElem('task'+n);
}
function removeLine(n)
{ putElem('task'+n);
  removeElem('taskfull'+n);
}
function putAll(day,n)
{
 removeElem('plusmain'+day);
 putElem('minusmain'+day);
 for(;n>0;n--)
  putLine(""+day+n);
}
function removeAll(day,n)
{
 putElem('plusmain'+day);
 removeElem('minusmain'+day);
 for(;n>0;n--)
  removeLine(""+day+n);
}

function setChecked( id ){
  if ( id.checked )
      id.checked = 0;
  else
      id.checked = 1;
}

function setCheckboxes(the_form)
{
    var elts      = (typeof(document.forms[the_form].elements['select[]']) != 'undefined')
                  ? document.forms[the_form].elements['select[]']
                  : document.forms[the_form].elements['select[]'];
    var elts_cnt  = (typeof(elts.length) != 'undefined')
                  ? elts.length
                  : 0;
    var is       = (typeof(document.forms[the_form].elements['toall']) != 'undefined')
                  ? document.forms[the_form].elements['toall']
                  : 0;

    var is_check  = (typeof(is.value) != 'undefined')
                  ? is.value
                  : 0;

   if (is_check==1) {
               do_check='true';
               document.forms[the_form].elements['toall'].value="0";
              }
      else {  do_check=''
            document.forms[the_form].elements['toall'].value="1";
         }

    if (elts_cnt) {
        for (var i = 0; i < elts_cnt; i++) {
            elts[i].checked = do_check;
        } // end for
    } else {
        elts.checked        = do_check;
    } // end if... else

    return true;
} // end of the 'setCheckboxes()' function


function swithSH (sVar, obj, sObj) {
   sVar^=1;
   if (sVar) {
               obj.style.display='block';
               sObj.innerHTML='6';
             } else {
               obj.style.display='none';
               sObj.innerHTML='4';
             }
   return sVar;
}// end swithSH function

function putTreeElementsByPrefix(prefix_elemId, method) {

  method = (document.all) ? 'block' : method;

  var minus = document.getElementById(prefix_elemId+'_minus');
  if (minus) minus.style.display = 'inline';
  var plus = document.getElementById(prefix_elemId+'_plus');
  if (plus) plus.style.display = 'none';

    var j=1;
    var item = document.getElementById(prefix_elemId+'_'+j);
    while(item) {
        item.style.display = method;
//        putTreeElementsByPrefix(prefix_elemId+'_'+j);
        j++;
        item = document.getElementById(prefix_elemId+'_'+j);
    }
}

function putTreeElementsByPrefixAll(prefix_elemId, method) {

  method = (document.all) ? 'block' : method;

  var minus = document.getElementById(prefix_elemId+'_minus');
  if (minus) minus.style.display = 'inline';
  var plus = document.getElementById(prefix_elemId+'_plus');
  if (plus) plus.style.display = 'none';

    var j=1;
    var item = document.getElementById(prefix_elemId+'_'+j);
    while(item) {
        item.style.display = method;
        putTreeElementsByPrefixAll(prefix_elemId+'_'+j, method);
        j++;
        item = document.getElementById(prefix_elemId+'_'+j);
    }
}

function removeTreeElementsByPrefix(prefix_elemId) {

    var minus=document.getElementById(prefix_elemId+'_minus');
    if (minus) minus.style.display = 'none';

    var plus = document.getElementById(prefix_elemId+'_plus');
    if (plus) plus.style.display = 'inline';

    var prefix = prefix_elemId.substring(0,3);

    var global_minus = document.getElementById(prefix+'_0_minus');
    if (global_minus) global_minus.style.display = 'none';

    var global_plus = document.getElementById(prefix+'_0_plus');
    if (global_plus) global_plus.style.display = 'inline';

    var j=1;
    var sub_item;
    var item = document.getElementById(prefix_elemId+'_'+j);
    while(item) {
        item.style.display = "none";

        sub_item = document.getElementById(prefix_elemId+'_'+j+'_1');
        if ((sub_item)&&(sub_item.style.display!='none'))
        removeTreeElementsByPrefix(prefix_elemId+'_'+j);
        j++;
        item = document.getElementById(prefix_elemId+'_'+j);
    }

}

function removeTreeElementsByPrefixAll(prefix_elemId, level) {

    var minus = document.getElementById(prefix_elemId+'_minus');
    if (minus) minus.style.display = 'none';
    var plus = document.getElementById(prefix_elemId+'_plus');
    if (plus) plus.style.display = 'inline';

    var sub_item;
    var j=1;
    var item = document.getElementById(prefix_elemId+'_'+j);
    while(item) {
        if (level>0) item.style.display = "none";

        sub_item = document.getElementById(prefix_elemId+'_'+j+'_1');
        if ((sub_item) && (sub_item.style.display!='none'))
        removeTreeElementsByPrefix(prefix_elemId+'_'+j,level+1);

        j++;
        item = document.getElementById(prefix_elemId+'_'+j);
    }
}
