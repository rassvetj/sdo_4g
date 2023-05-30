(function($){		

	$.fn.flashfile = function(options){
		return this.each(function(){
            var that = this;
            $(this).swfupload(options)
        		.bind('fileQueued', function(event, file){                
        			var listitem='<li id="'+file.id+'" >' 
        				//('+Math.round(file.size/1024)+' KB)<span class="progressvalue"></span>'
        				+'<div class="progressbar" ><img src="/images/cancel.png"><div class="progress" >'+
        				'<span>'+file.name+'</span></div></div>'+
        				'<p class="status" ><span class="wait">ожидание... </span></p>'+'</li>';
        				
        			$(that).find('#log').append(listitem);
        			$(that).find('li#'+file.id+' .progressbar img').bind('click', function(){ //Remove from queue on cancel click
        				var swfu = $.swfupload.getInstance(that);
        				swfu.cancelUpload(file.id);
        				$('li#'+file.id).hide();
        			});
        			// start the upload since it's queued
                    $(this).swfupload('setPostParams',
                    {
                        uniqid: $('#'+options.file_post_name+'-uniqid').val(),
                        sessid: options.session_id
                    });
        			$(this).swfupload('startUpload');
        		})
        		.bind('fileQueueError', function(event, file, errorCode, message){
                    if (file != null) {
        			    alert('Слишком большой размер или неверный тип файла '+file.name);
                    } else {
                        alert('Возможно лимит файлов исчерпан');                        
                    }
        		})
        		.bind('fileDialogComplete', function(event, numFilesSelected, numFilesQueued){
        			$(that).find('#queuestatus').text('Выбрано: '+numFilesSelected+' / Всего: '+numFilesQueued);
        			
        		})
        		.bind('uploadStart', function(event, file){
        			$(that).find('#log li#'+file.id).find('div.progressbar img').remove();
        	//	return false;
        			$(that).find('#log li#'+file.id).find('p.status').html('<span><img src="/images/spinner-small.gif"> загрузка...</span>');
        		//	return false;
        			$(that).find('#log li#'+file.id).find('span.progressvalue').text('0%');
        		//	return false;
        		})
        		.bind('uploadProgress', function(event, file, bytesLoaded){
        			//Show Progress
        			var percentage=Math.round((bytesLoaded/file.size)*100);
        			$(that).find('#log li#'+file.id).find('div.progress').css('width', percentage+'%');
        			$(that).find('#log li#'+file.id).find('span.progressvalue').text(percentage+'%');
        		})
        		.bind('uploadSuccess', function(event, file, serverData){
        			var item=$(that).find('#log li#'+file.id);
        			item.find('div.progress').css('width', '100%');
        			item.find('span.progressvalue').text('100%');
        			var pathtofile='<a href="/temp/'+file.name+'" target="_blank" >просмотр &raquo;</a>';
        			item.addClass('success').find('p.status').html('<span class="done"><img src="/images/done.png"></span> '/*+pathtofile*/);
        		})
        		.bind('uploadComplete', function(event, file){
        			// upload has completed, try the next one in the queue
        			$(that).swfupload('startUpload');
        		})
        });

    }
})(jQuery);