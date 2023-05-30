<?php
require("../1.php");
?>

function ImagePreloader(images, callback)
{
  // store the call-back
  this.callback = callback//function(){setTimeout(callback,10)};
  
  // initialize internal state.
  this.nLoaded = 0;
  this.nProcessed = 0;
  this.aImages = new Array;
  
  // record the number of images.
  this.nImages = images.length;
  
  // for each image, call preload()
  for ( var i = 0; i < images.length; i++ )
    this.preload(images[i]);
}


ImagePreloader.prototype.preload = function(image)
{
  // create new Image object and add to array
  var oImage = new Image;
  this.aImages.push(oImage);
  
  // set up event handlers for the Image object
  oImage.onload = ImagePreloader.prototype.onload;
  oImage.onerror = ImagePreloader.prototype.onerror;
  oImage.onabort = ImagePreloader.prototype.onabort;
  
  // assign pointer back to this.
  oImage.oImagePreloader = this;
  oImage.bLoaded = false;
  
  // assign the .src property of the Image object
  status = 'Loading '+image
  oImage.src = image;
}


ImagePreloader.prototype.onComplete = function()
{
  this.nProcessed++;
  if ( this.nProcessed == this.nImages )
  {
    status = 'Done.'
    this.callback(this.aImages, this.nLoaded);
  }
}


ImagePreloader.prototype.onload = function()
{
  this.bLoaded = true;
  this.oImagePreloader.nLoaded++;
  this.oImagePreloader.onComplete();
}


ImagePreloader.prototype.onerror = function()
{
  this.bError = true;
  this.oImagePreloader.onComplete();
}

ImagePreloader.prototype.onabort = function()
{
  this.bAbort = true;
  this.oImagePreloader.onComplete();
}

$.loadXML = function(xmlUrl, callback)
{
    if (window.ActiveXObject)
    {
        xmlDoc = new ActiveXObject("Microsoft.XMLDOM")
        xmlDoc.async = false
        xmlDoc.validateOnParse="false";
        xmlDoc.setProperty("SelectionLanguage", "XPath");
        xmlDoc.load(xmlUrl)
        callback(xmlDoc)
    }
    else
        $.ajax({
            type: "GET",
            url: xmlUrl,
            dataType: "xml",
            success: callback
        })
}

$.xslt = function(xml, xslUrl, id, callback)
{
    // IE Implementation:
    if (window.ActiveXObject)
    {
        var xsl = new ActiveXObject("Microsoft.XMLDOM");
        xsl.async = false;
        xsl.validateOnParse="false";
        xsl.setProperty("SelectionLanguage", "XPath");
        xsl.load(xslUrl);
        var doc = xml.transformNode(xsl);
        $(id).append(doc);
        callback()
    }
    else
    {
        // Mozilla/Opera Implementation:
        var done = function(xsl)
        {
            var xsltProcessor = new XSLTProcessor();
            var xslDocument = (new DOMParser()).parseFromString(xsl.responseText, "text/xml");
            var xmlDocument = (new DOMParser()).parseFromString(xml.responseText, "text/xml");
            xsltProcessor.importStylesheet(xslDocument);
            var doc = xsltProcessor.transformToFragment(xmlDocument, document);
            $(id).append(doc);
            callback()
        }
        $.ajax({
            type: "GET",
            url: xslUrl,
            dataType: "xml",
            success: done
        })
    }
}

var treeview = function(strx)
{
    var blockId
    var xmlfile = strx
    
    this.checkers = true
    var tree = this
    
    var fields = new Array()
    
    this.addField = function(title, width, html)
    {
        var h = html || ''
        fields.push({
            title:title,
            width: width,
            html: h
        })
    }
    
    var getFields = function(atrs)
    {
        var r = ''
        for (var i = 0; i < fields.length; i++)
        {
            var fieldHtml = fields[i].html
            var inside = false // after "{" and before "}"
            var def = false // inside == true and after ","
            var s = ""
            var a = ""
            var d = ""
            var c=0
            while (c < fieldHtml.length)
            {
                var htmlchar = fieldHtml.substr(c,1)
                switch(htmlchar)
                {
                    case '{':
                        inside = true
                        def = false
                    break
                    case ',':
                        if (inside)
                            def = true
												else
														s+=htmlchar
                    break
                    case '}':
                        inside = def = false
                        var attributeValue = atrs[a]
                        s+=((typeof attributeValue != "undefined")?attributeValue:d)
                        a = d = ""
                    break
                    default:
                        if (inside)
                        {
                            if (def)
                                d += htmlchar
                            else
                                a += htmlchar
                        }
                        else
                            s+= htmlchar
                    break
                }
                c++
            }
            var x = (i == 0)?' class="first"':''
            r+= '<b'+x+' style="width:'+fields[i].width+'px" title="'+""+'">'+s+'</b>'
        }
        return r
    }
    
    var topopup = new Array()
    
    var transform = function(xfile0, writeblockId, callback)
    {
        var xfile = xfile0.replace(/&amp;/g,"&")
        var back = callback || function(){}
        $.loadXML(xfile,function(xml){
            $.xslt(xml,"js/treeview.xsl",writeblockId, function(){
                //var set = ($($(blockId).get(0).parentNode).find('> span span .value').get(0).className).indexOf('chess') >= 0
                $(writeblockId).find('div.item .a').each(function(){
                    var atrs = eval('[{'+($(this).html().substr(1))+'}]')
										atrs = atrs[0]
                    $(this)
                        .html(getFields(atrs))
                        .css('visibility','visible')
                        .css('cursor','default')
                        .click(function(e){
                            e.cancelBubble = true
                        })
                    var fOver = function()
                    {
                        $(this).css('background-color','#EEE')
                    }
                    var fOut = function()
                    {
                        $(this).css('background-color','transparent')
                    }
                    $(this.parentNode).mouseover(fOver).mouseout(fOut)
                })
                $(writeblockId).find('span.m, span.p').each(function(){
                    var me = this
                    me.expanded = me.className=='m'
                    var childrenSize = $(this.parentNode.parentNode).find('> div.item').size()
                    var inner = $(this.parentNode.parentNode).find('> div.inner')
                    if (childrenSize > 0)
                    {
                        var fClick = function()
                        {
                            $(this.parentNode.parentNode).find('> div.item').toggle()
                            me.className = (me.expanded)?'p':'m'
                            me.expanded = !me.expanded
                        }
                        var fClickInner = function()
                        {
                            me.className = 'load'
                            $(me)
                                .css('cursor','default')
                                .unclick()
                            var fDo = function()
                            {
                                transform(inner.html(),me.parentNode.parentNode,function(){
                                    inner.remove()
                                    me.className = 'm'
                                    me.expanded = true
                                    var c = $(me).find('input')
                                    if (c.size() > 0)
                                    {
                                        c = c.get(0).checked
                                        $(me.parentNode.parentNode).find('input').each(function(){
                                            this.checked = c
                                        })
                                    }
                                    $(me)
                                        .css('cursor','pointer')
                                        .click(fClick)
                                })
                            }
                            setTimeout(fDo,200)
                        }
                        
                        var f = (inner.size() > 0)?fClickInner:fClick
                        $(me)
                            .css('cursor','pointer')
                            .click(f)
                    }
                })
                $(writeblockId).find('input').each(function(){
                    var me = this
                    $(this)
                        .click(function(e){
                            e.cancelBubble = true
                            var c = me.checked
                            $(me.parentNode.parentNode.parentNode.parentNode).find('input').each(function(){
                                    this.checked = c
                            })
                        })
                })
                tree.checkWidth(writeblockId)
                back()
            })
        })
    }
    
    this.checkWidth = function(optContext)
    {
			var context = optContext || blockId
			var maxoffset = 0
			$(context)
				.find(".value")
				.each(function(){
						var valw = $(this).width()
						var aw = $(this).find(".a").width()
						var textw = $(this).find("strong").width()
						
						var inpw = $(this).find("input").width()
						var icow = $(this).find("img").width()
						
						if (inpw > 0)
								inpw+=5
						
						if (icow > 0)
								icow+=5
						
						maxoffset = Math.max(inpw+icow+textw-valw+aw,maxoffset)
				})
			$(blockId).width($(blockId).width() + maxoffset)
    }
    
    this.showIn = function(container)
    {
      var me = this
      self.doDelayedTreeShow = function()
      {
        me.showIn0(container)
      }
      $(document).ready(function(){
        setTimeout(self.doDelayedTreeShow,300)
      })
    }
    
    this.showIn0 = function(container)
    {
        blockId = "#"+container
        
        var h = '<table cellpadding="0" cellspacing="0" class="headers" width="100%"><tr><td class="first"><div>&nbsp;</div></td>'
        for (var i = 0; i < fields.length; i++)
        {
            var t = fields[i].title
            if (t=='')
                t = '&nbsp;'
            h += '<td width='+fields[i].width+'"><div style="width:'+fields[i].width+'px; overflow: hidden">'+t+'</div></td>'
        }
        h += '<tr></table>'
        
        var loading = '<div class="loading"><?=_("загрузка...")?></div>'
        
        $(blockId).append(h+loading+'<div class="treeview"></div>')
        
        
        var fDrawItems = function()
        {
            $(blockId).find('.loading').show()
            //$(blockId).find('.loading').slideDown("normal",function(){
                transform(xmlfile,blockId+' .treeview', function(){
                    $(blockId).find('.loading').remove()
                    $(blockId).find('.treeview').show()
                    tree.checkWidth()
                })
            //})
            
        }
        
        // preload images
        images = [
            'images/treeview/0.gif',
            'images/treeview/1.gif',
            'images/treeview/b.gif',
            'images/treeview/c.gif',
            'images/treeview/delete.gif',
            'images/treeview/edit.gif',
            'images/treeview/load.gif',
            'images/treeview/m.gif',
            'images/treeview/n.gif',
            'images/treeview/p.gif',
            'images/treeview/t.gif'
        ]
        var ip = new ImagePreloader(images, fDrawItems)
    }
    
}