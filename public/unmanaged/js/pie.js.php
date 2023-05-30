<?php
require("../1.php");
?>

function piece(pparent,pi,ptitle,pcolor,pvalue)
{
	var parent = pparent || null

	var value = (typeof pvalue == "undefined") ? 1 : pvalue

	var thisPiece = this
	var myPieceIndex

	var id = pi

	this.title = ptitle || ''
	this.color = pcolor || '#AABBCC'
	this.block = null

	this.setValue = function(nv)
	{
		value = Math.min(100,Math.max(nv,0))

		updateInput()
	}

	this.getValue = function() { return value }

	this.getWidth = function()
	{
		return Math.round(value * parent.getPieWidth() / 100)
	}

	var strInputClassName
	this.knowInputByClassName = function(cn)
	{
		strInputClassName = cn
	}

	this.setKeyPressHandler = function()
	{
		var input = jQuery(thisPiece).find(strInputClassName)
		var me = this
		input
		.keypress(function(e){
			if (e.keyCode==13)
			{
				var v = input.val()
				v = parseInt(v,10)
				if (isNaN(v))
					v = 0
				v = Math.abs(v)
				input.val(v)
				me.setWidthByValue(v)
				return false
			}
		})
		/*
		.focus(function(){
			jQuery(me.block).find('.current').show()
		})
		.blur(function(){
			jQuery(me.block).find('.current').hide()
		})
		*/
	}

	var jqInput
	var updateInput = function()
	{

		if (typeof jqInput == "undefined")
			jqInput = jQuery(parent.getContainer()).find(strInputClassName)

		jqInput.val(value)
	}

	this.setValueByWidth = function(nw)
	{
		value = Math.min(Math.max(0,nw * 100 / parent.getPieWidth()),100)
		updateInput()
	}

	this.set2WidthByValue = function(nv)
	{
		var block = this.block

		var delta = nv - value

		this.setValue(nv)

		var nw = Math.round(value * parent.getPieWidth() / 100)

		var prevBlock = block.previousSibling
		var pcl = (prevBlock != null && prevBlock.className=='piece') ? parseInt(prevBlock.style.left,10) : 0
		var pcw = (prevBlock != null && prevBlock.className=='piece') ? parseInt(prevBlock.style.width,10) : 0

		var nl = pcl+pcw

		block.style.width = nw + 'px'
		block.style.left = nl + 'px'

	}

	this.setValueByDelta = function(d)
	{

		var oldValue = value
		this.setValue(Number(value)+Number(d))
		var newValue = value

		jQuery(this.block).width(this.getWidth())
		var cl = parseInt(jQuery(this.block).left(),10)

		var newDelta = ((newValue - oldValue) * parent.getPieWidth()) / 100

		jQuery(this.block).left(cl - newDelta)
		//jQuery(this.block).animate({left:cl - newDelta},'normal')
	}

	this.setWidthByValue = function(newValue)
	{
		var myDiv = this.block


		var next = parent.getPieceByBase(this,1)
		if (next == null) // if it's a last piece
		{
			updateInput()
			return
		}


		var nextValue = next.getValue()

		// determine allowed newValue
		var upperBound = Number(value)+Number(nextValue)
		newValue = Math.min(Math.max(0,newValue),upperBound)
		next.setValueByDelta(Number(-newValue) + Number(value))
		this.setValue(newValue)

		var nw = Math.round(value * parent.getPieWidth() / 100)
		myDiv.style.width = nw + 'px'
		jQuery(myDiv).animate({width: nw},'normal')
	}

	this.setResizing = function(i)
	{
		var myPie = parent

		var myPiece = this
		var myPreviousPiece = myPie.getPiece(i-1)
		myPieceIndex = i

		var onePercent = parent.op

		this.block.title = this.title

		jQuery(this.block)
			.find('.resize')
			.mousedown(function(e){
				e.cancelBubble = true
				this.saveX = e.clientX
				/*
				if (typeof e.saveX == "undefined")
					this.saveX =
				*/
				var target = document.body
				var me = this

				//alert(myPiece.title)

				var myDiv = me.parentNode

				/*
				var myPreviousDiv = myDiv.previousSibling
				if (parseInt(myPreviousDiv.style.width,10)==0)
				{
					myPreviousDiv.e = e
					jQuery(myPreviousDiv).find('.resize').mousedown()
					return
				}
				*/

				var stopResize = function(e){
					e.cancelBubble = true
					/*
					myPie.hideAlt()
					*/
					jQuery(this)
						.unbind('mousemove')
						.unbind('mouseup')
						.css('cursor','default')
						.find('.piece')
						.css('cursor','default')
				}

				jQuery(target)
					.mouseup(stopResize)
					.mousemove(function(e){
						var delta = e.clientX - me.saveX

						// snapping to 1%
						delta = Math.round(delta / onePercent) * onePercent

						// resize previous
						var myPrevDiv = myDiv.previousSibling
						var pcw = parseInt(myPrevDiv.style.width,10)
						var pcl = parseInt(myPrevDiv.style.left,10)
						//pnw = Math.max(onePercent,pcw+delta)
						pnw = Math.max(0,pcw+delta)


						// resize myDiv
						var cw = parseInt(myDiv.style.width,10)
						var cl = parseInt(myDiv.style.left,10)

						var nl = Math.max(0,cl + delta)

						//if (nl >= cw + cl + onePercent || nl <= pcl - onePercent)
						if (nl >= cw + cl + onePercent || nl <= pcl - onePercent)
						{
							nl = cl
							pnw = pcw

							/*
							var prev = myDiv
							var pw = pnw
							var pl
							while(pw==0 && (prev = prev.previousSibling) != null)
							{
								pw = parseInt(prev.style.width,10)
								pl = parseInt(prev.style.left,10)
								prev.style.left = pl+delta+'px'
							}
							*/

						}

						var nw = cw + (cl - nl)

						myDiv.style.width = nw + 'px'
						myDiv.style.left = nl + 'px'
						myPrevDiv.style.width = pnw+'px'

						var x = e.clientX + document.body.scrollLeft
						var y = e.clientY + 10 + document.body.scrollTop

						myPreviousPiece.setValueByWidth(pnw)
						myPiece.setValueByWidth(nw)

						/*
						myPie.showAlt(myPreviousPiece.getValue()+'%'+' - '+myPiece.getValue()+'%',y,x)
						*/

						me.saveX = delta+me.saveX
					})
					.css('cursor','e-resize')
					.find('.piece')
					.css('cursor','e-resize')
			})
	}

	this.exportToXMLString = function()
	{
	    if (typeof jqInput == "undefined")
            jqInput = jQuery(parent.getContainer()).find(strInputClassName)

		var s = ''
		s = '<piece id="'+id+'" value="'+jqInput.val()+'" />'
		return s
	}
}

function pie()
{
	var myPie = this
	var container
	var pieces = new Array()

	var pieWidth

	this.getPiece = function(i) { return pieces[i] }
	this.getPieWidth = function() { return pieWidth }
	this.getContainer = function() { return container }

	this.forEachPiece = function(fn)
	{
		jQuery.each(pieces,fn)
	}

	this.getPieceByBase = function(base, delta)
	{
		var piece = null
		for (var i = 0; i < pieces.length; i++)
			if (pieces[i] == base)
			{
				piece = pieces[i+delta]
				if (typeof piece == "undefined")
					piece = null
				break
			}
		return piece
	}

	this.addPiece = function(title,id,color,value)
	{
		pieces.push(new piece(this,id,title,color,value))
	}

	/*
	var altBlock
	var createAlt = function()
	{
		if (jQuery(document.body).find('div#alt').size() > 0)
			return
		altBlock = jQuery(document.body)
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

	createAlt()
	*/

	this.drawIn = function(w)
	{
		// clear container and add some html
		container = '#'+w
		var where = container

		var jqPie = jQuery(where)
			.html('<div class="pie"></div>')
			.find('.pie')

		// disable selections
		var objectPie = jqPie.get(0)
		objectPie.onselectstart = objectPie.ondragstart = function(){return false}

		// get pie, piece widths and one percent
		pieWidth = jqPie.width()
		pieceHeight = jqPie.height()
		onePercent = pieWidth/100
		this.op = onePercent

		// add html for each piece
		jQuery.each(pieces,function(i){
			var inside = (i==0)?'':'<div class="resize"></div>' // if not first add resize handler
			jqPie.append('<div class="piece">'+inside+'<div class="current"></div></div>')
		})

		// add css for each piece
		var pleft = 0
		var piecesLength = pieces.length
		jqPie.find('.piece').each(function(i){
			var pwidth = pieces[i].getWidth()
			pieces[i].block = this
			jQuery(this)
				.css({
					zIndex: i,
					height: pieceHeight,
					top: '1px',
					left: pleft+'px',
					width: pwidth,
					background: pieces[i].color
				})
			pleft += pwidth
		})

		// find instance name
		var myName = ''
		for (var i in window)
			if (window[i] == this)
			{
				myName = i
				break
			}

		// add resize handler for each piece
		var caps = ''
		jQuery.each(pieces,function(i){

			this.setResizing(i)
			caps += '<div>'+this.title+' : <input class="caption'+i+'" type="text" value="'+this.getValue()+'" /> %</div>'
			this.knowInputByClassName('.caption'+i)

		})

		// add captions
		jQuery(where).append('<div class="captions">'+caps+'</div>')

		// add keypress handlers for inputs
		jQuery.each(pieces,function(i){
			this.setKeyPressHandler()
		})

		// add eq button
		jQuery(where).append('<input value="<?=_("Сделать равными")?>" type="submit" class="send" onclick="'+myName+'.makeEqual()" />')
		// add submit button
		jQuery(where).append('<input value="<?=_("Сохранить")?>" type="submit" class="eq" onclick="'+myName+'.send()" />')
	}


	this.sendTo = ''
	this.gotoAfter = ''

	this.send = function()
	{
		if (this.sendTo == '')
			return

		this.sendToURL(this.sendTo, this.gotoAfter)
	}

	this.makeEqual = function()
	{
		var w = Math.round(100/pieces.length)
		var all = 0
		jQuery.each(pieces,function(){
			all+=w
			if (all > 100)
				w = w - all + 100
			this.set2WidthByValue(w)
		})
	}

	this.exportToXMLString = function()
	{
		var s = ''
		for (var i = 0; i < pieces.length; i++)
			s += pieces[i].exportToXMLString()
		s = '<pie id="'+container.substr(1)+'">'+s+'</pie>'
		return s
	}

	this.sendToURL = function(url, gotoAfter) // gotoAfter is optional
	{
		var strXML = this.exportToXMLString()
		//alert(strXML)
		jQuery.post(
			url,
			{
				xml : strXML
			},
			(typeof gotoAfter != "undefined" && gotoAfter != '')?(function() { location.href = gotoAfter }):function(){}
		)
	}

}
