/**
 * Created by jeproQxT on 26/07/2015.
 */
(function($) {
    $.fn.JeproshopCategory = function (opts) {
        //setting default options
        var defaults = {};

        /** calling default options **/
        var options = $.extend(defaults, opts);

        $(document).on('click', '.lnk_more', function(e){
            e.preventDefault();
            $('#jform_category_description_short').hide();
            $('#category_description_full').show();
            $(this).hide();
        });
    };
})(jQuery);
