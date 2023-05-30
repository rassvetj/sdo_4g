<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="initial-scale=1, width=device-width, user-scalable=no">

        <link href="/css/jquery-ui/jquery-ui-1.8.21.custom.css" rel="stylesheet">
        <link href="/css/jquery-ui/jquery.ui.selectmenu.css" rel="stylesheet">
        <style type="text/css">
            html, body {
                padding: 0;
                margin: 0;
            }

            .ajax-spinner {
                display: none;
            }
        </style>

        <?php
            echo $this->hmBootstrap()->getCss();
        ?>
        <script src="/js/lib/jquery/jquery-1.7.2.min.js"></script>
        <script src="/js/lib/jquery/jquery-ui-1.8.21.custom.min.js"></script>
        <script src="/js/lib/jquery/jquery.ui.selectmenu.min.js"></script>
        <?php
            echo $this->hmBootstrap()->getJS();
        ?>
        <script>
            hm.module.base.ui.ajax.AjaxSpinner.getInstance().disable();
        </script>
    </head>
    <body>
        <script>
            HM.create('hm.module.course.ui.schedule.urfu.UrfuSchedule', {
                renderTo: 'body',
                data: <?php echo json_encode($this->data) ?>,
                from: <?php echo json_encode($this->from) ?>,
                to:   <?php echo json_encode($this->to)   ?>,
                url:  <?php echo json_encode($this->url)  ?>,
                type:  <?php echo json_encode($this->type)  ?>
            });
            $(function() {
                $('#ZFDebug_debug').remove();
            });
        </script>
    </body>
</html>