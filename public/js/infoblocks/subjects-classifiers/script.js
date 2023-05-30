$(function(){    
    var colorize = function(sColor,fColor,fPer) {  
        var aRGBStart = sColor.replace("#","").match(/.{2}/g); 
        var aRGBFinish = fColor.replace("#","").match(/.{2}/g);        
        var sPer = 1 - fPer;
        var R,G,B;
        R = Math.floor( ('0x'+aRGBStart[0]) * sPer + ('0x'+aRGBFinish[0]) * fPer);
        G = Math.floor( ('0x'+aRGBStart[1]) * sPer + ('0x'+aRGBFinish[1]) * fPer);
        B = Math.floor( ('0x'+aRGBStart[2]) * sPer + ('0x'+aRGBFinish[2]) * fPer);    
        return 'rgb('+R+ ',' + G + ',' + B +')'; 
    }
    var setColor = function(tag,sColor,fColor){
        var row = $(tag)     
        var cMax=0
        for(var i=row.length;i--;){
            /* ---------  temp  ---------- */
            var cVal = parseInt(row[i].innerHTML)            
            cVal>cMax?(cMax=cVal):false          
        } 
        for(var k=row.length;k--;){
            var cVal = parseInt(row[k].innerHTML) 
            row[k].style.color=colorize(sColor,fColor,(cVal/cMax))
        }
        var debug = 0        
    }
    var setColorBullet = function(){
        var color={
            violet:"#B2ABE3",
            green:"#89E59F",
            yellow:"#FFF3AB",
            red:"#FFABAB"
        }
        var cRowPVal=[]
        var bRow = $(".courseWName span[class*='cc']")
        var cMax=0
        var regexp = /(\d){1,3}/
        for(var i=bRow.length;i--;){
            var cClass = $(bRow[i]).attr("class")
            var percent = regexp.exec(cClass)
            cRowPVal[i] = percent[0]
            
            parseInt(percent[0])>cMax?(cMax=percent[0]):false
        }
        for(var i=cRowPVal.length;i--;){
            if(cMax==0) var cPer=0
            else var cPer = (cRowPVal[i])/cMax
            if((cPer>=0)&&(cPer<=.33)){
                bRow[i].style.backgroundColor=colorize(color.violet,color.green,(cPer/.33))
            }
            if((cPer>.33)&&(cPer<=.66)){
                bRow[i].style.backgroundColor=colorize(color.green,color.yellow,((cPer-.33)/.33))
            }
            if((cPer>.66)&&(cPer<=1)){
                bRow[i].style.backgroundColor=colorize(color.yellow,color.red,((cPer-.66)/.34))
            }
        }
    }
    setColor(".courseWName .right","#948585","#E01717")
    setColorBullet()
})