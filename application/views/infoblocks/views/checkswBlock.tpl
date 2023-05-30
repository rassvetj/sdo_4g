<span id="EUDetectionContainer">
    <span style="color: red; font-weight: bold; font-size: 14px;">
        <?php echo _('Внимание! В браузере выключен JS. Для работы в системе необходимо включить его.'); ?>
    </span>
</span>
<script>
    (function() {
        var $cont = $('#EUDetectionContainer');
        
        $cont.html(
            hm.core.ClassManager.require('hm.core.HardwareDetect').get().checkWithFeedback()
        );
        
        if (window.clipboardData) {
            var $copyLink = $(document.createElement('a'));
            $copyLink.attr('href', '#');
            $copyLink.text(HM._('Скопировать в буфер обмена'));
            $copyLink.css({
                float: 'right'
            });
            $copyLink.on('click', function(e) {
                var hwDetect = hm.HardwareDetect.get(),
                    systemInfo = hwDetect.getSystemInfoForTable(hwDetect.getSystemInfo()),
                    data = [];
                
                $.each(systemInfo.info, function(i, item) {
                    data.push(item.appName + ':  ' + item.version);
                });
                
                if (clipboardData.setData('Text', data.join('\r\n'))) {
                    elsHelpers.alert(HM._('Текст успешно скопирован в буфер обмена!'));
                } else {
                    elsHelpers.alert(HM._('Не удалось скопировать текст в буфер обмена.'), HM._('Ошибка'));
                }
                
                e.preventDefault();
            });
            $cont.append($copyLink);
            $copyLink = null;
        }
        $cont = null;
    })();
</script>