(function(c){function ga(z,s){function m(){u=j.length;p=v*2/u;for(var b=0;b<u;b++)ha(b);o(g);c.browser.msie||N(0);c(document).bind("mousemove",function(d){k=d.pageX;q=d.pageY});z.onselectstart=function(){return false};a.settings.autoScroll&&A();a.settings.mouseScroll&&O();a.settings.mouseDrag&&X();a.settings.mouseWheel&&Y();a.settings.scrollbar&&ia();a.settings.tooltip&&c('<div class="tooltip"><p></p></div>').css("opacity",0).appendTo(i)}function ha(b){var d=c('<img class="carousel-item"/>').appendTo(i);
Z.push(d);d.css({width:a.settings.itemWidth,h:a.settings.itemHeight}).data({w:a.settings.itemWidth,h:a.settings.itemHeight,index:b}).addClass("out").bind({mouseover:function(){c(this).hasClass("out")&&c(this).removeClass("out").addClass("over");a.settings.tooltip&&ja(b);if(a.settings.mouseScroll)B=a.settings.mouseScrollSpeedHover;var e={type:"itemMouseOver",index:b,data:j[b]};c.isFunction(a.settings.itemMouseOver)&&a.settings.itemMouseOver.call(this,e)},mouseout:function(){c(this).hasClass("over")&&
c(this).removeClass("over").addClass("out");a.settings.tooltip&&ka();if(a.settings.mouseScroll)B=a.settings.mouseScrollSpeed;var e={type:"itemMouseOut",index:b,data:j[b]};c.isFunction(a.settings.itemMouseOut)&&a.settings.itemMouseOut.call(this,e)},click:function(){i.find(".click").removeClass("click").addClass("out");c(this).removeClass("over").addClass("click");a.settings.scrollOnClick&&C(b);if(j[b].link)window.open(j[b].link,j[b].linkTarget||a.settings.linkTarget);var e={type:"itemClick",index:b,
data:j[b]};c.isFunction(a.settings.itemClick)&&a.settings.itemClick.call(this,e)}});j[b].link&&d.css("cursor","pointer");c.browser.msie&&N(b)}function N(b){var d=j[b].path,e=Z[b];c("<img/>").load(function(){var h=parseInt(c(this).attr("width")||c(this).prop("width")),f=parseInt(c(this).attr("height")||c(this).prop("height"));if(a.settings.crop)e.css("background-image","url("+d+")");else{e.attr("src",d);e.css("background-image","none")}if(a.settings.resize)if(a.settings.maintainAspectRatio){scaleX=
a.settings.itemWidth/h;scaleY=a.settings.itemHeight/f;if(scaleX<scaleY){h*=scaleX;f*=scaleX}else{h*=scaleY;f*=scaleY}}else{h=a.settings.itemWidth;f=a.settings.itemHeigh}e.css({width:h,height:f});e.data({w:h,h:f});o(g);c.browser.msie||b<u-1&&N(++b)}).attr("src",d)}function o(b){i.find(".carousel-item").each(function(d){var e=c(this),h=e.data("w"),f=e.data("h"),D=Math.sin(-(p*d)+v*0.5+b*w)*a.settings.verticalRadius+P-f*0.5,r=(D-($-f*0.5))/(aa-$)*(1-a.settings.scaleRatio)+a.settings.scaleRatio;e.css({width:h*
r,height:f*r,left:Math.cos(-(p*d)+v*0.5+b*w)*a.settings.horizontalRadius+Q-h*0.5+h*(1-r)/2,top:D+f*(1-r)/2,"z-index":Math.floor(r*10*u)})});a.settings.scrollbar&&!x&&la(R())}function C(b){l=b;var d=p*(180/v)*b%360;g%=360;if(Math.abs(d-g)>180)d+=d>g?-360:360;if(d-g>180&&d>g)d-=360;E();S=setInterval(function(){if(Math.abs(d-g)>0.5){g+=(d-g)*(a.settings.scrollSpeed/100);o(g)}else F()},30);b={type:"itemSelect",index:l,data:j[l]};c.isFunction(a.settings.itemSelect)&&a.settings.itemSelect.call(this,b)}
function ba(){C(l==j.length-1?0:l+1)}function ca(){C(l==0?j.length-1:l-1)}function A(){if(!(G&&a.settings.pauseAutoScrollIfTooltip)){a.settings.autoScroll=true;t=setTimeout(function(){if(a.settings.autoScrollDirection=="next")ba();else a.settings.autoScrollDirection=="previous"&&ca()},a.settings.autoScrollDelay)}}function O(){a.settings.mouseScroll=true;B=a.settings.mouseScrollSpeed;var b=0,d=a.settings.mouseScrollReverse?-1:1;H=setInterval(function(){if(k>i.offset().left&&k<i.offset().left+a.settings.width&&
q>i.offset().top&&q<i.offset().top+a.settings.height){b=d*(k-(i.offset().left+Q))*(B/1E3);g+=b;o(g)}else if(Math.abs(b)>0.1){b*=a.settings.mouseScrollEase/100;g+=b;o(g)}else b=0},30)}function X(){function b(f){T=f.pageX;if(!I){E();d()}}function d(){I=true;J=setInterval(function(){var f=(360*(h*(T-da)/(100*a.settings.mouseDragSpeed))+e-g)*(a.settings.mouseDragEase/100);if((f>=0?f:-f)>0.1){g+=f;l=Math.round(g*w/p);o(g)}else F()},30)}a.settings.mouseDrag=true;var e=0,h=a.settings.mouseDragReverse?1:
-1;c(document).bind("mousedown",function(f){if(k>i.offset().left&&k<i.offset().left+a.settings.width&&q>i.offset().top&&q<i.offset().top+a.settings.height){T=da=f.pageX;e=g;c(document).bind("mousemove",b)}});c(document).bind("mouseup",function(){c(document).unbind("mousemove",b)})}function Y(){a.settings.mouseWheel=true;var b=0,d=a.settings.mouseWheelReverse?-1:1;i.bind("mousewheel",function(e,h){e.preventDefault();if(!K){E();K=true;b=g;L=setInterval(function(){if(Math.abs(b-g)>0.5){g+=(b-g)*(a.settings.mouseWheelSpeed/
100);l=Math.round(g*w/p);o(g)}else F()},30)}b+=d*h*10})}function ia(){function b(){n=k-h.offset().left-ea;d()}function d(){if(n<0)n=0;else if(n>parseInt(h.css("width"))-parseInt(f.css("width")))n=parseInt(h.css("width"))-parseInt(f.css("width"));x&&f.css("left",n);U=n/(parseInt(h.css("width"))-parseInt(f.css("width")));if(!M){E();M=true;g%=360;V=setInterval(function(){if(Math.abs(R()-U)>0.0010){var W=(U-R())*(a.settings.scrollbarEase/100);g+=W*360;l=Math.round(g*w/p);o(g)}else M&&F()},30)}}var e=
c('<div class="scrollbar"></div>').appendTo(i),h=c('<div class="track"></div>').appendTo(e),f=c('<div class="thumb"></div>').appendTo(h),D=c('<div class="left"></div>').appendTo(e),r=c('<div class="right"></div>').appendTo(e),n=0,ea;e.css({top:aa,left:Q-parseInt(e.css("width"))/2});f.bind("mousedown",function(W){W.preventDefault();ea=k-f.offset().left;x=true;c(document).bind("mousemove",b)});c(document).bind("mouseup",function(){if(x){x=false;c(document).unbind("mousemove",b)}});D.bind("click",function(){n=
parseInt(f.css("left"))-a.settings.arrowScrollAmount;d()});r.bind("click",function(){n=parseInt(f.css("left"))+a.settings.arrowScrollAmount;d()})}function la(b){var d=i.find(".scrollbar").find(".track"),e=d.find(".thumb");e.css("left",b*(parseInt(d.css("width"))-parseInt(e.css("width"))))}function R(){var b=g%360/360;if(b<0)b+=1;return b}function E(){fa();if(!y){y=true;c.isFunction(a.settings.scrollStart)&&a.settings.scrollStart.call(this)}}function F(){fa();if(y){y=false;c.isFunction(a.settings.scrollComplete)&&
a.settings.scrollComplete.call(this)}a.settings.mouseScroll&&O();a.settings.autoScroll&&A()}function fa(){H&&clearInterval(H);if(J){I=false;clearInterval(J)}if(L){K=false;clearInterval(L)}if(V){M=false;clearInterval(V)}S&&clearInterval(S);t&&clearTimeout(t)}function ja(b){if(b=j[b].tooltip){G=true;var d=i.find(".tooltip");d.find("p").html(b);d.stop().animate({opacity:1},300);var e=-d.outerWidth()/2,h=0-d.outerHeight()-parseInt(d.css("marginBottom"));d.css({left:k-i.offset().left+e,top:q-i.offset().top+
h});c(document).bind("mousemove.tooltip",function(){d.css({left:k-i.offset().left+e,top:q-i.offset().top+h})});t&&a.settings.pauseAutoScrollIfTooltip&&clearTimeout(t)}}function ka(){if(G){G=false;var b=i.find(".tooltip");b.stop().animate({opacity:0},200,function(){c(document).unbind("mousemove.tooltip");b.css("left",-9999)});a.settings.autoScroll&&a.settings.pauseAutoScrollIfTooltip&&A()}}this.settings=c.extend({},c.fn.carousel.defaults,s);var i=c(z),a=this,l=0,j=[],Z=[],v=Math.PI,w=v/180,S,t,H,J,
L,V,B=a.settings.mouseScrollSpeed,k,q,T=0,da=0,I=false,K=false,x=false,M=false,U=0,g=0,Q=a.settings.width/2,P=a.settings.height/2,$=P-a.settings.verticalRadius,aa=P+a.settings.verticalRadius,p,u,y=false,G=false;(function(){i.addClass("carousel").css({width:a.settings.width,height:a.settings.height});if(a.settings.xmlSource){i.empty();c.ajax({type:"GET",url:a.settings.xmlSource,dataType:c.browser.msie?"text":"xml",success:function(b){var d;if(c.browser.msie){d=new ActiveXObject("Microsoft.XMLDOM");
d.async=false;d.loadXML(b)}else d=b;c(d).find("item").each(function(){for(var e={},h=0;h<c(this).children().length;h++){var f=c(this).children()[h];e[f.nodeName]=c(this).find(f.nodeName).text()}j.push(e)});m()}})}else{i.children().each(function(){for(var b={},d=0;d<c(this).children().length;d++){var e=c(this).children()[d];if(c(e).is("a")){b.path=c(e).find("img").attr("src");b.link=c(e).attr("href");if(c(e).attr("target"))b.linkTarget=c(e).attr("target")}else if(c(e).is("img"))b.path=c(e).attr("src");
else b[c(e).attr("class")]=c(e).html()}j.push(b)});i.empty();m()}})();this.startAutoScroll=A;this.stopAutoScroll=function(){a.settings.autoScroll=false;clearTimeout(t)};this.startMouseScroll=O;this.stopMouseScroll=function(){a.settings.mouseScroll=false;clearInterval(H)};this.startMouseDrag=X;this.stopMouseDrag=function(){I=a.settings.mouseDrag=false;clearInterval(J)};this.startMouseWheel=Y;this.stopMouseWheel=function(){K=a.settings.mouseWheel=false;clearInterval(L)};this.scrollToItem=C;this.scrollToNext=
ba;this.scrollToPrevious=ca;this.isScrolling=function(){return y}}c.fn.carousel=function(z){for(var s=[],m=0;m<this.length;m++)if(!this[m].carousel){this[m].carousel=new ga(this[m],z);s.push(this[m].carousel)}return s.length>1?s:s[0]};c.fn.carousel.defaults={xmlSource:null,width:500,height:300,itemWidth:100,itemHeight:100,horizontalRadius:250,verticalRadius:100,resize:true,maintainAspectRatio:true,crop:false,scaleRatio:0.5,mouseScroll:false,scrollOnClick:true,mouseDrag:false,scrollbar:false,arrowScrollAmount:50,
tooltip:true,mouseScrollEase:90,mouseDragEase:10,scrollbarEase:10,scrollSpeed:10,mouseDragSpeed:20,mouseScrollSpeed:10,mouseScrollSpeedHover:3,mouseWheel:false,mouseWheelSpeed:10,mouseScrollReverse:false,mouseDragReverse:false,mouseWheelReverse:false,autoScroll:false,autoScrollDirection:"next",autoScrollDelay:3E3,pauseAutoScrollIfTooltip:true,linkTarget:"_blank",itemSelect:null,itemClick:null,itemMouseOver:null,itemMouseOut:null,scrollStart:null,scrollComplete:null}})(jQuery);

(function($) {

var types = ['DOMMouseScroll', 'mousewheel'];

if ($.event.fixHooks) {
    for ( var i=types.length; i; ) {
        $.event.fixHooks[ types[--i] ] = $.event.mouseHooks;
    }
}

$.event.special.mousewheel = {
    setup: function() {
        if ( this.addEventListener ) {
            for ( var i=types.length; i; ) {
                this.addEventListener( types[--i], handler, false );
            }
        } else {
            this.onmousewheel = handler;
        }
    },
    
    teardown: function() {
        if ( this.removeEventListener ) {
            for ( var i=types.length; i; ) {
                this.removeEventListener( types[--i], handler, false );
            }
        } else {
            this.onmousewheel = null;
        }
    }
};

$.fn.extend({
    mousewheel: function(fn) {
        return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel");
    },
    
    unmousewheel: function(fn) {
        return this.unbind("mousewheel", fn);
    }
});


function handler(event) {
    var orgEvent = event || window.event, args = [].slice.call( arguments, 1 ), delta = 0, returnValue = true, deltaX = 0, deltaY = 0;
    event = $.event.fix(orgEvent);
    event.type = "mousewheel";
    
    // Old school scrollwheel delta
    if ( orgEvent.wheelDelta ) { delta = orgEvent.wheelDelta/120; }
    if ( orgEvent.detail     ) { delta = -orgEvent.detail/3; }
    
    // New school multidimensional scroll (touchpads) deltas
    deltaY = delta;
    
    // Gecko
    if ( orgEvent.axis !== undefined && orgEvent.axis === orgEvent.HORIZONTAL_AXIS ) {
        deltaY = 0;
        deltaX = -1*delta;
    }
    
    // Webkit
    if ( orgEvent.wheelDeltaY !== undefined ) { deltaY = orgEvent.wheelDeltaY/120; }
    if ( orgEvent.wheelDeltaX !== undefined ) { deltaX = -1*orgEvent.wheelDeltaX/120; }
    
    // Add event and delta to the front of the arguments
    args.unshift(event, delta, deltaX, deltaY);
    
    return ($.event.dispatch || $.event.handle).apply(this, args);
}

})(jQuery);
