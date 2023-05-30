$(document).ready(function(){
    $(document).on('click', '.cancel-upload > a', function(){
        var li = $(this).parent().parent();
        $.get($(this).attr('href'), function(data){
//#14979 //счетчик файлов не декрементировался при удалении
// Стандартный механизм удаления видимо был переделан - восстанавливаем работоспособность
            var id = li.parent().attr('id');    // вычисляем идентификатор 
            id = id.substring(0, id.length-5);  // главного контрола
            $('#'+id).data('fileupload')._trigger('destroy');
//
            li.remove();
        });
        return false;
    });
});