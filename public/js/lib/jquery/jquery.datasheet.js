(function($){		

	$.fn.datasheet = function(options){

        var l10n = options.l10n

        function slideColumns(columns, value, totalVisibleColumns) {
            for (var i = 0; i < columns.length; ++i) {
                if (i < value || i >= (totalVisibleColumns + value))
                    columns[i].addClass('ui-helper-hidden');
                else
                    columns[i].removeClass('ui-helper-hidden');
            }
        }

        function onMarksheetLayoutUpdate(columns, columnWidth) {
            var totalVisibleColumns = 8
              , debouncedSlideColumns = _.debounce(slideColumns, 20); // calculate here

            $('#marksheet-slider').slider('destroy');
            if (totalVisibleColumns >= columns.length) {
                $('#marksheet-slider').closest('tr')
                    .addClass('ui-helper-hidden');
                $('#marksheet-slider').closest('td').attr('colspan', columns.length);
            } else {
                $('#marksheet-slider').closest('tr')
                    .removeClass('ui-helper-hidden');
                $('#marksheet-slider').closest('td')
                    .attr('colspan', totalVisibleColumns);
            }
            slideColumns(columns, 0, totalVisibleColumns);
            $('#marksheet-slider').css('width', 'auto').slider({
                min: 0,
                animate: true,
                step: (columns.length - totalVisibleColumns) / 100,
                max: columns.length - totalVisibleColumns,
                slide: function (event, ui) {
                    debouncedSlideColumns(columns, Math.round(ui.value), totalVisibleColumns);
                }
            });
        }

        $(function () {
            var columnWidth
              , columns = []
              , $allRows
              , $randomRow = $('thead').find('tr:eq(0)').find('td.lesson-cell');

            columnWidth = $randomRow.eq(0).outerWidth();

            for (var i = 0, length = $randomRow.length; i < length; ++i) {
                $allRows || ($allRows = $('thead, tbody').find('tr'));
                columns.push($allRows.find('td.lesson-cell:eq('+ i +')'));
            }

            onMarksheetLayoutUpdate(columns, columnWidth);
            $(window).add(document).bind('resize.marksheet-layout', _.debounce(function () {
                //log('triggering relayout');
            }, 300));
        });

        function initActionsButtons()
        {
            $datasheet = $(this);

            var buttons = {};

            buttons[l10n.ok] = function() {
                $(this).dialog('close');
            }

            $('#verticalSubmitButton, #horizontalSubmitButton').click(function (event) {

                $horizontalCheckboxes = $datasheet.find('.marksheet-colcheckbox input[type="checkbox"]');
                $verticalCheckboxes = $datasheet.find('.marksheet-rowcheckbox input[type="checkbox"]');

                var $this = $(this)
                , data = $this.is('#verticalSubmitButton') ? $verticalCheckboxes.serialize() : $horizontalCheckboxes.serialize()
                , url = $this.is('#verticalSubmitButton') ? $('#verticalMassAction').val() : $('#horizontalMassAction').val()
                , message
                , dialogId
                , $dialog
                , buttonsConfirm = {};

                alert(data);

                buttonsConfirm[l10n.yes] = function() {
                    $.ajax({
                        type: 'POST',
                        data: data,
                        url: url,
                        complete: function() {
                            document.location.reload();
                        }
                    });
                    $(this).dialog('close');
                }

                buttonsConfirm[l10n.no] = function () {
                    $( this ).dialog( "close" );
                }

                if (!data || url == 'none' || !url) {
                    dialogId = this.id + (data ? '-nourl' : '') + '-dialog';
                    $dialog = $('#'+dialogId);
                    if (!$dialog.length) {
                        message = $this.is('#verticalSubmitButton')
                            ? data
                                ? l10n.noVerticalActionSelected
                                : l10n.noVerticalSelected
                            : data
                                ? l10n.noHorizontalActionSelected
                                : l10n.noHorizontalSelected;
                        $dialog = $('<div id="'+ dialogId +'" title="'+ l10n.formError +'">'+ message +'</div>')
                            .hide()
                            .appendTo('body')
                            .dialog({
                                resizable: false,
                                autoOpen: false,
                                height: 140,
                                modal: true,
                                buttons: buttons
                            });
                    }
                    $dialog.dialog('open');
                    return;
                }

                if (!$("#confirm-dialog").length) {
                    $( '<div id="confirm-dialog" title="'+ l10n.confirm +'">'+ l10n.areUshure +'</div>' )
                        .hide()
                        .appendTo('body')
                        .dialog({
                            resizable: false,
                            autoOpen: false,
                            height: 140,
                            modal: true
                        });
                }
                $("#confirm-dialog")
                    .dialog('option', 'buttons', buttonsConfirm)
                    .dialog('open');

            });
        }


		return this.each(function(){
            var that = this;

            initActionsButtons();

            $datasheet = $(this);
            $datasheetScoreCells = $datasheet.find('tbody td.score-cell');
            $datasheetInputs = $datasheetScoreCells.find('input[type="text"]');
            $datasheetCheckboxes = $datasheetScoreCells.find('input[type="checkbox"]');
            $datasheetSelects = $datasheetScoreCells.find('select');

            if ($datasheetInputs.length > 0) {
                marks = $datasheetInputs.serializeArray();
                marks = _.reduce(marks, function (memo, item) {
                    memo[item.name] = item.value;
                    return memo;
                }, {});
            }

            if ($datasheetCheckboxes.length > 0) {
                marksCheckboxes = $datasheetCheckboxes.serializeArray();
                marksCheckboxes = _.reduce(marksCheckboxes, function (memo, item) {
                    memo[item.name] = item.value;
                    return memo;
                }, {});
            }

            if ($datasheetSelects.length > 0) {
                marksSelects = $datasheetSelects.serializeArray();
                marksSelects = _.reduce(marksSelects, function (memo, item) {
                    memo[item.name] = item.value;
                    return memo;
                }, {});
            }

            yepnope({
                test: Modernizr.input.pattern,
                nope: '/js/lib/polyfills/h5f.js',
                complete: function() {
                    $(function() {
                        window.H5F && H5F.setup($('#'+options.id+'-form').get(0), { invalidClass: "invalid" });
                        $(document).delegate('#'+options.id, 'keyup click change', _.debounce(
                            function(event) {
                                diff = null;

                                if ($datasheetInputs.length > 0) {
                                    $datasheetInputs.each(function(index) {
                                        var $this = $datasheetInputs.eq(index)
                                        , mark
                                        , isValid = !!this.checkValidity && this.checkValidity()
                                        if (isValid && marks[$this.attr('name')] != $this.val()) {

                                            diff || (diff = {})
                                            diff[$this.attr('name')] = $this.val();
                                        }
                                    });

                                    if (diff) {
                                        marks = _.extend(marks, diff);
                                    }

                                }

                                if ($datasheetCheckboxes.length > 0) {
                                    $datasheetCheckboxes.each(function(index) {
                                        var $this = $datasheetCheckboxes.eq(index)

                                        if (marksCheckboxes[$this.attr('name')] == undefined) {
                                            marksCheckboxes[$this.attr('name')] = 0;
                                        }

                                        if ($this.attr('checked') && (marksCheckboxes[$this.attr('name')] != $this.val())) {
                                            diff || (diff = {})
                                            diff[$this.attr('name')] = $this.val();
                                        } else if (!$this.attr('checked') && (marksCheckboxes[$this.attr('name')] == $this.val())) {
                                            diff || (diff = {})
                                            diff[$this.attr('name')] = 0;
                                        }
                                    });

                                    if (diff) {
                                        marksCheckboxes = _.extend(marksCheckboxes, diff);
                                    }

                                }

                                if ($datasheetSelects.length > 0) {
                                    $datasheetSelects.each(function(index) {
                                        var $this = $datasheetSelects.eq(index)

                                        if (marksSelects[$this.attr('name')] != $this.val()) {
                                            diff || (diff = {})
                                            diff[$this.attr('name')] = $this.val();
                                        }
                                    });

                                    if (diff) {
                                        marksSelects = _.extend(marksSelects, diff);
                                    }
                                }

                                if (diff) {
                                    $.post(options.url.save, diff, function(data) {
                                            if (undefined != options.callback) {
                                                options.callback()
                                            }
                                        }
                                    );
                                }
                            }
                        , 2000));
                    });
                }
            });

        });
    }
})(jQuery);