function IsSummerTime(date)
{
    var march = new Date( Date.UTC( date.getUTCFullYear(), 2, 31 ) ); 
    var begin_st = new Date( Date.UTC( date.getUTCFullYear(), 2, 31 - march.getUTCDay() ) );

    var oct  = new Date( Date.UTC( date.getUTCFullYear(), 9, 31 ) );
    var end_st = new Date( Date.UTC( date.getUTCFullYear(), 9, 31 - oct.getUTCDay() ) );

    if ((date >= begin_st) && (date < end_st)) return 1;
    else return 0;
}

function summerMinus(endDate,startDate)
{
  var result = endDate.getTime() - startDate.getTime() // in milliseconds
  if (IsSummerTime(endDate))
    result+=60*60*1000
  if (IsSummerTime(startDate))
    result-=60*60*1000
  return result
}
function summerMinus2(endDate,startDate)
{
  var result = endDate.getTime() - startDate.getTime() // in milliseconds
  if (IsSummerTime(endDate))
    result-=60*60*1000
  if (IsSummerTime(startDate))
    result+=60*60*1000
  return result
}


function item()
{
	var name;
	var id;
	var startDate;
	var endDate;
	var selected = false;
	var block; // item
	var wrapblock; // item track
	
	var itemWidth;
	var itemLeft;
	var rowWidth;
	
	var parent;
	
	var color;
	
	var settings = new Array();
	var settingsAND = true;
	
	var linksFrom = new Array()
	var linksTo = new Array()
	
	var updateDates = function()
  // if bLeft then only itemLeft is updated, else only itemWidth
	{
		var d1 = parent.getStartDate().getTime()
		var d2 = parent.getEndDate().getTime()
		var d = summerMinus(parent.getEndDate(),parent.getStartDate())
		
		var dx1 = itemLeft * d / rowWidth + d1
		var dx2 = itemWidth * d / rowWidth + dx1
    
    /*
    var sign = function(x) { return x>0 }
    
    if (dx1 % parent.getPDay() != 0)
      dx1 -= 60*60*1000*sign(delta)
    
    if (dx2 % parent.getPDay() != 0)
      dx2 -= 60*60*1000*sign(delta)
    
    if (dx2 % parent.getPDay() != 0)
      alert(dx2 % parent.getPDay())
    */
    
		startDate.setTime(dx1)
		endDate.setTime(dx2)
	}
	
	this.setConditions = function(xconditions)
	{
		settingsAND = (xconditions.operation == 'AND')
		
		for (var i = 0; i < xconditions.conditions.length; i++)
		{
			var xid = xconditions.conditions[i].id
			var xlinkwith = xconditions.conditions[i].linkwith
			var t
			if (xid == '1')
			{
				var x = parent.getItemById(xlinkwith)
				if (x == null)
					continue
				var n = x.getName()
				if (n.length > 20)
					n = n.substring(0,20)+'...'
				t = 'Связать с "<strong>'+n+'</strong>" условием'
			}
			else
			{
				var x = parent.getConditionById(xid)
				if (x == null)
					continue
				t = x.text
			}
			settings.push({
				text: t,
				id: xid,
				value: xconditions.conditions[i].value,
				linkWith: xlinkwith
			})
		}
	}
	
	this.showSettings = function()
	{
		settingsLayer = parent.getSettingsWindow()
		var n = this.getName()
		if (n.length > 20)
			n = n.substring(0,20)+'...'
		
		this.importSettings()
		
		$(settingsLayer)
			.find('.wndCaption')
			.html('Условия для занятия "<strong>'+n+'</strong>"')
		
		var slTop = lastMouseY + 10 + document.body.scrollTop
		var slLeft = lastMouseX + 10 - parseInt(settingsLayer.clientWidth) / 2 + document.body.scrollLeft
    
		$(settingsLayer)
			.css({
				'visibility':'visible',
				'top':slTop,
				'left':slLeft
			})
		
		var me = this
		
		$('.ok',settingsLayer).unclick().click(function(){
			me.exportSettings()
			me.hideSettings()
			me.unselect()
		})
		
		$('.cancel',settingsLayer).unclick().click(function(){
			me.hideSettings()
			me.unselect()
		})
	}
	
	this.exportSettings = function()
	{
		settings = new Array()
		settingsLayer = parent.getSettingsWindow()
		
		$('.wct .condition',settingsLayer).each(function(){
			var ctext = $(this).find('.ctext').html()
			var i = $(this).find('input').get(0)
			var cvalue = i.value
			var cid = i.className.substr('condition'.length)
			var ctype = i.name.substr('condition'.length)
			
			settings.push({
				text:ctext,
				value:cvalue,
				id:cid,
				linkWith:ctype
			})
			
		})
		
		settingsAND = true 
		$(settingsLayer).find('input[name="optbool"]').each(function(){
			if ((this.type=='radio') && (this.value=='or') && (this.checked))
				settingsAND = false
		})
		
		/*
		var s = ''
		for (var i in settings)
			s+='\n\n'+i+' : '
				+'\ntext: '+settings[i].text
				+'\nvalue: '+settings[i].value
				+'\nid: '+settings[i].id
				+'\nlinkWith: '+settings[i].linkWith
		alert(s)
		*/
	}
	
	
	this.importSettings = function()
	{
		settingsLayer = parent.getSettingsWindow()
		var x = ''
		for (var i = 0; i < settings.length; i++)
			x += ''
				+'<div class="condition">'
					+'<div class="ctext">'+settings[i].text+'</div>'
					+'<input value="'+settings[i].value+'" class="condition'+settings[i].id+'" '+((settings[i].linkWith != '')?'name="condition'+settings[i].linkWith+'"':'')+' />'
				+'</div>'
		
		var bblock = '<table cellpadding="5" cellspacing="0">'
		var buttons = parent.buttons
		for (var i = 0; i < buttons.length; i++)
		  bblock+='<td>'+buttons[i].htmlBefore+this.getId()+buttons[i].htmlAfter+'</td>'
		bblock+='<td class="wndCaption"></td></table>'
		
		$(settingsLayer).find('.drag').html(bblock)
		
		$(settingsLayer).find('.wct').html(x)
		$(settingsLayer).find('input').each(function(){
			if (this.type=='radio')
				this.checked =
					(this.value == 'and')
						?
							settingsAND
						:
							!settingsAND
		})
	}
	
	
	this.hideSettings = function()
	{
		/*
		if (typeof blockSettings != "undefined")
			$(blockSettings).hide()
		*/
		settingsLayer = parent.getSettingsWindow()
		$(settingsLayer).css('visibility','hidden')
		//$('.wndSettings',settingsLayer).remove()
	}
	
	
	this.setLeft = function(newLeft)
	{
    var newL = parseInt(newLeft,10)
    var delta = newL - itemLeft
		itemLeft = newL
		updateDates()
	}
	
	
	this.setWidth = function(newWidth)
	{
    var newW = parseInt(newWidth, 10)
    var delta = newW - itemWidth
		itemWidth = newW
		updateDates()
		this.initDrag()
	}
	
	
	this.getColor = function()
	{
		return color
	}
	
	
	this.setColor = function(c)
	{
		color = c || '#000000'
	}
	
	
	this.isSelected = function()
	{
		return selected
	}
	
	
	this.unselect = function()
	{
		selected = false
		$(block).removeClass('selected')
		//this.hideSettings()
	}
	
	
	this.select = function()
	{
		selected = true
		$(block).addClass('selected')
		this.showSettings()
	}
	
	
	this.addLinkFrom = function(itemFromId)
	{
		// show link in block
		$('.itemLinksFrom',block).append('<span class="itemLinkFrom">'+itemFromId+'</span>')
		//$('.itemCaption',block).center()
		
		this.unselect()
		linksFrom.push(itemFromId)
	}
	
	
	this.addLinkTo = function(itemToId)
	{
		// show link in block
		$('.itemLinksTo',block).append('<span class="itemLinkTo">'+itemToId+'</span>')
		//$('.itemCaption',block).center()
		
		for (var i = 0; i< linksTo.length; i++)
			if (linksTo[i] == itemToId)
			{
				this.removeLinkTo(itemToId)
				return
			}
		
		this.unselect()
		linksTo.push(itemToId)
		
		parent.getItemById(itemToId).addLinkFrom(id)
	}
	
	
	this.removeLinkFrom = function(itemFromId)
	{
		this.unselect()
		
		// remove link from block
		$('.itemLinkFrom',block).each(function(){
			if($(this).html()==itemFromId)
				$(this).remove()
		})
		//$('.itemCaption',block).center()
		
		for (var i = 0; i< linksFrom.length; i++)
			if (linksFrom[i] == itemFromId)
			{
				linksFrom.splice(i,1)
				break
			}
	}
	
	
	this.removeLinkTo = function(itemToId)
	{
		this.unselect()
		
		// remove link from block
		$('.itemLinkTo',block).each(function(){
			if($(this).html()==itemToId)
				$(this).remove()
		})
		//$('.itemCaption',block).center()
		
		for (var i = 0; i< linksTo.length; i++)
			if (linksTo[i] == itemToId)
			{
				linksTo.splice(i,1)
				break
			}
		parent.getItemById(itemToId).removeLinkFrom(id)
	}
	
	
	var checkDates = function()
	{
		if (!(startDate && endDate))
			return
		
		startDate.setHours(0,0,0,0)
		endDate.setHours(0,0,0,0)
		
		if (endDate.getTime() - startDate.getTime() < 0)
		{
			var tmp = endDate
			endDate = startDate
			startDate = tmp
		}
	}
	
	
	this.setParent = function(obj)
	{
		parent = obj
	}
	
	
	this.setStartDate = function(date) // date example : 'Jun 06, 2006 06:06:06'
	{
		startDate = new Date(date)
		checkDates()
	}
	
	
	this.setEndDate = function(date)
	{
		endDate = new Date(date)
		checkDates()
	}
	
	
	this.setName = function(xname)
	{
		name = xname
	}
	
	
	this.getStartDate = function()
	{
		return startDate
	}
	
	
	this.getEndDate = function()
	{
		return endDate
	}
	
	
	this.getName = function()
	{
		return name
	}
	
	
	this.setId = function(xid)
	{
		id = xid
	}
	
	
	this.getId = function()
	{
		return id
	}
	
	
	this.draw = function(container, containerHeight)
	{
		wrapblock = container
		var d1 = parent.getStartDate().getTime()
		var d2 = parent.getEndDate().getTime()
		var dx1 = startDate.getTime()
		var dx2 = endDate.getTime()
		
		var d = summerMinus(parent.getEndDate(),parent.getStartDate())
		var dx2Mdx1 = summerMinus(endDate,startDate)
    var dx1Md1 = summerMinus(startDate,parent.getStartDate())
    
		rowWidth = parent.getPLength()
		itemWidth = rowWidth * (dx2Mdx1) / d
		itemLeft = rowWidth * (dx1Md1) / d
		
		var me = this
		var timeline = parent
		
		var before = ''//'<span style="color: black">&nbsp;&lt;</span>'
		var after = ''//'<span style="color: black">&gt;&nbsp;</span>'
		var itemBlock = $(container).append('<div class="itemBlock"><div class="itemCaption">'+before+'<span class="itemId">'+id+'</span>'+after+'</div></div>')
			.find('.itemBlock')
			.click(function(e){
				if (e.ctrlKey)
				{
					var add = true
					var id = me.getId()
					if (id == timeline.getSelectedItemId())
						return
					$(timeline.getContainer()+' .wct .condition1').each(function(){
						if (this.name == 'condition'+id)
							add = false
					})
					if (!add) return;
					
					var n = me.getName()
					if (n.length > 20)
						n = n.substring(0,20)+'...'
					
					var x = ''
					+'<div class="condition">'
						+'<div class="ctext">Связать с "<strong>'+n+'</strong>" условием</div>'
						+'<input class="condition1" name="condition'+id+'" />'
					+'</div>'
					$(timeline.getContainer()+' .wct').append(x)
				}
			})
			.css({
				background: me.getColor(),
				position:'absolute',
				display: 'block',
				top:0,
				left:itemLeft,
				width:itemWidth,
				height:containerHeight
			})
			.get(0)
		
		$(wrapblock)
			.dblclick(function(e)
			{
				lastMouseX = e.clientX
				lastMouseY = e.clientY
				parent.select(me.getId())
			})
			.css
			({
				'cursor':'help'
			})
		
		//itemBlock.title = name
		
		block = itemBlock
		var myitem = me
		
		$(itemBlock)
			.append('<div class="resizeHandler"><span></span></div><div class="resizeHandler2"><span></span></div>')
			.find('.resizeHandler, .resizeHandler2')
			.mousedown(function(e){
			
				e.cancelBubble = true
				this.saveX = e.clientX
				
				var target = document.body
				var me = this
				
				var stopResize = function(e){
					e.cancelBubble = true
					timeline.hideAlt()
					$(this)
						.unmousemove()
						.unmouseup()
						.css('cursor','default')
						.find('.itemBlock')
						.css('cursor','pointer')
				}
				
				$(target)
					.mouseup(stopResize)
					.css('cursor','e-resize')
					.find('.itemBlock')
					.css('cursor','e-resize')
				
				if (this.className == 'resizeHandler')
				{
					$(target)
						.mousemove(function(e){
							e.cancelBubble = true
							var delta = e.clientX - me.saveX
							
							delta = Math.round(delta / timeline.getPDay()) * timeline.getPDay() 
							
							var cw = parseInt(me.parentNode.style.width,10)
							var nw = Math.min(rowWidth-itemLeft, Math.max(cw+delta, timeline.getPDay()))
							me.parentNode.style.width = nw + 'px'
							
							var x = e.clientX + document.body.scrollLeft
							var y = e.clientY+10 + document.body.scrollTop
							timeline.showAlt(timeline.getDayWithPOffset(myitem.getEndDate(), nw - cw - timeline.getPDay()),y,x)
							myitem.setWidth(nw)
							me.saveX = delta + me.saveX
						})
				}
				else // resizeHandler2
				{
					$(target)
						.mousemove(function(e){
							e.cancelBubble = true
							var delta = e.clientX - me.saveX
							
							delta = Math.round(delta / timeline.getPDay()) * timeline.getPDay() 
							
							var cw = parseInt(me.parentNode.style.width,10)
							var cl = parseInt(me.parentNode.style.left,10)
							
							var nl = Math.max(0,cl + delta)
							
							if (nl == cw + cl)
								nl = cl
							
							var nw = cw + (cl - nl)
							
							me.parentNode.style.width = nw + 'px'
							me.parentNode.style.left = nl + 'px'
							
							var x = e.clientX + document.body.scrollLeft
							var y = e.clientY+10 + document.body.scrollTop
							timeline.showAlt(timeline.getDayWithPOffset(myitem.getStartDate(), nw - cw),y,x)
							myitem.setWidth(nw)
							myitem.setLeft(nl)
							me.saveX = delta + me.saveX
						})
				}
			})
		
		var itn = $('.itemCaption',container)
			.get(0)
			//.center()
		
		Drag.init(block,null,0,rowWidth-itemWidth,0,0,this,parent.getPDay())
		itn.onselectstart = itn.ondragstart = new Function('','return false')
	}
	
	
	this.initDrag = function()
	{
		Drag.update(block,0,rowWidth-parseInt(itemWidth),0,0)
	}
	
	
	this.exportToXMLString = function()
	{
		var s = ''
		
		for (var i = 0; i < settings.length; i++)
			s+='\n      <condition id="'+settings[i].id+'" value="'+settings[i].value+'"'+((settings[i].id == '1')?' linkwith="'+settings[i].linkWith+'"':'')+'/>'
		s = '\n    <conditions operation="'+((settingsAND)?'and':'or')+'">'+s+'\n    </conditions>'
		
    /*
		var sd = this.getStartDate().getTime() - parent.getStartDate().getTime()
		var ed = this.getEndDate().getTime() - parent.getStartDate().getTime()
    */
    var sd = summerMinus2(this.getStartDate(),parent.getStartDate())
		var ed = summerMinus2(this.getEndDate(),parent.getStartDate())
    
    
		s = '\n  <item id="'+this.getId()+'" startdate="'+sd+'" enddate="'+ed+'">'+s+'\n  </item>'
		return s
	}
}










function timeline()
{
	
	var conditions = new Array() // item := {id:strId, text:strText, icon: strURL}
	
	var startDate
	var endDate
	
	var items = new Array()
	
	var container
	
	var rowNameLength = 20 // number of letters to be shown in row name
	
	var msLength
	var pLength
	var msDay = 24 * 60 * 60 * 1000
	var pDay = 30
	var pDay100 = pDay // day length in pixels when scale is 100%
	var rowHeight = 20
	
	this.showOffsets = false
	//this.showDates = true
	
	var checkDates = function()
	{
		if (!(startDate && endDate))
			return
		
		startDate.setHours(0,0,0,0)
		endDate.setHours(0,0,0,0)
		
		if (endDate.getTime() - startDate.getTime() < 0)
		{
			var tmp = endDate
			endDate = startDate
			startDate = tmp
		}
	}
	
	var altBlock
	var createAlt = function()
	{
		altBlock = $(document.body)
			.append('<div id="alt">some alt text</div>')
			.find('div#alt')
	}
	
	this.showAlt = function(text,x,y)
	{
		altBlock
			.css({
				visibility:'visible',
				top:x,
				left:y
			})
			.html(text)
	}
	
	this.hideAlt = function()
	{
		altBlock
			.css('visibility','hidden')
	}
	
	
	this.getConditions = function()
	{
		return conditions
	}
	
	
	this.addCondition = function(strId, strText, strIcon)
	{
		var x = {
			id : strId,
			text : strText,
			icon : strIcon
		}
		conditions.push(x)
	}
	
	
	this.getSelectedItemId = function()
	{
		for (var i = 0; i < items.length; i++)
			if (items[i].isSelected())
				return items[i].getId()
	}
	
	
	this.select = function(itemId)
	{
		for (var i = 0; i < items.length; i++)
		{
			var item2 = items[i]
			if (item2.getId() == itemId)
			{
				/*
				if (item2.isSelected())
					item2.unselect()
				else
				*/
					item2.select()
			}
			else
				item2.unselect()
		}
	}
	
	
	this.getContainer = function()
	{
		return container
	}
	
	
	this.setStartDate = function(date)
	{
		startDate = new Date(date)
		checkDates()
	}
	
	
	this.setEndDate = function(date)
	{
		endDate = new Date(date)
		checkDates()
	}
	
	
	this.getStartDate = function()
	{
		return startDate
	}
	
	
	this.getEndDate = function()
	{
		return endDate
	}
	
	
	var generateConditionsBlock = function()
	{
		var radio = ''
		+'<table cellpadding="0" cellspacing="0">'
		+'<tr>'
			+'<td><input type="radio" name="optbool" value="and" checked="checked"></td>'
			+'<td>И</td>'
		+'</tr>'
		+'<tr>'
			+'<td><input type="radio" name="optbool" value="or"></td>'
			+'<td>ИЛИ</td>'
		+'</tr>'
		+'</table>'
		
		
		var all = ''
		+'<table cellpadding="5" cellspacing="0" class="conditionButtons">'
			+'<tr>'
		
		for (var i = 0; i < conditions.length; i++)
			all+=''
				+'<td><img name="condition'+conditions[i].id+'" border="0" src="'+conditions[i].icon+'" /></td>'
		
		all+='<td>'+radio+'</td>'
		all+='</tr></table>'
		
		
		return all
	}
	
	
	this.getConditionById = function(id)
	{
		for (var i=0; i < conditions.length; i++)
			if (conditions[i].id == id)
				return conditions[i]
		return null
	}
	
	
	this.getItemById = function(id)
	{
		for (var i = 0; i < items.length; i++)
			if (items[i].getId() == id)
				return items[i]
		return null
	}
	
	
	this.addItem = function(xname, xid, xcolor, xstart, xend, xconditions)
	{
		var x = new item()
		
		var xsd = new Date(xstart)
    xsd.setHours(0,0,0,0)
		var xed = new Date(xend)
    xed.setHours(0,0,0,0)
		
		var t1 = xsd.getTime() - startDate.getTime()
		var t2 = endDate.getTime() - xed.getTime()
		if (t1 < 0)
		{
			xed.setTime(Math.min(xed.getTime()-t1,endDate.getTime()))
			xsd = startDate
		}
		
		if (t2 < 0)
		{
			xsd.setTime(Math.max(xsd.getTime()+t2, startDate.getTime()))
			xed = endDate
		}
		
		x.setStartDate(xsd)
		x.setEndDate(xed)
    x.setName(xname)
		x.setParent(this)
		x.setId(xid)
		x.setColor(xcolor)
		if (typeof xconditions != "undefined")
			x.setConditions(xconditions)
		items.push(x)
		
		/*
		var s = ''
		for (var i = 0; i < items.length; s+=i+' '+items[i].getId()+'\n',i++);
		*/
	}
	
	
	var settingsWindow;
	this.getSettingsWindow = function()
	{
		return settingsWindow
	}
	
	var tm = this
	var createSettingsWindow = function()
	{
		var x = ''
		+ '<table cellspacing="0" class="wndSettings">'
			+ '<tr>'
				+ '<td colspan="2">'
					+ '<div class="drag">&nbsp;</div>'
				+ '</td>'
			+ '</tr>'
			+ '<tr>'
				+ '<td class="padding" colspan="2">'
					+ generateConditionsBlock()
					+ '<div class="wct"></div>'
				+ '</td>'
			+ '</tr>'
			+ '<tr>'
				+ '<td class="padding" align="left"><a class="ok" href="javascript:void(0)">сохранить</a></td>'
				+ '<td class="padding" align="right"><a class="cancel" href="javascript:void(0)">отмена</a></td>'
			+'</tr>'
		+'</table>'
		$(container).append(x)
		settingsWindow = $(container).find('.wndSettings')
			.css('visibility','hidden')
			.get(0)
		
		Drag.init($(settingsWindow).find('.drag').get(0),settingsWindow)
		
		// add behaviour to conditions :
		$(container+' .conditionButtons img')
			.click(function(){
				var c = tm.getContainer()
				var con = tm.getConditionById(this.name.substr('condition'.length))
				if ($(c+' .wct .condition'+con.id).get(0)!=null)
					return
				var x = ''
				+'<div class="condition">'
					+'<div class="ctext">'+con.text+'</div>'
					+'<input class="condition'+con.id+'">'
				+'</div>'
				$(c+' .wct').append(x)
			})
			.css('cursor','pointer')
	}
	
	
	this.getRowHeight = function()
	{
		return rowHeight
	}
	
	
	this.getPLength = function()
	{
		return pLength
	}
	
	
	this.getPDay = function()
	{
		return pDay
	}
	
	this.redrawTracksAndItems = function()
	{
		/*
		var h = $(container).html()
		var w = window.open()
		w.document.write('<textarea rows="100" cols="100">'+h+'</textarea>')
		*/
		drawTracksAndItems()
		
		var drawDays = pDay > 20
		
		drawCaptions(drawDays)
		
		createAlt()
	}
	
	var drawTracksAndItems = function()
	{
		
		// draw tracks for items
		var htmlTL = ''
		var tmp = '<td class="track"><div style="height: '+rowHeight+'px; width : '+pLength+'px"></div></td>' // track template
		for (var i = 0; i < items.length; i++)
		{
			var rowname = items[i].getName()
			
			if (rowname.length > rowNameLength)
				rowname = rowname.substring(0,rowNameLength)+'...'
			
			rowname = '<td align="right" valign="middle" class="rowName" nowrap="nowrap">'+rowname+'</td>'
			
			htmlTL+='<tr class="'+((i+1 == items.length)?'last':'')+'row">'+rowname+tmp+'</tr>'
		}
		
		htmlTL = '<div class="timeline"><table cellspacing="0" cellpadding="0">'+htmlTL+'</table></div>'
		
		$(container+' .timeline').remove()
		$(container+' .scaleChange').after(htmlTL)
		
		// draw items (containers + blocks)
		var pl = pLength 
		$(container+' .timeline .row, '+container+' .timeline .lastrow').each(function(i){
			//if (i == items.length) return
			var r = $('td',this).get(1)
			var rL = r.offsetLeft
			var rT = r.offsetTop
			
			var containerHeight = this.clientHeight
			$(container+' .timeline').append('<div class="rowRect'+i+'"><span></span></div>')
			var x = $(container+' .timeline .rowRect'+i).css({
				width: pl,
				height: containerHeight-2,
				display: 'block',
				position: 'absolute',
				top: rT+2,
				left: rL+1,
				zIndex: 20
			}).get(0)
			items[i].draw(x, containerHeight-3)
		})
	}
	
	var drawCaptions = function(boolDrawDays)
	{
		// important : startDate and endDate both have time set to 00:00:00!
		var daysInTimeline = msLength / msDay // integer! or is it?
		var capheight = rowHeight
		
		var r = $(container+' .timeline td').get(1)
		
		var t = r.offsetTop+1
		var l = r.offsetLeft+1
		
		var w = pDay
		var h = items.length*(rowHeight+1)
		
		var drawDays = (typeof boolDrawDays != "undefined") ? boolDrawDays : true
		
		if (drawDays & !tm.showOffsets)
			h += capheight
		
		var grid = $(container+' .timeline')
			.append('<div class="grid"></div>')
			.find('.grid')
			.css({
					position:'absolute',
					top:t,
					left:l,
					width:pLength,
					height:h
			})
		for (var i = 0; i <= daysInTimeline; i++)
		{
			grid
				.append(''
					+'<div class="grid'+i+'"'
					+'style="position: absolute; top:0; left:'+((i==0)?-1:(w*i))+'; width:'+w+'; height: '+h+'; border-left: 1px solid #DDD">'
					+'</div>')
		}
		
		var aDays = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб']
		
		var currentDay = startDate.getDay()
		
		var getDateAfter = function(daysDelta)
		{
			var d = new Date()
			d.setTime(startDate.getTime()+daysDelta*msDay)
			
			var year = parseInt(d.getFullYear(),10)
			var month = (parseInt(d.getMonth(),10) + 1)
			var date = (parseInt(d.getDate(),10))
			
			if (year < 10)
				year = '0'+year
			if (month < 10)
				month = '0'+month
			if (date < 10)
				date = '0'+date
			
			return date+'.'+month+'.'+year
		}
		
		if (drawDays)
		{
			// only for 100 and 75 %
			var t1 = h - capheight
			var t2 = t1 + capheight
			var t3 = t2 + capheight
			
			var t = new Date()
			t.setHours(0)
			t.setMinutes(0)
			t.setSeconds(0)
			var msToday = t.getTime()
			var dToday = -1
			if (msToday > startDate.getTime() && msToday < endDate.getTime())
				dToday = Math.round((msToday - startDate.getTime()) / msDay) // must be integer
			
			for (var i = 0; i < daysInTimeline; i++)
			{
				if (!tm.showOffsets)
				{
					var day = '<div class="day">'+aDays[currentDay]+'</div>'
					grid
						.append(''
							+'<div class="'+((i==dToday)?'today ':'')+((currentDay==0 || currentDay==6)?'dayOff ':'')+'days'+i+'"'
							+'style="position: absolute; top:'+t1+'; left:'+(w*i)+'; width:'+w+'; height: '+(capheight)+'; border-bottom: 1px solid #DDD">'
							+ day
							+'</div>')
				}
				
				if (tm.showOffsets)
				{
					grid
						.append(''
							+'<div class="moffset" '
							+'style="position: absolute; top:'+t2+'; left:'+w*i+'; width: '+w+'">'
							+ (i+1)
							+'</div>')
				}
				if (!tm.showOffsets && (currentDay == 1 && i+3 < daysInTimeline) )
				{
					grid
						.append(''
							+'<div class="mdate" '
							+'style="position: absolute; top:'+t2+'; left:'+(w*i)+'">'
							+ getDateAfter(i)
							+'</div>')
				}
				
				currentDay = (currentDay+1) % 7
			}
		}
		
	}
	
	this.showIn = function(element)
	{
		container = '#'+element
		var c = $(container).get(0)
		
		// forbid text selection
		c.onselectstart = c.ondragstart = new Function('','return false')
		
		// count length of timeline
		msLength = summerMinus(endDate, startDate)
    
		pLength = (msLength / msDay) * pDay
		var me = this
		var myName = ''
		for (var i in window)
			if (window[i] == this)
			{
				myName = i
				break
			}
		
		scaleSelect = ''
		+'<select class="scaleChange" onChange="'+myName+'.setScaleTo(this.value)">'
		  +'<option value="10">10%</option>'
		  +'<option value="25">25%</option>'
		  +'<option value="50">50%</option>'
		  +'<option value="75">75%</option>'
		  +'<option selected="yes" value="100">100%</option>'
		+'</select>'
		
		$(container).prepend(scaleSelect)
		
		drawTracksAndItems()
		drawCaptions()
		createSettingsWindow()
		createAlt()
	}
	
	this.exportToXMLString = function()
	{
		var s = ''
		for (var i = 0; i < items.length; i++)
			s += items[i].exportToXMLString()
		s = '<timeline id="'+container.substr(1)+'">'+s+'\n</timeline>'
		return s
	}
	
	this.setScaleTo = function(amount) // amount between 10 and 200
	{
		pDay = Math.round(pDay100 * parseInt(amount) / 100)
		pLength = msLength * pDay / msDay
		this.redrawTracksAndItems()
	}
	
	this.sendTo = function(url, gotoAfter) // gotoAfter is optional
	{
		var strXML = this.exportToXMLString()
		$.post(
			url,
			{
				xml : strXML
			},
			(typeof gotoAfter != "undefined")?(function() { location.href = gotoAfter }):function(){}
		)
	}
	
	
	var addLeadingZero = function(x)
	{
		return (parseInt(x,10) < 10)?'0'+x:x
	}
	
	this.getDayWithPOffset = function(dDate,pOffset)
	{
		if (this.showOffsets)
    {
			//return ((Math.abs(summerMinus(dDate,startDate)) / (1000*60*60*24))+1) // division by day
      return ((Math.abs(dDate.getTime() - startDate.getTime()) / (1000*60*60*24))+1) // division by day
    }
		else
		{
      var msOffset = pOffset * msDay / pDay
			var d = new Date()
			d.setTime(dDate.getTime()+msOffset)
			return addLeadingZero(d.getDate())+'.'+addLeadingZero(d.getMonth()+1)+'.'+addLeadingZero(d.getFullYear())
		}
	}
	
	this.buttons = new Array()
	this.addButton = function(htmlBefore, htmlAfter)
	{
		this.buttons.push({htmlBefore:htmlBefore,htmlAfter:htmlAfter})
	}
}
