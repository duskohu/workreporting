// Nette init
$(function () {
    $.nette.init();
});

/**
 * Items per page
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 * @copyright  Copyright (c) 2013 Dusan Hudak
 */
(function ($, undefined) {
    $.nette.ext({
        load: function () {
            $('select[data-items-per-page]').off('change');
            $('select[data-items-per-page]').on("change", function (e) {
                e.preventDefault();
                $(this).closest('form').submit();
            });
        }
    });
})(jQuery);

/**
 * Dependent Select Box
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 * @copyright  Copyright (c) 2013 Dusan Hudak
 */
(function ($, undefined) {
    $.nette.ext('data-dependent-select-box', {
        load: function () {
            $('select[data-dependent-select-box]').off('change');
            $('select[data-dependent-select-box]').on("change", function (e) {
                e.preventDefault();
                var data = $(this).data('dependentSelectBox');
                var urlObj = {};
                urlObj[data.paramYear] = $('#' + data.yearId).val();
                urlObj[data.paramMonth] = $('#' + data.monthId).val();
                urlObj[data.paramCallerElement] = data.callerElement;

                if (data.handle !== 'undefined') {
                    $.nette.ajax({
                        url: data.handle,
                        data: urlObj
                    });
                }
            });
        }
    });
})(jQuery);


/**
 * TWB tooltip
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 * @copyright  Copyright (c) 2013 Dusan Hudak
 */
(function ($, undefined) {
    $.nette.ext({
        load: function () {
            $("span[rel='tooltip']").tooltip({html: "true"});
        }
    });
})(jQuery);


/**
 * Refresh LogIn
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 * @copyright  Copyright (c) 2013 Dusan Hudak
 */
(function ($, undefined) {
    $.nette.ext({
        load: function () {
            var obj = $('[data-refresh-login]').data('refreshLogin');
            if (obj) {
                setTimeout(function () {
                    $.nette.ajax({
                        off: ['unique', 'snippets', 'spinner'],
                        url: obj.handle
                    });
                }, 1200000);
            }
        }
    });
})(jQuery);