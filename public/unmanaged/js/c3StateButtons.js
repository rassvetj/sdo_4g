function load3StateButtons() {
	var rootURL = self.rootURL || '..';
	(new Image()).src = rootURL+'/images/no-button.gif';
	$(window).mouseout(function(){
		if (window.currentButton) {
			window.currentButton.mouseDown = false;
		}
	})
	$('.tri-state')
		.each(function(){
			var $this = $(this);
			$this
				.attr('state1', !!$('> img:eq(0)', this).get(0) ? $('> img:eq(0)', this).get(0).src : rootURL+'/images/no-button.gif')
				.attr('state2', !!$('> img:eq(1)', this).get(0) ? $('> img:eq(1)', this).get(0).src : rootURL+'/images/no-button.gif')
				.attr('state3', !!$('> img:eq(2)', this).get(0) ? $('> img:eq(2)', this).get(0).src : rootURL+'/images/no-button.gif')
				.attr('state4', !!$('> img:eq(3)', this).get(0) ? $('> img:eq(3)', this).get(0).src : rootURL+'/images/no-button.gif')
		})
		.mouseover(function(event){
			var $this = $(this);
			if ($this.is('.disabled')) { return; }
			if (!!this.mouseDown) {
				$('> img:first', this).get(0).src = $this.attr('state3');
			} else {
				$('> img:first', this).get(0).src = $this.attr('state2');
			}
		})
		.mouseout(function(event){
			var $this = $(this);
			if ($this.is('.disabled')) { return; }
			$('> img:first', this).get(0).src = $this.attr('state1');
		})
		.mousedown(function(event){
			var $this = $(this);
			if ($this.is('.disabled')) { return false; }
			window.currentButton = this;
			this.mouseDown = true;
			$('> img:first', this).get(0).src = $this.attr('state3');
			return false;
		})
		.mouseup(function(event){
			var $this = $(this);
			if ($this.is('.disabled')) { return; }
			if (window.currentButton == this) {
				window.currentButton = null;
			}
			this.mouseDown = false;
			$('> img:first', this).get(0).src = $this.attr('state2');
		})
		.click(function(event){
			var $this = $(this);
			if ($this.is('.disabled')) { return; }
		})
	$('.tri-state.disabled').
		each(function(){
			var $this = $(this);
			$('> img:first', this).get(0).src = $this.attr('state4');
		});
	$(window).mouseup(function(){
		if (window.currentButton) {
			window.currentButton.mouseDown = false;
		}
	});
}
var oldBU = null;
if (!!window.onbeforeunload) {
	oldBU = window.onbeforeunload;
}
window.onbeforeunload = function() {
	if (!!arguments.callee.called) { return; }
	arguments.callee.called = true;
	$('.tri-state').unbind();
	$(window).unbind();
	window.currentButton = null;
	if (oldBU) { oldBU(); }
}
