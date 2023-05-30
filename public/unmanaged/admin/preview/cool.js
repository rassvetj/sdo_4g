var width=200 ;

var x,y ;
var visible=0;


var pW, pH ;

var ns4 = (document.layers)? true:false;
var ns6 = (document.getElementById)? true:false;
var ie4 = (document.all)? true:false;
var ie5 = false;

// Microsoft Stupidity Check(tm).
if (ie4) {
        if ((navigator.userAgent.indexOf('MSIE 5') > 0) || (navigator.userAgent.indexOf('MSIE 6') > 0)) {
                ie5 = true;
        }
        if (ns6) {
                ns6 = false;
        }
}
var visible;
var meg;
var image;

function handlerMM(e){
 x  =   (document.layers) ? e.pageX     : event.clientX ;
 y  =   (document.layers) ? e.pageY     : event.clientY ;
 pW =   (document.layers) ? innerWidth  : document.body.offsetWidth-4;
 pH =   (document.layers) ? innerHeight : document.body.offsetHeight-2;
 ShowMenuMove();

}

        document.onmousemove = handlerMM;        

function ShowMenu(param,img,w) {

        if (visible<1)
                {
//               ClearAll();
                 width = (w > 200) ? w : 200 ;
                 layerWrite(param,img);
                 image=img;

                 if ( (ns4) || (ie4) || (ns6) ) 
                    
                    {
                      if (ns4) meg = document.mega ;
                      if (ie4) meg = mega.style ;
                      if (ns6) meg = self.document.getElementById("mega");
                    }

//                 window.document.all['mega'].style['visibility']='visible';
                showObject(meg);

                 oElement = window.event.srcElement;
                         if (oElement == '[object]')
                                 oElement.style.cursor = "hand";
        
                 visible=1;

                 ShowMenuMove();
                } 

      }

function ShowMenuMove()
{
       if (visible>0) 
         {
                scrolloffset = (ie4) ? document.body.scrollTop : pageYOffset;

                document.all['mega'].style.posLeft= (x+width+30 < pW) ? x+10 : pW-width-20 ;
         
                document.all['mega'].style.posTop = (y+40  < pH) ? y + scrolloffset+10 : pH - 30 + scrolloffset;
              
         }
        
}

function layerWrite(txt,img) {
        lable =  "<table border='0' width='"+width+"' cellspacing='1' cellpadding='0' bgcolor='black'><tr><td align=center bgcolor='f5f5f5'>";
        lable+=txt;
//        lable+=" - ";
//        lable+=img;
        lable+=  "</td></tr><tr><td align=center bgcolor='white'>";
        lable+=  "<img src='"+img+"'>"
        lable+=  "</td></tr></table>";
        txt = lable;
        
        if (ns4) {
                var lyr = document.mega.document

                lyr.write(txt)
                lyr.close()
        } else if (ie4) {
                document.all["mega"].innerHTML = txt
        } 
        
}

function CloseMenu() 
        {
//      head=window.setTimeout("window.document.all['mega'].style['visibility']='hidden'", 0);
//        window.document.all['mega'].style['visibility']='hidden';
        if ( (ns4) || (ie4) || (ns6) ) {
                      if (ns4) meg = document.mega ;
                      if (ie4) meg = mega.style ;
                      if (ns6) meg = document.getElementById("mega");
                        }

        hideObject(meg);
        visible=0;
        }

function ClearAll()
{
//       window.clearTimeout(head);
}

function showObject(meg) {
        if (ns4) meg.visibility = "show";
        else if (ie4) meg.visibility = "visible";
        else if (ns6) meg.style.visibility = "visible";
}

// Hides an object
function hideObject(meg) {
        if (ns4) meg.visibility = "hide";
        else if (ie4) meg.visibility = "hidden";
        else if (ns6) meg.style.visibility = "hidden";
       
}
function get_image_name()
{
//alert(image);
window.returnValue=image;
window.close()
}