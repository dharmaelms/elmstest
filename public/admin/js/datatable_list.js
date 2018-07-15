jQuery.fn.extend({
    initializeDatatable : function (options) {
        var table = $(this).dataTable(options);
        var tableHeaderCheckbox = table.find("thead th input[type='checkbox']");

        table.customProperties = {};
        table.customProperties.selectedCheckboxes = [];

        simpleloader.init();
        table.on("processing.dt", function (event, settings, flag) {
            if(flag) {
                simpleloader.fadeIn();
            } else {
                simpleloader.fadeOut();
            }
        }).on("draw.dt", function (event, settings, flag) {
            var checkboxes = $(table).find("tbody tr td input[type=checkbox]");
            var checkboxCount = 0;
            if (table.customProperties.selectedCheckboxes.length > 0) {
                checkboxes.each(function () {
                    if (isValueExist(parseIntWithRadix10(this.value))) {
                        $(this).prop("checked", true);
                        ++checkboxCount;
                    }
                });
            }

            if (checkboxCount > 0 && checkboxCount === checkboxes.length) {
                tableHeaderCheckbox.prop({
                    checked : true
                });
            } else {
                tableHeaderCheckbox.prop({
                    checked : false
                });
            }

            checkboxes.change(function (event) {
                if ($(this).prop("checked")) {
                    pushCheckedValue($(this).val());
                } else {
                    tableHeaderCheckbox.prop({
                        checked : false
                    });

                    pullUncheckedValue($(this).val());
                }
            });

            $(".show-tooltip").tooltip();
        });
        
        $("#"+table.attr("id")+"_filter input").unbind().bind("keydown",
            function (e) { // Bind for enter key press
                // Search when user presses Enter
                if (e.keyCode == 13) {

                    tableHeaderCheckbox.prop({
                        checked : false
                    });

                    table.customProperties.selectedCheckboxes = [];
                    table.api().search(this.value).draw();
                }
            });
        
        tableHeaderCheckbox.change(function () {
            var checkboxes = table.find("tbody tr td input[type='checkbox']");

            if ($(this).prop("checked")) {
                var checkedValues = getCheckboxValues(checkboxes.prop({
                    checked : true
                }));

                for (var i = 0; i < checkedValues.length; ++i) {
                    if (table.customProperties.selectedCheckboxes.indexOf(checkedValues[i]) === -1) {
                        table.customProperties.selectedCheckboxes.push(checkedValues[i]);
                    }
                }
            } else {
                var uncheckedValues = getCheckboxValues(checkboxes.prop({
                    checked : false
                }));
                
                table.customProperties.selectedCheckboxes = uncheckedValues.filter(function (value) {
                    return !isValueExist(value);
                });
            }
        });

        var parseIntWithRadix10 = function (value) {
            return parseInt(value, 10);
        };

        var getCheckboxValues = function (checkboxes) {
            var values = [];

            checkboxes.each(function () {
                values.push(parseIntWithRadix10($(this).val()));
            });

            return values;
        };

        var pushCheckedValue = function (checked_value) {
            checked_value = parseIntWithRadix10(checked_value);
            if (!isValueExist(checked_value)) {
                table.customProperties.selectedCheckboxes.push(checked_value);
            }
        };

        var pullUncheckedValue = function (unchecked_value) {
            unchecked_value = parseIntWithRadix10(unchecked_value);
            if (isValueExist(unchecked_value)) {
                table.customProperties.selectedCheckboxes.splice(
                    table.customProperties.selectedCheckboxes.indexOf(unchecked_value),
                    1
                );
            }
        };

        var isValueExist = function (value) {
            return ($.inArray(value, table.customProperties.selectedCheckboxes) !== -1);
        };

        return table;
    }
});