<?php $this->headScript()->appendFile($this->baseUrl("/js/lib/backbone-0.3.3.js")); ?>
<?php $this->headScript()->appendFile($this->baseUrl("/js/application/report/generator/construct.js")); ?>
<?php $this->headLink()->appendStylesheet( $this->baseUrl('css/application/report/construct.css') ); ?>
<?php $this->inlineScript()->captureStart(); ?>
$(function () {
    new ReportView({
        el: $("#report-app"),
        model: new Report({name:      <?php echo Zend_Json::encode($this->name); ?>,
                           report_id: <?php echo Zend_Json::encode($this->reportId); ?>}),
        previewUrl: <?php echo Zend_Json::encode($this->serverUrl('/report/generator/grid')); ?>,
        saveUrl: <?php echo Zend_Json::encode($this->serverUrl('/report/generator/save')); ?>,
        saveUrl: <?php echo Zend_Json::encode($this->serverUrl('/report/generator/save')); ?>,
        exitUrl: <?php echo Zend_Json::encode($this->serverUrl('/report/list')); ?>,
        domain: <?php echo Zend_Json::encode($this->domain); ?>,
        reportFields: <?php echo Zend_Json::encode($this->dataFields); ?>,
        sourceOfValues: $('#reportAllFields')
    });
});
<?php $this->inlineScript()->captureEnd(); ?>
<?php $aggregatorsTranslation = array(
    'max'   => _("Максимум"),
    'min'   => _("Минимум"),
    'avg'   => _("Среднее"),
    'sum'   => _("Сумма"),
    'count' => _("Количество"),
    'group_concat' => _("Групповое объединение"),
    'count_distinct' => _("Количество уникальных елементов"),
    'group_concat_distinct' => _("Групповое объединение уникальных елементов")
); ?>
<div style="display: none;"><select id="reportAllFields" class="report-constructor-all-fields">
    <option value="">&nbsp;</option>
    <?php foreach($this->fields['categories'] as $categoryName => $category):?>
        <optgroup label="<?php echo $this->escape($category['title'])?>">
        <?php foreach($category['fields'] as $fieldName => $field):?>
            <?php
                $functions = '';
                if ($field['function']) {
                    $functions = array();
                    foreach($field['function'] as $functionName => $functionAttributes) {
                        array_push($functions, array('name' => $functionName, 'title' => iconv("UTF-8", Zend_Registry::get('config')->charset, $functionAttributes['title'])));
                    }
                    $functions = Zend_Json::encode($functions);
                }
                $aggregations = '';
                if ($field['aggregation']) {
                    $aggregations = array();
                    foreach($field['aggregation'] as $aggregationName) {
                        array_push($aggregations, array(
                            'name' => $aggregationName,
                            'title' => ($aggregatorsTranslation[$aggregationName]
                                      ? iconv("UTF-8", Zend_Registry::get('config')->charset, $aggregatorsTranslation[$aggregationName])
                                      : $aggregationName
                                       )
                        ));
                    }
                    $aggregations = Zend_Json::encode($aggregations);
                }
            ?>
            <option
                data-aggregation="<?php echo $this->escape($aggregations);?>"
                data-functions="<?php echo $this->escape($functions);?>"
                data-type="<?php echo $this->escape($field['type']); ?>"
                value="<?php echo $this->domain?>.<?php echo $categoryName?>.<?php echo $fieldName?>"><?php echo $this->escape($field['title'])?></option>
        <?php endforeach;?>
        </optgroup>
    <?php endforeach;?>
</select></div>
<div id="report-app" class="report-constructor"><form>
    <div class="fields-section">
        <div class="fields-labels">
            <span class="field-name"><?php echo _("Поле в базе данных"); ?></span>
            <span class="field-alt-name"><?php echo _("Заголовок столбца"); ?></span>
            <span class="field-transformations"><?php echo _("Преобразования"); ?></span>
            <span class="field-calculations"><?php echo _("Вычисления"); ?></span>
            <span class="field-filter"><?php echo _("Фильтры"); ?></span>
        </div>
        <ol class="fields-list"></ol>
    </div>
    <div class="report-footer">
        <div class="buttons">
            <button class="preview" disabled><?php echo _("Предпросмотр"); ?></button>
            <button class="save" disabled><?php echo _("Сохранить"); ?></button>
        </div>
    </div>
    <div class="report-preview-box">
        <div class="report-preview-wrapper"><div class="report-preview"></div></div>
    </div>
</form></div>
<script type="text/template" id="field-row-template">
    <div class="field-row-wrapper<% if (m.isHidden) { %> is-hidden<% } %><% if (m.isInput) { %> is-input<% } %><% if (m.isFirst && m.isLast) { %> is-single<% } %><% if (m.value) { %> has-value<% } %>">
        <span class="field-cell drag-handler"></span>
        <span class="field-cell idx-container"><span class="idx-value"><%= idx %></span></span>
        <span class="field-cell select-field"></span>
        <span class="field-cell field-alt-name-input-container"><input class="field-alt-name-value" type="text" value="<%= m.displayName %>" autocomplete="off" placeholder="<?php echo _("название столбца"); ?>" ></span>
        <span class="function-fields"></span>
        <span class="field-cell field-with-btt field-hide"><span title="<% if (m.isHidden) { %><?php echo _("Поле является скрытым"); ?><% } else { %><?php echo _("Поле является видимым"); ?><% } %>" data-title="<?php echo $this->escape(Zend_Json::encode(array( 'hidden' => _("Поле является скрытым"), 'visible' => _("Поле является видимым") ))); ?>" class="field-btt field-hide-btt"></span></span>
        <span class="field-cell field-with-btt field-use-as-source"><span title="<% if (m.isInput) { %><?php echo _("Поле является входным параметром отчета"); ?><% } else { %><?php echo _("Поле не является входным параметром отчета"); ?><% } %>" data-title="<?php echo $this->escape(Zend_Json::encode(array( 'input' => _("Поле является входным параметром отчета"), 'notinput' => _("Поле не является входным параметром отчета") ))); ?>" class="field-btt field-use-as-source-btt"></span></span>
        <span class="field-cell field-with-btt field-add"><span title="<?php echo _("Добавить поле"); ?>" class="field-btt field-add-btt"></span></span>
        <span class="field-cell field-with-btt field-remove"><span title="<?php echo _("Удалить поле"); ?>" class="field-btt field-remove-btt"></span></span>
        <span class="field-cell field-filters">
            <span class="field-icon"></span>
        </span>
        <span class="field-cell drag-handler drag-handler-right"></span>
    </div>
</script>
<script type="text/template" id="field-row-functions-template">
    <span class="field-cell function-field">
        <span class="field-icon"></span>
        <% if (functions.length) { %>
        <select class="functions report-constructor-field-functions">
            <!-- <option value="">&nbsp;</option> -->
            <% _.each(functions, function (option) { %>
            <option value="<%= option.name %>"><%= option.title.toLowerCase() %></option>
            <% }); %>
        </select>
        <% } %>
    </span><span class="field-cell aggregate-field">
        <span class="field-icon"></span>
        <% if (aggregators.length) { %>
        <select class="aggregators report-constructor-field-aggregators">
            <!-- <option value="">&nbsp;</option> -->
            <% _.each(aggregators, function (option) { %>
            <option value="<%= option.name %>"><%= option.title.toLowerCase() %></option>
            <% }); %>
        </select>
        <% } %>
    </span>
</script>
<script type="text/template" id="filter-dialog">
    <div title="<?php echo _("Введите значение фильтра для поля"); ?> &mdash; <%= field %>"><%= content %></div>
</script>
<script type="text/template" id="filter-type-date">
    <div class="field-filters-value-date field-filters-value-container">
        <div>
            <strong><?php echo _("от"); ?></strong>
            <em class="date-from"><%= filter.from || '&#8722;&#8734;' %></em>
        </div>
        <div>
            <strong><?php echo _("до"); ?></strong>
            <em class="date-to"><%= filter.to || '+&#8734;' %></em>
        </div>
        <div class="field-filters-value-date-calendars calendars" id="date-<%= rowId %>-<%= valueId %>">
            <div class="calendar calendar-from" id="date-from-<%= rowId %>-<%= valueId %>" data-rangepos="from"></div>
            <div class="calendar calendar-to" id="date-to-<%= rowId %>-<%= valueId %>" data-rangepos="to"></div>
        </div>
    </div>
</script>
<script type="text/template" id="filter-type-integer">
    <div class="field-filters-value-container"
         data-validationerror="<?php echo _("Неверный формат целого числа"); ?>"><input type="text" value="<%= filter %>" placeholder="<?php echo _("целое число"); ?>"></div>
</script>
<script type="text/template" id="filter-type-double">
    <div class="field-filters-value-container"
         data-validationerror="<?php echo _("Неверный формат вещественного числа"); ?>"><input type="text" value="<%= filter %>" placeholder="<?php echo _("вецественное число"); ?>"></div>
</script>
<script type="text/template" id="filter-type-string">
    <div class="field-filters-value-container"><input type="text" value="<%= filter %>" placeholder="<?php echo _("маска строки"); ?>"></div>
</script>
<script type="text/template" id="filter-type-default">
    <div class="field-filters-value-container"><input type="text" value="<%= filter %>" placeholder="<?php echo _("тип неопределён"); ?>"></div>
</script>