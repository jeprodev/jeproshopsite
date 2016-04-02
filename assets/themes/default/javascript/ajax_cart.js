/**
 * Created by jeproQxT on 08/02/15.
 */
(function($){
    $.fn.JeproshopAjaxCart = function(opts){
        var defaults = {
            content_only : true,
            required_field : null
        };
        var options = $.extend(defaults, opts);

        var jeproshopAjaxCartObj = this;

        return jeproshopAjaxCartObj.each(function(){
            init();
        });

        function init(){
            overRideButtonsInThePage();
/*
            $(document).on('click', '.block_cart_collapse', function(evt){
                evt.preventDefault();
                collapse();
            });

            $(document).on('click', '.block_cart_expand', function(evt){
                evt.preventDefault();
                expand();
            });

            var cart_quantity = 0;
            var current_timestamp = parseInt(new Date().getTime() / 1000);

            if(typeof $('.ajax_cart_quantity').html() == 'undefined' || (typeof generated_date != 'undefined' && generated_date != null && (parseInt(generated_date) + 30 < current_timestamp))){
                refresh();
            }else{
                cart_quantity = parseInt($('.ajax_cart_quantity').html());
            }

            /** roll over cart ** /
            var cart_block = new HoverWatcher('.cart_block');
            var shopping_cart = new HoverWatcher('.shopping_cart');

            if('ontouchstart' in document.documentElement){
                $('.shipping_cart > a:first').on('click', function(evt){
                    evt.preventDefault();
                });
            }

            $(document).on('touchstart', '.shopping_cart a:first', function(){
                if($(this).next('.cart_block:visible').length){
                    $('.cart_block').stop(true, true).slideUp(450);
                }else{
                    $('.cart_block').stop(true, true).slideDown(450);
                }
                evt.preventDefault();
                evt.stopPropagation();
            });

            $(document).on('click', '.ajax_add_to_cart button', function(evt){
                evt.preventDefault();
                var product_id = $(this).data('product-id');
                if($(this).prop('disabled') != 'disabled'){ add(product_id, null, false, this); }
            });

            $('.shopping_cart a:first').hover(
                function(){
                    if(options.nb_total_products > 0 || cart_quantity > 0){ $('.cart_block').stop(true, true).slideDown(450); }
                },
                function(){
                    setTimeout(function(){
                        if(!shopping_cart.isHoveringOver() && !cart_block.isHoveringOver()){
                            $('.cart_block').stop(true, true).slideUp(450);
                        }
                    }, 200);
                }
            );

            $('.cart_block').hover(
                function(){},
                function(){
                    setTimeout(function(){
                        if(!shopping_cart.isHoverOver()){ $('.cart_block').stop(true, true).slideUp(450); }
                    }, 200);
                }
            );

            $(document).on('click', '.delete_voucher', function(evt){
                evt.preventDefault();
                var xhr = ajaxRequest();
                var url = $(this).attr('href') + '&rand=' + new Date().getTime();

                $(this).parent().parent().remove();
                if(current_view == 'oder' || current_view == 'oder-pc'){
                    if(typeof(update_address_selection) != 'undefined'){
                        updateAddressSelection();
                    }else{ location.reload(); }
                }
            });



            /** for product added from product page ** /
            $(document).on('click', '#jform_add_to_cart button', function(evt){
                evt.preventDefault();
                add($('#jform_product_id').val(), $('jform_combination_id').val(), true, null, $('#jform_quantity_wanted').val(), null);
            });

            $(document).on('click', '.cart_block_list .ajax_cart_block_remove_link', function(evt){
                evt.preventDefault();
                /** customized product management ** /
                var customization_id = 0;
                var product_id = 0;
                var product_attribute_id = 0;
                var customizable_product_div = $($(this).parent()).find("div[data-id^=jform_delete_customizable_product_]");
                var address_delivery_id = false;

                if(customizable_product_div && $(customizable_product_div).length){
                    var ids = customizable_product_div.data('id').split('_');
                    if(typeof(ids[1]) != undefined){
                        customization_id = parseInt(ids[1]);
                        product_id = parseInt(ids[2]);
                        if(typeof(ids[3]) != undefined){ product_attribute_id = parseInt(ids[3]); }
                        if(typeof(ids[4]) != undefined){address_delivery_id = parseInt(ids[4]); }
                    }
                }

                /** common product management ** /
                if(!customization_id){
                    /** retrieve product_id and combination_id from the displayed product in the block cart ** /
                    var firstCut = $(this).parent().parent().data('id').replace('cart_block_product_', '');
                    firstCut = firstCut.replace('deleteCustomizableProduct_', '');
                    ids = firstCut.split('_');
                    product_id = parseInt(ids[0]);

                    if (typeof(ids[1]) != 'undefined'){ product_attribute_id = parseInt(ids[1]); }
                    if (typeof(ids[2]) != 'undefined'){ address_delivery_id = parseInt(ids[2]); }
                }
                // Removing product from the cart
                jeproshopAjaxCartObj.remove(product_id, product_attribute_id, customization_id, address_delivery_id);
            }); */
        }

        function overRideButtonsInThePage(){
            $('a.ajax_add_to_cart_button', jeproshopAjaxCartObj).each(function(index, elt){

            });
            $(document).on('click', '.ajax_add_to_cart_button', function(evt){
                evt.preventDefault();
                var product_id = $(this).data('product-id');
                if($(this).prop('disabled') != 'disabled'){
                    add(product_id, null, false, this);
                }
            });

            //for product page 'add' button...
            $(document).on('click', '#jform_add_to_cart button', function(evt){
                evt.preventDefault();
                add($('#jform_product_id').val(), $('#jform_combination_id').val(), true, null, $('#jform_quantity_wanted').val(), null);
            });

            $(document).on('click', '.cart_block_list .ajax_cart_block_remove_link', function(evt){
                evt.preventDefault();
                //Customized product management
                var customization_id = 0;
                var product_id = 0;
                var product_attribute_id = 0;
                var customizable_product_wrapper = $($(this).parent().parent()).find("div[data-id^=delete_customizable_product_]");
                var address_delivery_id = false;

                if(customizable_product_wrapper && $(customizable_product_wrapper).length){
                    var ids = customizable_product_wrapper.data('id').split('_');
                    if(typeof(ids[1]) != 'undefined'){
                        customization_id = parseInt(ids[1]);
                        product_id = parseInt(ids[2]);
                        if(typeof(ids[3])  != 'undefined'){ product_attribute_id = parseInt(ids[3]); }
                        if(typeof(ids[4])  != 'undefined'){ address_delivery_id = parseInt(ids[4]); }
                    }
                }

                //common product management
                if(!customization_id){
                    //retrieve product_id and customization_id  from the displayed product in the block cart
                    var firstCut = $(this).parent().parent().data('id').replace('cart_block_product_', '');
                    firstCut = firstCut.replace('delete_customizable_product_', '');
                    ids = firstCut.split('_');
                    product_id = parseInt(ids[0]);

                    if(typeof(ids[1]) != 'undefined'){ product_attribute_id = parseInt(ids[1]); }
                    if(typeof(ids[2]) != 'undefined'){ address_delivery_id = parseInt(ids[2]); }
                }

                //Removing product from the cart
                remove(product_id, product_attribute_id, customization_id, address_delivery_id);
            });
        }

        function remove(product_id, combination_id, customization_id, address_delivery_id){
            var url = 'index.php?option=com_jeproshop&view=cart&task=delete&rand=' + new Date().getTime() + '&product_id=' + product_id + '&product_attribute_id=' + ((combination_id != null && parseInt(combination_id)) ? combination_id : '') + ((customization_id && customization_id != null) ? '&customization_id=' + customization_id : '') + '&address_delivery_id=' + address_delivery_id + '&use_ajax=1';
            var xhr = ajaxRequest(); //TODO to be continued
        }
/*
        function add(product_id, combination_id, added_from_product_page, caller_element, quantity, wish_list){
            if(added_from_product_page && !checkCustomizations()){
                if(options.content_only){
                    var product_url = window.document.location.href + '';
                    var data = product_url.replace('content_only=1', '');
                    window.parent.document.href = data;
                }

                if(!!$.prototype.fancybox){
                    $.fancybox.open([{type : 'inline', autoScale: true, minHeight: 30, content : '<p class="fancybox-error">' + options.required_field + '</p>'}],{ padding :0 });
                }else{ alert(options.required_field); }
            }

            emptyCustomizations();

            /** Disable te button when adding to avoid double adding on double click ** /
            if(added_from_product_page){
                $('#jform_add_to_cart button').prop('disabled', 'disabled').addClass('disabled');
                $('.filled').removeClass('filled');
            }else{
                $(caller_element).prop('disabled', 'disabled');
            }

            if($('.cart_block_list').hasClass('collapsed')){ this.expand(); }

            //send the ajax request to the server
            ajaxRequest(product_id, combination_id, quantity, wish_list, added_from_product_page);
        }

        /** update cart information ** /
        function updateCartInformation(jsonData, added_from_product_page){
            jeproshopAjaxCartObj.updateCart(jsonData);
            if(added_from_product_page){
                $('#jform_add_to_cart button').removeProp('disabled').removeClass('disabled');
                if(!jsonData.hasError || jsonData.hasError == false){
                    $('#jform_add_to_cart button').addClass('added');
                }else{
                    $('#jform_add_to_cart button').removeClass('added');
                }
            }else{
                $('.ajax_add_to_cart button').removeProp('disabled');
            }
        }

        /** update product quantity ** /
        function updateProductQuantity(product, quantity){
            $('dt[data-id=jform_cart_block_product_' + product.product_id + '_' + (product.combination_id ? product.combination_id : '') + '_' + (product.address_delivery_id ? product.address_delivery_id : '0') + '] .quantity').fadeTo('fast', 0, function(){
                $(this).text(quantity);
                $(this).fadeTo('fast', 1, function(){
                    $(this).fadeTo('fast', 0, function(){
                        $(this).fadeTo('fast', 1, function(){
                            $(this).fadeTo('fast', 0, function(){ $(this).fadeTo('fast', 1); });
                        });
                    });
                });
            });
        }

        /** Generally update the display of the cart ** /
        function updateCart(jsonData){
            /** user errors display ** /
            if(jsonData.hasError){
                var errors = '';
                for(error in jsonData.errors){
                    if(error != 'indexOf'){ errors += $('<div />' + html(jsonData.errors[error].text() + "\n"))}
                }

                if(!!$.prototype.fancybox){
                    $.fancybox.open([{ type: 'inline', autoScale: true, minHeight: 30, content: '<p class="fancybox-error">' + errors + '</p>'}], { padding: 0 })
                }else{ alert(errors); }
            }else{
                jeproshopAjaxCartObj.updateCartEveryWhere(jsonData);
                jeproshopAjaxCartObj.hideOldProduct(jsonData);
                jeproshopAjaxCartObj.displayNewProduct(jsonData);
                jeproshopAjaxCartObj.refreshVouchers(jsonData);

                $('.cart_block .products dt').removeClass('first_item').removeClass('last_item').removeClass('item');
                $('.cart_block .products dt:first').addClass('first_item');
                $('.cart_block .products dt:not(:first,:last)').addClass('item');
                $('.cart_block .products dt:last').addClass('last-item');
            }
        }

        //update general cart informations everywhere in the page
        function updateCartEveryWhere(jsonData){
            $('.ajax_cart_total').text($.trim(jsonData.productTotal));

            if (parseFloat(jsonData.shippingCostFloat) > 0){
                $('.ajax_cart_shipping_cost').text(jsonData.shippingCost);
            }else if (typeof(freeShippingTranslation) != 'undefined'){
                $('.ajax_cart_shipping_cost').html(freeShippingTranslation);
            }
            $('.ajax_cart_tax_cost').text(jsonData.taxCost);
            $('.cart_block_wrapping_cost').text(jsonData.wrappingCost);
            $('.ajax_block_cart_total').text(jsonData.total);
            $('.ajax_block_products_total').text(jsonData.productTotal);
            $('.ajax_total_price_wt').text(jsonData.total_price_wt);

            if (parseFloat(jsonData.freeShippingFloat) > 0) {
                $('.ajax_cart_free_shipping').html(jsonData.freeShipping);
                $('.free_shipping').fadeIn(0);
            } else if (parseFloat(jsonData.freeShippingFloat) == 0){
                $('.free_shipping').fadeOut(0);
            }

            this.nb_total_products = jsonData.nbTotalProducts;

            if (parseInt(jsonData.nbTotalProducts) > 0) {
                $('.ajax_cart_no_product').hide();
                $('.ajax_cart_quantity').text(jsonData.nbTotalProducts);
                $('.ajax_cart_quantity').fadeIn('slow');
                $('.ajax_cart_total').fadeIn('slow');

                if (parseInt(jsonData.nbTotalProducts) > 1) {
                    $('.ajax_cart_product_txt').each( function (){
                        $(this).hide();
                    });

                    $('.ajax_cart_product_txt_s').each( function (){
                        $(this).show();
                    });
                } else {
                    $('.ajax_cart_product_txt').each( function (){
                        $(this).show();
                    });

                    $('.ajax_cart_product_txt_s').each( function (){
                        $(this).hide();
                    });
                }
            } else {
                $('.ajax_cart_quantity, .ajax_cart_product_txt_s, .ajax_cart_product_txt, .ajax_cart_total').each(function(){
                    $(this).hide();
                });
                $('.ajax_cart_no_product').show('slow');
            }
        }

        function checkCustomizations(){
            return false;
        }

        function emptyCustomizations(){

        }

        function ajaxRequest(product_id, combination_id, quantity, wish_list, added_from_product_page){ alert('bonjour jeff');
            var xhr;
            var url = 'index.php?option=com_jeproshop&rand=' + new Date().getTime() + '&view=cart&task=add&use_ajax=1&quantity=' + ((quantity && quantity != null) ? quantity : '1')  + '&product_id=' + product_id + ((parseInt(combination_id) && combination_id != null ) ? '&product_attribute_id=' + parseInt(combination_id) : '');
            var target; //+ '&' + options.token + '=1'
            try{
                xhr = new ActiveXObject('Msxml2.XMLHTTP');
            }catch(ex){
                try{
                    xhr = new ActiveXObject('Microsoft.XMLHTTP');
                }catch (exp){
                    try{
                        xhr = new XMLHttpRequest();
                    }catch(excep){
                        xhr = false;
                    }
                }
            }

            xhr.onreadystatechange = function(){
                if(xhr.readyState == 4){
                    if(xhr.status == 200){
                        if(wish_list){ wishListAddProductCart(wish_list[0], product_id, combination_id, wish_list[1]); }

                        if(options.content_only){
                            updateCartInformation(xhr.responseXML, added_from_product_page);
                        }else{
                            updateCartInformation(xhr.responseXML, added_from_product_page);
                        }

                        if(combination_id){

                        }
                    }else{
                       alert('error');
                    }
                }
            }
            xhr.open("POST", url, true);
            xhr.send();
        }

        function updateCartInformation(xmlData, added_from_product_page){
            updateCart(xmlData);

            //reactive the button when adding has finished
            if(added_from_product_page){
                $('#jform_add_to_cart button').removeProp('disabled').removeClass('disabled');
                if(!xmlData){
                    $('#jform_add_to_cart button').addClass('added');
                }else{
                    $('#jform_add_to_cart button').removeClass('added');
                }
            }else{ $('.ajax_add_to_cart_button').removeProp('disabled'); }
        }
*/
        function refresh(){
            var xhr = ajaxRequest();
            var url = 'index.php?option=com_jeproshop&view=cart&use_ajax=1&rand=' + new Date().getTime();
            xhr.onreadystatechange = function(){
                if(xhr.readyState == 4){
                    if(xhr.status == 200){
                        updateCart();
                    }else{
                        alert('error');
                    }
                }
            }
            xhr.open("POST", url, true);
            xhr.send();
        }

        function ajaxRequest(url, type, target){
            var xhr;
            try{
                xhr = new ActiveXObject('Msxml2.XMLHTTP');
            }catch(ex){
                try{
                    xhr = new ActiveXObject('Microsoft.XMLHTTP');
                }catch (exp){
                    try{
                        xhr = new XMLHttpRequest();
                    }catch(excep){
                        xhr = false;
                    }
                }
            }
            return xhr;
/*
            xhr.onreadystatechange = function(){
                if(xhr.readyState == 4){
                    if(xhr.status == 200){

                    }else{
                       alert('error');
                    }
                }
            }
            xhr.open(type, url, true);
            xhr.send(); */
        }
    };
})(jQuery);