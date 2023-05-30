<?php
class HM_View_Helper_Tooltip extends HM_View_Helper_Abstract
{

    public function tooltip($body)
    {
        $this->view->inlineScript()->offsetSetScript("tooltip_decorator", "
            yepnope({
                test: Modernizr.canvas,
                nope: ['/js/lib/jquery/excanvas.compiled.js'],
                complete: function () {
                    yepnope({
                        test: $.fn.bt,
                        nope: [
                            '/css/jquery-ui/jquery.ui.tooltip.css',
                            '/js/lib/jquery/jquery.hoverIntent.minified.js',
                            '/js/lib/jquery/jquery.ui.tooltip.js'
                        ],
                        complete: function () {
                            _.delay(function () {
                                jQuery(function ($) {
                                    $('.tooltip').bt({killTitle: false});
                                });
                            }, 100);
                        }
                    });
                }
            });
        ");

        $this->view->body = $body;
        return $this->view->render('tooltip.tpl');
    }
}