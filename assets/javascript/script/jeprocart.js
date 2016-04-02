/**
 * Created by jeproQxT on 26/04/15.
 */

function JeproshopHoverWatcher(selector){
    this.hovering = false;
    var self = this;

    this.isHoveringOver = function(){
        return self.hovering;
    };

    jQuery(selector).hover(
        function(){
            self.hovering = true;
        }, function(){
            self.hovering = false;
    });
}

(function($){
    $.fn.JeproshopAjaxCart = function(opts){
        var defaults = {
            view: '', task: '',
            static_token: '',
            generated_date:'',
            customization_fields: [],
            update_address_selection:'',
            error_thrown : '',
            required_fields : []
        };

        /** calling default options **/
        var options = $.extend(defaults, opts);
        var ajaxCart = this;
        var cartTools = new JeproshopTools({});
        var ajaxXhr = cartTools.getXhr();
        var baseDir = 'index.php?option=com_jeproshop';
        var ajaxWishList = new JeproshopWishList({});

        return ajaxCart.each(function(){ ajaxInitCartInitialize(); });

        function ajaxInitCartInitialize(){
            $('#jform_block_collapse').click(function(){ collapseCart(); });
            $('#jform_block_expand').click(function(){ expandCart(); });

            overRideButtonsInThePage();

            var cartQuantity = 0;
            var ajaxCartQuantity = $('.ajax_cart_quantity');
            var currentTimeStamp = parseInt(new Date().getTime()/1000);

            if(typeof(ajaxCartQuantity.html()) == 'undefined' || (typeof(options.generated_date) != 'undefined' && options.generated_date != null && (parseInt(options.generated_date) + 30) < currentTimeStamp)){
                refreshCart();
            }else{
                cartQuantity = parseInt(ajaxCartQuantity.html());
            }

            /** roll over cart **/
            var cartBlock = new JeproshopHoverWatcher('.cart_block');
            var shoppingCart = new JeproshopHoverWatcher('.shopping_cart');

            var shoppingCartWrapper = $('#jform_shopping_cart');
            $('a:first', shoppingCartWrapper).hover(
                function(){
                    $(this).css('border-radius', '3px 3px 0px 0px');
                    if(options.nb_total_products > 0 || cartQuantity > 0){
                        $('.cart_block').stop(true, true).slideDown(450);
                    }
                },
                function(){
                    var localShoppingCart = $('.shopping_cart');
                    $('a', localShoppingCart).css('border-radius', '3px');
                    setTimeout(function(){
                        if(!shoppingCart.isHoveringOver() && !cartBlock.isHoveringOver()){
                            $('.cart_block').stop(true, true).slideUp(450);
                        }
                    }, 200);
                }
            );

            var shopCart = $('#shopping_cart');
            $('#cart_block').hover(function(){
                $('a', shopCart).css('border-radius', '3px 3px 0px 0px');
            }, function(){
                $('a', shopCart).css('border-radius', '3px');
                setTimeout(function(){
                    if(!shoppingCart.isHoveringOver()){
                        $('#cart_block').stop(true, true).slideUp(450);
                    }
                }, 200);
            });

            $('.delete_voucher').live('click', function(){
                var type = "POST";
                var async = true;
                var cache = false;
                var content = null;
                var url = jQuery(this).attr('href') + '&rand=' + new Date().getTime();
                launchRequest(type, url, content, null );
                /*$.ajax({
                    type: 'POST', headers:{ "cache-control": "no-cache" },

                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        alert("TECHNICAL ERROR: \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
                    }
                }); */

                $(this).parent().parent().remove();
                if(options.view == 'order' && (options.task == '' || options.task == 'opc')){
                    if(typeof(options.update_address_selection) != 'undefined'){
                        updateAddressSelection();
                    }else{ location.reload(); }
                }
                return false;
            });

            var cartNavigation = $('#cart_navigation');
            $('input', cartNavigation).click(function(){
                $(this).attr('disabled', true).removeClass('exclusive').addClass('exclusive_disabled');
                $(this).closest('form').get(0).submit();
            });
        }

        function overRideButtonsInThePage(){
            //for every add buttons...
            $('.ajax_add_to_cart_button').unbind('click').click(function(){
                /*var productId = $(this).attr('rel').replace('nofollow');
                if($(this).attr('disabled') != 'disabled'){ addProductToCart(productId, null, false, this); }
                return false; */
            });

            //for product page 'add' button...
            var addToCart = $('#jform_add_to_cart');
            $('button', addToCart).unbind('click').click(function(evt){
                evt.stopPropagation();
                addProductToCart( $('#jform_product_page_product_id').val(), $('#jform_combination_id').val(), true, null, $('#jform_quantity_wanted').val(), null);
                return false;
            });

            //for 'delete' buttons in the cart block...
            var cartBlockList = $('#jform_cart_block_list');
            $('.ajax_cart_block_remove_link', cartBlockList).unbind('click').click(function(){
                var customizationId = 0;
                var productId = 0;
                var productAttributeId = 0;
                var customizableProductDiv = $($(this).parent().parent()).find("div[id^=jform_delete_customizable_product_]");

                if(customizableProductDiv && $(customizableProductDiv).length){
                    $(customizableProductDiv).each(function(){
                        var ids = $(this).attr('id').split('_');
                        if(typeof(ids[1]) != 'undefined'){
                            customizationId = parseInt(ids[1]);
                            productId = parseInt(ids[2]);
                            if(typeof(ids[3]) != 'undefined'){
                                productAttributeId = parseInt(ids[3]);
                            }
                            return false;
                        }
                    });
                }

                //Common product management
                if(!customizationId){
                    //retrieve product id and combination id from the display
                    var firstCut = $(this).parent().parent().attr('id').replace('jform_cart_block_product_', '');
                    firstCut = firstCut.replace('jform_delete_customizable_product_', '');
                    var ids = firstCut.split('_');
                    productId = parseInt(ids[0]);
                    if(typeof(ids[1]) != 'undefined'){
                        productAttributeId = parseInt(ids[1]);
                    }
                }

                var addressDeliveryId = $(this).parent().parent().attr('id').match(/.*_\d+_\d+(\d+)/)[1];

                //Removing product from the cart
                removeProductFromCart(productId, productAttributeId, customizationId, addressDeliveryId);
                return false;
            });
        }

        function expandCart(){
            if($('#jform_cart_block_list').hasClass('collapsed')){
                $('#jform_cart_summary').slideUp(200, function(){
                    $(this).addClass('collapsed').removeClass('expanded');
                    $('#jform_cart_block_list').slideDown({
                        duration: 450, complete: function(){ $(this).addClass('expanded').removeClass('collapsed'); }
                    });
                });

                //toggle the button expand/collapse button
                $('#jform_block_cart_expand').fadeOut('slow', function(){ $('#jform_block_cart_collapse').fadeIn('fast'); });

                // save the expand status in the user cookie
                /*$.ajax({
                    type: 'POST', headers:{"cache-control": "no-cache" },
                    url: options.baseDir,
                    async: true, cache: false, data: 'ajax_block_cart_display=expand'
                }); */
            }
        }

        function refreshCart(){
            /*$.ajax({
                type: 'POST', headers: {"cache-control": "no-cache"},
                url: options.baseDir + '&rand=' + new Date().getTime(),
                async: true, cache: false, dataType: "json",
                data: '&view=cart&use_ajax=true&token=' + options.static_token,
                success:function(jsonData){ updateCart(jsonData); },
                error: function(XMLHttpRequest, textStatus, errorThrown){
                    //TODO alert("TECHNICAL ERROR: \n\nDetails:\nError thrown: " + XMLHttpRequest + "\nText status: " + textStatus);
                }
            }); */
        }

        // try to collapse the cart
        function collapseCart(){
            if ($('#jform_cart_block_list').hasClass('expanded')){
                $('#jform_cart_block_list').slideUp('slow', function(){
                    $(this).addClass('collapsed').removeClass('expanded');
                    $('#jform_cart_block_summary').slideDown(450, function(){
                        $(this).addClass('expanded').removeClass('collapsed');
                    });
                });
                $('#jform_block_cart_collapse').fadeOut('slow', function(){
                    $('#jform_block_cart_expand').fadeIn('fast');
                });

                // save the expand status in the user cookie
                /*$.ajax({
                    type: 'POST', headers: { "cache-control": "no-cache" },
                    url: options.baseDir + 'modules/blockcart/blockcart-set-collapse.php' + '?rand=' + new Date().getTime(),
                    async: true, cache: false,
                    data: 'ajax_block_cart_display=collapse' + '&rand=' + new Date().getTime()
                }); */
            }
        }

        function updateCartInformation(jsonData, addedFromProductPage){
            updateCart(jsonData);

            //reactive the button when adding has finished
            if (addedFromProductPage)
                $('#jform_add_to_cart input').removeAttr('disabled').addClass('exclusive').removeClass('exclusive_disabled');
            else
                $('.ajax_add_to_cart_button').removeAttr('disabled');
        }

        function addProductToCart(productId, combinationId, addedFromProductPage, callerElement, quantity, wishList){
            if (addedFromProductPage && !checkCustomizations()) {
                alert(options.required_fields);
                return ;
            }

            emptyCustomizations();
            //disabled the button when adding to not double add if user double click
            if (addedFromProductPage){
                var addToCartButton = $('#jform_add_to_cart');
                $('input', addToCartButton).attr('disabled', true).removeClass('exclusive').addClass('exclusive_disabled');
                $('.filled').removeClass('filled');
            }else{
                $(callerElement).attr('disabled', true);
            }

            if ($('#cart_block_list').hasClass('collapsed')){ this.expand(); }
            //send the ajax request to the server
            var url = 'index.php?option=com_jeproshop&view=cart&rand=' + new  Date().getTime() + '&format=html';
            var content = 'task=product&product_id=' + productId + '&use_ajax=true&quantity=' + ((quantity && ( quantity != null)) ? quantity : 1) + '&' + options.static_token + '=1' ;
            content += ((parseInt(combinationId) && combinationId != null) ? '&product_attribute_id=' + parseInt(combinationId) : '');
            ajaxXhr.onreadystatechange = function(){
                if(ajaxXhr.readyState == 4){
                    if(ajaxXhr.status == 200) {
                        // add appliance to wish list module
                        if(wishList){ ajaxWishList.wishListAddProductCart(wishList[0], productId, combinationId, wishList[1]); }

                        // add the picture to the cart
                        var elementImages = $(callerElement).parent().parent().find('a.product_image img,a.product_img_link img');
                        if (!elementImages.length){ elementImages = $('#jform_big_picture'); }
                        var picture = elementImages.clone();
                        var pictureOffsetOriginal = elementImages.offset();
                        pictureOffsetOriginal.right = $(window).innerWidth() - pictureOffsetOriginal.left - elementImages.width(); alert(ajaxXhr.responseText);

                        if(picture.length){
                            picture.css({
                                position: 'absolute',
                                top: pictureOffsetOriginal.top,
                                right: pictureOffsetOriginal.right
                            });
                        }

                        var pictureOffset = picture.offset();
                        var cartBlock = $('#jform_cart_block');
                        if (!cartBlock[0] || !cartBlock.offset().top || !cartBlock.offset().left)
                            cartBlock = $('#jform_shopping_cart');
                        var cartBlockOffset = cartBlock.offset();
                        cartBlock.css("right", ($(window).innerWidth() - cartBlock.css("left") - cartBlock.width()));

                        // Check if the block cart is activated for the animation
                        if (cartBlockOffset != undefined && picture.length) {
                            picture.appendTo('body');
                            picture.css({
                                position: 'absolute',
                                top: pictureOffsetOriginal.top,
                                right: pictureOffsetOriginal.right,
                                zIndex: 4242
                            }).animate({
                                width: elementImages.attr('width')*0.66,
                                height: elementImages.attr('height')*0.66,
                                opacity: 0.2,
                                top: cartBlockOffset.top + 30,
                                right: cartBlockOffset.right + 15
                            }, 1000).fadeOut(100, function() {
                                updateCartInformation(ajaxXhr.responseText, addedFromProductPage);
                                $(this).remove();
                            });
                        }else{
                            updateCartInformation(ajaxXhr.responseText, addedFromProductPage);
                        }
                    }else{
                        alert("Impossible to add the product to the cart.\n\ntextStatus: '" + ajaxXhr.status + "'\nerrorThrown: '" + options.error_thrown + "'\nresponseText:\n" + ajaxXhr.responseText);
                        //reactive the button when adding has finished
                        if (addedFromProductPage){
                            $('input', addToCartButton).removeAttr('disabled').addClass('exclusive').removeClass('exclusive_disabled');
                        }else{
                            $(callerElement).removeAttr('disabled');
                        }
                    }
                }
            };
            ajaxXhr.open("POST", url, true);
            ajaxXhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            ajaxXhr.send(content);
            /*$.ajax({
                type: 'POST', headers: { "cache-control": "no-cache" },
                url: options.baseDir + '&rand=' + new Date().getTime(),
                async: true, cache: false, dataType : "json",
                data: 'view=cart&task=add&use_ajax=true&quantity=' + + ( (parseInt(combinationId) && combinationId != null) ? '&product_attribute_id=' + parseInt(combinationId): ''),
                success: function(jsonData,textStatus,jqXHR){


                },
                error: function(XMLHttpRequest, textStatus, errorThrown){

                }
            }); */
        }

        function removeProductFromCart(productId, combinationId, customizationId, addressDeliveryId){
            //send the ajax request to the server
            /*$.ajax({
                type: 'POST', headers: { "cache-control": "no-cache" },
                url: options.baseDir + '&rand=' + new Date().getTime(),
                async: true, cache: false,
                dataType : "json",
                data: 'view=cart&task=delete&product_id=' + productId + '&product_attribute_id=' + ((combinationId != null && parseInt(combinationId)) ? combinationId : '') + ((customizationId && customizationId != null) ? '&customization_id=' + customizationId : '') + '&address_delivery_id=' + addressDeliveryId + '&token=' + options.static_token + '&use_ajax=true',
                success: function(jsonData)	{
                    updateCart(jsonData);

                    if ($('body').attr('id') == 'order' || $('body').attr('id') == 'order-opc')
                        deleteProductFromSummary(productId + '_' + combinationId + '_' + customizationId+'_' + addressDeliveryId);
                },
                error: function() {alert('ERROR: unable to delete the product');}
            }); */
        }

        //hide the products displayed in the page but no more in the json data
        function hideOldProducts(jsonData){
            //delete an eventually removed product of the displayed cart (only if cart is not empty!)
            if ($('#cart_block_list dl.products').length > 0) {
                var removedProductId = null;
                var removedProductData = null;
                var removedProductDomId = null;
                //look for a product to delete...
                $('#jform_cart_block_list dl.products dt').each(function(){
                    //retrieve idProduct and idCombination from the displayed product in the block cart
                    var domProductId = $(this).attr('id');
                    var firstCut =  domProductId.replace('cart_block_product_', '');
                    var ids = firstCut.split('_');

                    //try to know if the current product is still in the new list
                    var stayInTheCart = false;
                    for (aProduct in jsonData.products){
                        //we've called the variable aProduct because IE6 bug if this variable is called product
                        //if product has attributes
                        if (jsonData.products[aProduct]['product_id'] == ids[0] && (!ids[1] || jsonData.products[aProduct]['combination_id'] == ids[1])){
                            stayInTheCart = true;
                            // update the product customization display (when the product is still in the cart)
                            hideOldProductCustomizations(jsonData.products[aProduct], domProductId);
                        }
                    }
                    //remove product if it's no more in the cart
                    if (!stayInTheCart) {
                        removedProductId = $(this).attr('id');
                        if (removedProductId != null) {
                            var firstCut =  removedProductId.replace('jform_cart_block_product_', '');
                            var ids = firstCut.split('_');

                            $('#'+removedProductId).addClass('strike').fadeTo('slow', 0, function(){
                                $(this).slideUp('slow', function(){
                                    $(this).remove();
                                    // If the cart is now empty, show the 'no product in the cart' message and close detail
                                    if($('#jform_cart_block dl.products dt').length == 0)
                                    {
                                        $("#jform_header #jform_cart_block").stop(true, true).slideUp(200);
                                        $('#jform_cart_block_no_products:hidden').slideDown(450);
                                        $('#jform_cart_block dl.products').remove();
                                    }
                                });
                            });
                            $('#jform_cart_block_combination_of_' + ids[0] + (ids[1] ? '_'+ids[1] : '') + (ids[2] ? '_'+ids[2] : '')).fadeTo('fast', 0, function(){
                                $(this).slideUp('fast', function(){
                                    $(this).remove();
                                });
                            });
                        }
                    }
                });
            }
        }

        function hideOldProductCustomizations(product, domProductId){
            var customizationList = $('#customization_' + product['id'] + '_' + product['idCombination']);
            if(customizationList.length > 0)
            {
                $(customizationList).find("li").each(function(){
                    $(this).find("div").each(function() {
                        var customizationDiv = $(this).attr('id');
                        var tmp = customizationDiv.replace('deleteCustomizableProduct_', '');
                        var ids = tmp.split('_');
                        if ((parseInt(product.idCombination) == parseInt(ids[2])) && !doesCustomizationStillExist(product, ids[0]))
                            $('#' + customizationDiv).parent().addClass('strike').fadeTo('slow', 0, function(){
                                $(this).slideUp('slow');
                                $(this).remove();
                            });
                    });
                });
            }

            var removeLinks = $('#' + domProductId).find('.ajax_cart_block_remove_link');
            if (!product.hasCustomizedDatas && !removeLinks.length)
                $('#' + domProductId + ' span.remove_link').html('<a class="ajax_cart_block_remove_link" rel="nofollow" href="' + options.baseDir + '&view=cart&task=delete&product_id=' + product['id'] + '&product_attribute_id=' + product['idCombination'] + '&token=' + options.static_token + '"> </a>');
            if (product.is_gift)
                $('#' + domProductId + ' span.remove_link').html('');
        }

        function doesCustomizationStillExist(product, customizationId){
            var exists = false;

            $(product.customizedDatas).each(function() {
                if (this.customizationId == customizationId) {
                    exists = true;
                    // This return does not mean that we found nothing but simply break the loop
                    return false;
                }
            });
            return (exists);
        }

        //refresh display of vouchers (needed for vouchers in % of the total)
        function refreshVouchers(jsonData){
            if (typeof(jsonData.discounts) == 'undefined' || jsonData.discounts.length == 0)
                $('#jform_vouchers').hide();
            else
            {
                $('#jform_vouchers tbody').html('');
                for (i=0; i < jsonData.discounts.length; i++)
                {
                    if (parseFloat(jsonData.discounts[i].price_float) > 0)
                    {
                        var delete_link = '';
                        if (jsonData.discounts[i].code.length)
                            delete_link = '<a class="delete_voucher" href="'+jsonData.discounts[i].link+'" title="'+delete_txt+'"><img src="'+img_dir+'icon/delete.gif" alt="'+delete_txt+'" class="icon" /></a>';
                        $('#jform_vouchers tbody').append($(
                            '<tr class="bloc_cart_voucher" id="jform_bloc_cart_voucher_'+jsonData.discounts[i].id+'">'
                                + '<td class="quantity">1x</td>'
                                + '<td class="name" title="'+jsonData.discounts[i].description+'">'+jsonData.discounts[i].name+'</td>'
                                + '<td class="price">-'+jsonData.discounts[i].price+'</td>'
                                + '<td class="delete">' + delete_link + '</td>'
                                + '</tr>'
                        ));
                    }
                }
                $('#jform_vouchers').show();
            }
        }

        function updateProductQuantity(product, quantity){
            $('#cart_block_product_' + product.id + '_' + (product.idCombination ? product.idCombination : '0')+ '_' + (product.idAddressDelivery ? product.idAddressDelivery : '0') + ' .quantity').fadeTo('fast', 0, function() {
                $(this).text(quantity);
                $(this).fadeTo('fast', 1, function(){
                    $(this).fadeTo('fast', 0, function(){
                        $(this).fadeTo('fast', 1, function(){
                            $(this).fadeTo('fast', 0, function(){
                                $(this).fadeTo('fast', 1);
                            });
                        });
                    });
                });
            });
        }

        function displayNewProducts(jsonData){
            //add every new products or update displaying of every updated products
            $(jsonData.products).each(function(){
                //fix ie6 bug (one more item 'undefined' in IE6)
                if (this.id != undefined)
                {
                    //create a container for listing the products and hide the 'no product in the cart' message (only if the cart was empty)

                    if ($('#cart_block dl.products').length == 0)
                    {
                        $('#cart_block_no_products').before('<dl class="products"></dl>');
                        $('#cart_block_no_products').hide();
                    }
                    //if product is not in the displayed cart, add a new product's line
                    var domIdProduct = this.id + '_' + (this.idCombination ? this.idCombination : '0') + '_' + (this.idAddressDelivery ? this.idAddressDelivery : '0');
                    var domIdProductAttribute = this.id + '_' + (this.idCombination ? this.idCombination : '0');
                    if ($('#cart_block_product_'+ domIdProduct).length == 0)
                    {
                        var productId = parseInt(this.id);
                        var productAttributeId = (this.hasAttributes ? parseInt(this.attributes) : 0);
                        var content =  '<dt class="hidden" id="cart_block_product_' + domIdProduct + '">';
                        content += '<span class="quantity-formated"><span class="quantity">' + this.quantity + '</span>x</span>';
                        var name = $('<span />').html(this.name).text();
                        name = (name.length > 12 ? name.substring(0, 10) + '...' : name);
                        content += '<a href="' + this.link + '" title="' + this.name + '" class="cart_block_product_name">' + name + '</a>';

                        if (typeof(this.is_gift) == 'undefined' || this.is_gift == 0)
                            content += '<span class="remove_link"><a rel="nofollow" class="ajax_cart_block_remove_link" href="' + baseUri + '?controller=cart&amp;delete=1&amp;id_product=' + productId + '&amp;token=' + static_token + (this.hasAttributes ? '&amp;ipa=' + parseInt(this.idCombination) : '') + '"> </a></span>';
                        else
                            content += '<span class="remove_link"></span>';
                        if (typeof(freeProductTranslation) != 'undefined')
                            content += '<span class="price">' + (parseFloat(this.price_float) > 0 ? this.priceByLine : freeProductTranslation) + '</span>';
                        content += '</dt>';
                        if (this.hasAttributes)
                            content += '<dd id="cart_block_combination_of_' + domIdProduct + '" class="hidden"><a href="' + this.link + '" title="' + this.name + '">' + this.attributes + '</a>';
                        if (this.hasCustomizedDatas)
                            content += displayNewCustomizedDatas(this);
                        if (this.hasAttributes) content += '</dd>';

                        $('#cart_block dl.products').append(content);
                    }
                    //else update the product's line
                    else {
                        var jsonProduct = this;
                        if($.trim($('#cart_block_product_' + domIdProduct + ' .quantity').html()) != jsonProduct.quantity || $.trim($('#cart_block_product_' + domIdProduct + ' .price').html()) != jsonProduct.priceByLine)
                        {
                            // Usual product
                            if (!this.is_gift)
                                $('#cart_block_product_' + domIdProduct + ' .price').text(jsonProduct.priceByLine);
                            else
                                $('#cart_block_product_' + domIdProduct + ' .price').html(freeProductTranslation);
                            updateProductQuantity(jsonProduct, jsonProduct.quantity);

                            // Customized product
                            if (jsonProduct.hasCustomizedDatas)
                            {
                                customizationFormatedDatas = displayNewCustomizedDatas(jsonProduct);
                                if (!$('#customization_' + domIdProductAttribute).length)
                                {
                                    if (jsonProduct.hasAttributes)
                                        $('#cart_block_combination_of_' + domIdProduct).append(customizationFormatedDatas);
                                    else
                                        $('#cart_block dl.products').append(customizationFormatedDatas);
                                }
                                else
                                {
                                    $('#customization_' + domIdProductAttribute).html('');
                                    $('#customization_' + domIdProductAttribute).append(customizationFormatedDatas);
                                }
                            }
                        }
                    }
                    $('#cart_block dl.products .hidden').slideDown(450).removeClass('hidden');

                    var removeLinks = $('#cart_block_product_' + domIdProduct).find('a.ajax_cart_block_remove_link');
                    if (this.hasCustomizedDatas && removeLinks.length){
                        $(removeLinks).each(function() {
                            $(this).remove();
                        });
                    }
                }
            });
        }

        function displayNewCustomizedDatas(product){
            var content = '';
            var productId = parseInt(product.id);
            var productAttributeId = typeof(product.idCombination) == 'undefined' ? 0 : parseInt(product.idCombination);
            var hasAlreadyCustomizations = $('#customization_' + productId + '_' + productAttributeId).length;

            if (!hasAlreadyCustomizations)
            {
                if (!product.hasAttributes)
                    content += '<dd id="cart_block_combination_of_' + productId + '" class="hidden">';
                if ($('#customization_' + productId + '_' + productAttributeId).val() == undefined)
                    content += '<ul class="cart_block_customizations" id="customization_' + productId + '_' + productAttributeId + '">';
            }

            $(product.customizedDatas).each(function()
            {
                var done = 0;
                customizationId = parseInt(this.customizationId);
                productAttributeId = typeof(product.idCombination) == 'undefined' ? 0 : parseInt(product.idCombination);
                content += '<li name="customization"><div class="deleteCustomizableProduct" id="deleteCustomizableProduct_' + customizationId + '_' + productId + '_' + (productAttributeId ?  productAttributeId : '0') + '"><a rel="nofollow" class="ajax_cart_block_remove_link" href="' + baseUri + '?controller=cart&amp;delete=1&amp;id_product=' + productId + '&amp;ipa=' + productAttributeId + '&amp;id_customization=' + customizationId + '&amp;token=' + static_token + '"></a></div><span class="quantity-formated"><span class="quantity">' + parseInt(this.quantity) + '</span>x</span>';

                // Give to the customized product the first textfield value as name
                $(this.datas).each(function(){
                    if (this['type'] == CUSTOMIZE_TEXTFIELD)
                    {
                        $(this.datas).each(function(){
                            if (this['index'] == 0)
                            {
                                content += ' ' + this.truncatedValue.replace(/<br \/>/g, ' ');
                                done = 1;
                                return false;
                            }
                        })
                    }
                });

                // If the customized product did not have any textfield, it will have the customizationId as name
                if (!done)
                    content += customizationIdMessage + customizationId;
                if (!hasAlreadyCustomizations) content += '</li>';
                // Field cleaning
                if (customizationId)
                {
                    $('#uploadable_files li div.customizationUploadBrowse img').remove();
                    $('#text_fields input').attr('value', '');
                }
            });

            if (!hasAlreadyCustomizations)
            {
                content += '</ul>';
                if (!product.hasAttributes) content += '</dd>';
            }
            return (content);
        }

        function updateCart(jsonData){
            //user errors display
            if (jsonData.hasError){
                var errors = '';
                for (error in jsonData.errors)
                    //IE6 bug fix
                    if (error != 'indexOf')
                        errors += $('<div />').html(jsonData.errors[error]).text() + "\n";
                alert(errors);
            }else{
                updateCartEveryWhere(jsonData);
                hideOldProducts(jsonData);
                displayNewProducts(jsonData);
                refreshVouchers(jsonData);

                //update 'first' and 'last' item classes
                $('#cart_block .products dt').removeClass('first_item').removeClass('last_item').removeClass('item');
                $('#cart_block .products dt:first').addClass('first_item');
                $('#cart_block .products dt:not(:first,:last)').addClass('item');
                $('#cart_block .products dt:last').addClass('last_item');

                //reset the onclick events in relation to the cart block (it allow to bind the onclick event to the new 'delete' buttons added)
                overRideButtonsInThePage();
            }
        }

        function updateCartEveryWhere(jsonData){
            $('.ajax_cart_total').text($.trim(jsonData.productTotal));

            if (parseFloat(jsonData.shippingCostFloat) > 0 || jsonData.nbTotalProducts < 1)
                $('.ajax_cart_shipping_cost').text(jsonData.shippingCost);
            else if (typeof(freeShippingTranslation) != 'undefined')
                $('.ajax_cart_shipping_cost').html(freeShippingTranslation);
            $('.ajax_cart_tax_cost').text(jsonData.taxCost);
            $('.cart_block_wrapping_cost').text(jsonData.wrappingCost);
            $('.ajax_block_cart_total').text(jsonData.total);

            this.nb_total_products = jsonData.nbTotalProducts;

            if (parseInt(jsonData.nbTotalProducts) > 0)
            {
                $('.ajax_cart_no_product').hide();
                $('.ajax_cart_quantity').text(jsonData.nbTotalProducts);
                $('.ajax_cart_quantity').fadeIn('slow');
                $('.ajax_cart_total').fadeIn('slow');

                if (parseInt(jsonData.nbTotalProducts) > 1)
                {
                    $('.ajax_cart_product_txt').each( function () {
                        $(this).hide();
                    });

                    $('.ajax_cart_product_txt_s').each( function () {
                        $(this).show();
                    });
                }
                else
                {
                    $('.ajax_cart_product_txt').each( function () {
                        $(this).show();
                    });

                    $('.ajax_cart_product_txt_s').each( function () {
                        $(this).hide();
                    });
                }
            }
            else
            {
                $('.ajax_cart_quantity, .ajax_cart_product_txt_s, .ajax_cart_product_txt, .ajax_cart_total').each(function(){
                    $(this).hide();
                });
                $('.ajax_cart_no_product').show('slow');
            }
        }

        function checkCustomizations(){
            var pattern = new RegExp(' ?filled ?');

            if (typeof options.customization_fields != 'undefined'){
                for (var i = 0; i < options.customization_fields.length; i++){
                    /* If the field is required and empty then we abort */
                    if (parseInt(options.customization_fields[i][1]) == 1 && ($('#' + options.customization_fields[i][0]).html() == '' ||  $('#' + options.customization_fields[i][0]).text() != $('#' + options.customization_fields[i][0]).val()) && !pattern.test($('#' + options.customization_fields[i][0]).attr('class'))){
                        return false;
                    }
                }
            }
            return true;
        }

        function emptyCustomizations(){
            if(typeof(options.customization_fields) == 'undefined') return;

            $('.customization_block .success').fadeOut(function(){ $(this).remove(); });

            $('.customization_block .error').fadeOut(function(){ $(this).remove(); });

            for (var i = 0; i < options.customization_fields.length; i++){
                $('#' + options.customization_fields[i][0]).html('');
                $('#' + options.customization_fields[i][0]).val('');
            }
        }

        function createXHR(){
            var request = false;
            try {
                request = new ActiveXObject('Msxml2.XMLHTTP');
            }catch (err2) {
                try {
                    request = new ActiveXObject('Microsoft.XMLHTTP');
                }catch (err3) {
                    try {
                        request = new XMLHttpRequest();
                    } catch (err1){
                        request = false;
                    }
                }
            }
            return request;
        }

        function launchRequest(method, url, content, target){
            var xhr = createXHR();
            xhr.onreadystatechange = function(){
                if(xhr.readyState == 4){
                    if(xhr.status == 200){
                        if(target){
                            jQuery(target).append(xhr.responseText);
                        }
                    }else{
                        alert("Error");
                    }
                }
            };
            xhr.open(method, url, true);
            xhr.send( content);
        }

    }
})(jQuery);