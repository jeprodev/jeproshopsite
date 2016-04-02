/**
 * Created by jeproQxT on 27/04/15.
 */

function JeproshopHoverWatcher(selector){
    this.hovering = false;
    var self = this;

    this.isHoveringOver = function(){
        return self.hovering;
    };

    jQuery(selector).hover(function(){
        self.hovering = true;
    }, function(){
        self.hovering = false;
    });
}

(function($){
    $.fn.Jeproshop = function(opts){
        var defaults = {
            customization_fields: null
        };

        /** calling default options **/
        var options = $.extend(defaults, opts);


    }


})(jQuery);