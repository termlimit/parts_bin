/* globals token, start, end, dateRangeURL, everything, firstDate, moment, currentMonthName, $, previousMonthName, nextMonthName, applyLabel, cancelLabel, toLabel, customRangeLabel, fromLabel, */
$(function () {
    "use strict";
    $('.currencySelect').click(currencySelect);

    var ranges = {};
	ranges[previousMonthName] = [prevMonthStart, prevMonthEnd];
	ranges[currentMonthName] = [selectMonthStart, selectMonthEnd];
	ranges[nextMonthName] = [nextMonthStart, nextMonthEnd];
	ranges[nowMonthName] = [nowMonthStart, nowMonthEnd];
	ranges[everything] = [firstDate, moment()];
    $('#daterange').daterangepicker(
        {
            ranges: ranges,
            opens: 'left',
            locale: {
                applyLabel: applyLabel,
                cancelLabel: cancelLabel,
                fromLabel: fromLabel,
                toLabel: toLabel,
                weekLabel: 'W',
                customRangeLabel: customRangeLabel,
                daysOfWeek: moment.weekdaysMin(),
                monthNames: moment.monthsShort(),
                firstDay: moment.localeData()._week.dow,
				format: 'YYYY-MM-DD',
            },
            startDate: start,
            endDate: end
        },
        function (start, end, label) {
			var form = $('<form></form>');

			form.attr("method", "post");
			form.attr("action", dateRangeURL);

			var startField = $('<input></input>');
			startField.attr("type", "hidden");
			startField.attr("name", '_chartstart');
			startField.attr("value", encodeURIComponent(start.format('YYYY-MM-DD')));
			form.append(startField);

			var endField = $('<input></input>');
			endField.attr("type", "hidden");
			endField.attr("name", '_chartend');
			endField.attr("value", encodeURIComponent(end.format('YYYY-MM-DD')));
			form.append(endField); 

			var labelField = $('<input></input>');
			labelField.attr("type", "hidden");
			labelField.attr("name", '_chartlabel');
			labelField.attr("value", label);
			form.append(labelField);

			var tokenField = $('<input></input>');
			tokenField.attr("type", "hidden");
			tokenField.attr("name", '_token');
			tokenField.attr("value", token);
			form.append(tokenField);

			// The form needs to be a part of the document in
			// order for us to be able to submit it.
			$(document.body).append(form);
			form.submit();
		
			// send post.
            /*$.post(dateRangeURL, {
                start: start.format('YYYY-MM-DD'),
                end: end.format('YYYY-MM-DD'),
                label: label,
                _token: token
            }).success(function () {
                console.log('Succesfully sent new date range.');
				//alert('A ' + label + ' date range was chosen: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                window.location.reload(true);
            }).fail(function () {
                console.log('Could not send new date range.');
                alert('Could not change date range');

            });*/

            //alert('A date range was chosen: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        }
    );

});

function currencySelect(e) {
    "use strict";
    var target = $(e.target);
    var symbol = target.data('symbol');
    var code = target.data('code');
    var id = target.data('id');
    var fieldType = target.data('field');
    var menu = $('.' + fieldType + 'CurrencyDropdown');

    var symbolHolder = $('#' + fieldType + 'CurrentSymbol');
    symbolHolder.text(symbol);
    $('input[name="' + fieldType + '_currency_id"]').val(id);

    // close dropdown (hack hack)
    menu.click();


    return false;
}