/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 */
(function ($, undefined) {

    $.nette.ext({
        load: function () {
            $('.date, .datetime-local').each(function (i, el) {
                el = $(el);
                var input = false;

                if (!el.parent().hasClass('date')) { // is div

                    if (el.find('input[type=date]').length > 0) {
                        input = el.find('input[type=date]');
                        input.get(0).type = 'text';
                        input.removeClass('date');
                    } else if (el.is("input")) {
                        input = el;
                        input.get(0).type = 'text';
                    }

                    if (input) {
                        el.datetimepicker({
                            startDate: el.attr('min'),
                            endDate: el.attr('max'),
                            weekStart: 1,
                            minView: el.is('.date') ? 'month' : 'hour',
                            format: el.is('.date') ? 'd. m. yyyy' : 'd. m. yyyy - hh:ii',
                            autoclose: true,
                            todayBtn: true,
                            language: 'sk'
                        });
                        input.attr('value') && el.datetimepicker('setValue');
                    }
                }
            });
        }
    });

})(jQuery);
