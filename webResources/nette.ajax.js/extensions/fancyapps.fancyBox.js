/**
 * fancyapps.fancyBox
 *
 * @version    0.1
 * @package    Nas- Nette aplication System
 *
 * @author Dusan Hudak <admin@dusan-hudak.com>
 * @copyright  Copyright (c) 2013 Dusan Hudak
 */

(function ($, undefined) {

    $.nette.ext({
        load: function () {
            $(".fancybox").fancybox({
                openEffect: 'elastic',
                closeEffect: 'elastic',
                helpers: {
                    title: { type: 'inside' }
                }
            });
        }
    });

})(jQuery);