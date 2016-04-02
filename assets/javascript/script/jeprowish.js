/** jeproshop tools class **
 *
 * @param opts
 * @constructor
 */
var JeproshopWishList = function(opts){
    var defaults = {};
    this.options = jQuery.extend(opts, defaults);
    this.wishTools = new JeproshopTools({});
    this.ajaxXhr = this.wishTools.getXhr();
};

jQuery.extend(JeproshopWishList.prototype, {
    wishListAddProductCart : function(token, product_id, product_attribute_id, quantity){
        if(jQuery('#' + quantity).val() <= 0){ return false; }

        this.ajaxXhr.onreadystatechange = function(){
            if(this.ajaxXhr.readyState == 4){
                if(this.ajaxXhr.status == 200){
                    if(this.ajaxXhr.responseText){
                        alert(this.ajaxXhr.responseText);
                    }else{
                        var quantityWrapper = jQuery('#' + quantity);
                        quantityWrapper.val(quantityWrapper.val() - 1);
                    }
                }
            }
            return true;
        };

        var url = 'index.php?option=com_jeproshop&task=wishlist&product_id=' + product_id + '&product_attribute_id=' + product_attribute_id + '&' + token + '=1';
        this.ajaxXhr.open("GET", url, true);
        this.ajaxXhr.send(null);
    }
});
