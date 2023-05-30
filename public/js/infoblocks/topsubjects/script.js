function topsubjectschartInited() {
    return true;
	data = data['topsubjects-chart'];
	if (typeof(data) != 'undefined') {
	    xml = $.parseXML(data);
	    $(xml).find('slice').each(function(){
			$('#topsubjects-placeholder-list').append('<li>' + $(this).attr('title') + ': <span class="topsubjects-number">' + $(this).text() + '</span></li>');
	    });

	}
}
