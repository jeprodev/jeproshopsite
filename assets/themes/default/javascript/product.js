/**
 * Created by jeproQxT on 12/02/15.
 */
(function($){
    $.fn.JeproshopProduct = function(opts){
        var defaults = {
            combinations : null,
            customization_fields : [],
            content_only : true
        };
        var options = $.extend(defaults, opts);
        var jeproshopProductObject = this;
        var selectedCombination = [];
        var globalQuantity = 0;
        var colors = [];

        init();

        function init(){
            if(options.customization_fields !== 'undefined' && options.customization_fields){
                var customization_fields_backup = options.customization_fields;
                var index_j = 0;
                options.customization_fields = [];
                for(var index_i = 0; index_i < customization_fields_backup.length; ++index_i){
                    var key = 'pictures_' + parseInt(options.product_id) + '_' + parseInt(customization_fields_backup[index_i]['customization_field_id']);
                    options.customization_fields[index_i] = [];
                    options.customization_fields[index_i][0] = (parseInt(customization_fields_backup[index_i]['type']) == 0) ? 'image_' + index_i : 'text_field_' + index_j++;
                    options.customization_fields[index_i][1] = (parseInt(customization_fields_backup[index_i]['type']) == 0 && customization_fields_backup[index_i][key]) ? 2 : parseInt(customization_fields_backup[index_i]['required']);
                }
            }

            if(typeof options.combination_images != 'undefined' && options.combination_images){
                var index_k= 0;
                 var combination_images_java = [];
                combination_images_java[0] = [];
                for(var i in options.combination_images){
                    combination_images_java[i] = [];
                    for(var j in options.combination_images[i]){
                        var image_id = parseInt(options.combination_images[i][j]['image_id']);
                        if(image_id){
                            combination_images_java[0][index_k++] = image_id;
                            combination_images_java[i][j] = [];
                            combination_images_java[i][j] = image_id;
                        }
                    }
                }

                if(typeof combination_images_java[0] !== 'undefined' && combination_images_java[0]){
                    var array_values = [];
                    for(var key in arrayUnique(combination_images_java[0])){
                        array_values.push(combination_images_java[0][key]);
                    }
                    combination_images_java[0] = array_values;
                }
                options.combination_images = combination_images_java;
            }

            if(typeof options.combinations !== 'undefined' && options.combinations){
                combination_java = [];
                var index_k = 0;
                for(var i in options.combinations){
                    globalQuantity += options.combinations[i]['quantity'];
                    combination_java[k] = [];
                    combination_java[k]['combination_id'] = parseInt(i);
                    combination_java[k]['attributes_ids'] = options.combinations[i]['attributes'];
                    combination_java[k]['quantity'] = options.combinations[i]['quantity'];
                    combination_java[k]['price'] = options.combinations[i]['price'];
                    combination_java[k]['ecotax'] = options.combinations[i]['ecotax'];
                    combination_java[k]['image'] = parseInt(options.combinations[i]['image_id']);
                    combination_java[k]['reference'] = options.combinations[i]['reference'];
                    combination_java[k]['unit_price'] = options.combinations[i]['unit_impact'];
                    combination_java[k]['minimal_quantity'] = parseInt(options.combinations[i]['minimal_quantity']);

                    combination_java[k]['available_date'] = [];
                    combination_java[k]['available_date']['date'] = options.combinations[i]['available_date'];
                    combination_java[k]['available_date']['date_formatted'] = options.combinations[i]['date_formatted'];

                    combination_java[k]['specific_price'] = [];
                    combination_java[k]['specific_price']['reduction_percent'] = (options.combinations[i]['specific_price'] && options.combinations[i]['specific_price']['reduction'] && options.combinations[i]['specific_price']['reduction_type'] == 'percentage') ? options.combinations[i]['specific_price']['reduction'] * 100 : 0;
                    combination_java[k]['specific_price']['reduction_price'] = (options.combinations[i]['specific_price'] && options.combinations[i]['specific_price']['reduction'] && options.combinations[i]['specific_price']['reduction_type'] == 'amount') ? options.combinations[i]['specific_price']['reduction'] : 0;
                    combination_java[k]['price'] = (options.combinations[i]['specific_price'] && options.combinations[i]['specific_price']['price'] && parseInt(options.combinations[i]['specific_price']['price']) != 1) ? options.combinations[i]['specific_price']['price'] : options.combinations[i]['price'];

                    combination_java[k]['reduction_type'] = (options.combinations[i]['specific_price'] && options.combinations[i]['specific_price']['reduction_type']) ? options.combinations[i]['specific_price']['reduction_type'] : '';
                    combination_java[k]['product_attribute_id'] = (options.combinations[i]['specific_price'] && options.combinations[i]['specific_price']['product_attribute_id']) ? options.combinations[i]['specific_price']['product_attribute_id'] : 0;
                    k++;
                }
                options.combinations = combination_java;
            }

            $("#jform_thumbs_list").serialScroll({
                items: 'li:visible', next : '#jform_view_scroll_right', previous: '#jform_view_sroll_left', axis: 'X', offset : 0, start : 0, stop: true,
                onBefore: serialScrollFixLock, duration: 700, step:2, lazy: true, lock: false, force: false, cycle: false
            });

            $('#jform_thumbs_list').trigger('goto', 1);
            $('#jform_thumbs_list').trigger('goto', 0);

            //hover 'other views' image
            $('#jform_views_block li a').hover(
                function(){ displayImage($(this)); }
            );

            if(typeof options.jqZoomEnabled != 'undefined' && options.jqZoomEnabled){
                $('.jqzoom').jqzoom({
                    zoomType: 'innerzoom', zoomWidth: 458, zoomHeight: 458, xOffset: 21, yOffset : 0, title: false
                });
            }

            $(document).on('click', '#jform_full_size, #jform_image_block', function(evt){

            });

            origina_url = window.location + '';
            first_url_check = true;
            var url_found = checkUrl();
            initLocationChange();

            if(typeof options.productHasAttributes != 'undefined' && options.productHasAttributes && !url_found){
                findCombination(true);
            }else if(typeof options.productHasAttributes != 'undefined' && !options.productHasAttributes && !url_found){
                refreshProductImages(0);
            }

            $(document).on('click', 'a[name=jform[reset_images]]', function(evt){
                evt.preventDefault();
                refreshProductImages(0);
            });

            $(document).on('click', '.color_pick', function(e){
                e.preventDefault();
                colorPickerClick($(this));
                getProductAttribute();
            });

            $(document).on('change', '.attribute_select', function(e){
                e.preventDefault();
                findCombination();
                getProductAttribute();
            });

            $(document).on('click', '.attribute_radio', function(e){
                e.preventDefault();
                findCombination();
                getProductAttribute();
            });
        }

        function arrayUnique(a){
            return a.reduce(function(p, c){
                if(p.indexOf(c) < 0){p.push(c); }
                return p;
            }, []);
        }

        function function_exists(function_name){
            if(typeof function_name == 'string'){
                return (typeof window[function_name] == 'function');
            }
            return (function_name instanceof Function);
        }

        function addCombination(combination_id, array_of_attributes_id, quantity, price, ecotax, image_id, reference, unit_price, minimal_quantity, available_date, combination_specific_price){
            globalQuantity += quantity;
            var combination = [];
            combination['combination_id'] = combination_id;
            combination['quantity'] = quantity;
            combination['attributes_ids'] = array_of_attributes_id;
            combination['price'] = price;
            combination['ecotax'] = ecotax;
            combination['image'] = image_id;
            combination['reference'] = reference;
            combination['unit_price'] = unit_price;
            combination['minimal_quantity'] = minimal_quantity;
            combination['available_date'] = [];
            combination['available_date'] = available_date;
            combination['specific_price'] = [];
            combination['specific_price'] = combination_specific_price;

            options.combinations.push(combination);
        }

        function findCombination(firstTime){
            $('#jform_minimal_quantity_wanted_p').fadeOut();
            if (typeof $('#jform_minimal_quantity_label').text() === 'undefined' || $('#jform_minimal_quantity_label').html() > 1){
                $('#jform_quantity_wanted').val(1);
            }

            var choice = [];
            var radio_inputs = parseInt($('#jform_attributes .checked > input[type=radio]').length);
            if(radio_inputs){
                radio_inputs = '#jform_attributes .checked > input[type=radio]';
            }else{
                radio_inputs = '#jform_attributes input[type=radio]:checked'
            }

            $('#jform_attributes  select, #jform_attributes input[type=hidden]', + radio_inputs).each(function(){
                choice.push(parseInt($(this).val()));
            });

            if(typeof options.combinations == 'undefined' || !options.combinations){
                options.combinations = [];
            }

            for(var combination = 0; combination < options.combinations.length; ++combination){
                var combinationMatchForm = true;
                $.each(options.combinations[combination]['attributes_ids'], function(key, value){
                    if(!in_array(parseInt(value), choice)){ combinationMatchForm = false; }
                });

                if(combinationMatchForm){
                    if(options.combinations[combination]['minimal_quantity'] > 1){
                        $('#jform_minimal_quantity_label').html(options.combinations[combination]['minimal_quantity']);
                        $('#jform_minimal_quantity_wanted_p').fadeIn();
                        $('#jform_quantity_wanted').val(options.combinations[combination]['minimal_quantity']);
                        $('#jform_quantity_wanted').bind('keyup', function(){ checkMinimalQuantity(options.combinations[combination]['minimal_quantity']); });
                    }

                    //combination of the user has been found in our specifications of combinations (created in back office)
                    selectedCombination['unavailable'] = true;
                    selectedCombination['reference'] = options.combinations[combination]['reference'];

                    //get the data of product with these attributes
                    availableQuantity = options.combinations[combination]['quantity'];
                    selectedCombination['price'] = options.combinations[combination]['price'];
                    selectedCombination['unit_price'] = options.combinations[combination]['unit_price'];
                    selectedCombination['specific_price'] = options.combinations[combination]['specific_price'];
                    if(options.combinations[combination]['ecotax']){
                        selectedCombination['ecotax'] = options.combinations[combination]['ecotax'];
                    }else{
                        selectedCombination['ecotax'] = options.default_eco_tax;
                    }

                    //show the large image in relation to the selected combination
                    if(options.combinations[combination]['image'] && options.combinations[combination]['image'] != -1){
                        displayImage($('#thumb_' + options.combinations[combination]['image']).parent());
                    }

                    //show discounts values according to the selected combination
                    if(options.combinations[combination]['combination_id'] && options.combinations[combination]['combination_id'] > 0){
                        displayDiscounts(options.combinations[combination]['combination_id']);
                    }
                    //get available_date for combination product
                    selectedCombination['available_date'] = options.combinations[combination]['available_date'];

                    updateDisplay();

                    if(typeof(firstTime) != 'undefined' && firstTime){
                        refreshProductImages(0);
                    }else{
                        refreshProductImages(options.combinations[combination]['combination_id'])
                    }
                    return;
                }
            }
            //this combination doesn't exist (not created in back office)
            selectedCombination['unavailable'] = true;
            if (typeof(selectedCombination['available_date']) != 'undefined'){ delete selectedCombination['available_date']; }

            updateDisplay();
        }

        //update display of the availability of the product AND the prices of the product
        function updateDisplay(){
            var productPriceDisplay = options.product_price;
            var productPriceWithoutReductionDisplay = options.product_price_without_reduction;

            if(!selectedCombination['unavailable'] && availableQuantity > 0 && options.product_available_for_order == 1){
                $('#jform_quantity_wanted_p:hidden').show('slow');

                //show the "add to cart" button ONLY if it was hidden
                $('#jform_add_to_cart:hidden').fadeIn(600);

                $('#jform_availability_date').fadeOut();

                if(options.availableNowValue != ''){
                    $('#jform_availability_value').removeClass('warning_inline');
                    $('#jform_availability_value').text(options.availableNowValue);if(options.stock_managment){
                        $('#jform_availability_status:hidden').show();
                    }
                }else{
                    $('#jform_availability_status:visible').hide();
                }

                //'last quantities' message management
                if(!options.allow_buy_when_out_of_stock){
                    if(availableQuantity <= options.max_quantity_to_allow_display_of_last_quantity_message){
                        $('#jform_last_quanties').show('slow');
                    }else{
                        $('#jform_last_quanties').hgide('slow');
                    }
                }

                if (options.quantities_display_allowed){
                    $('#jform_p_quantity_available:hidden').show('slow');
                    $('#jform_quantity_available').text(availableQuantity);

                    if (availableQuantity < 2){
                        // we have 1 or less product in stock and need to show "item" instead of "items"
                        $('#jform_quantity_available_text').show();
                        $('#jform_quantity_available_text_multiple').hide();
                    } else {
                        $('#jform_quantity_available_text').hide();
                        $('#jform_quantity_available_text_multiple').show();
                    }
                }
            }else{
                if(options.product_available_for_order == 1){
                    $('#oosHook').show();
                    if ($('#oosHook').length > 0 && function_exists('oosHookJsCode'))
                        oosHookJsCode();
                }

                //hide 'last quantities' message if it was previously visible
                $('#last_quantities:visible').hide('slow');

                //hide the quantity of pieces if it was previously visible
                $('#pQuantityAvailable:visible').hide('slow');

                //hide the choice of quantities
                if (!allowBuyWhenOutOfStock)
                    $('#quantity_wanted_p:visible').hide('slow');

                //display that the product is unavailable with theses attributes
                if (!selectedCombination['unavailable'])
                {
                    $('#availability_value').text(doesntExistNoMore + (globalQuantity > 0 ? ' ' + doesntExistNoMoreBut : ''));
                    if (!allowBuyWhenOutOfStock)
                        $('#availability_value').addClass('warning_inline');
                }
                else
                {
                    $('#availability_value').text(doesntExist).addClass('warning_inline');
                    $('#oosHook').hide();
                }
                if (stock_management == 1 && !allowBuyWhenOutOfStock)
                    $('#availability_statut:hidden').show();

                if (typeof(selectedCombination['available_date']) != 'undefined' && selectedCombination['available_date']['date'].length != 0)
                {
                    var available_date = selectedCombination['available_date']['date'];
                    var tab_date = available_date.split('-');
                    var time_available = new Date(tab_date[0], tab_date[1], tab_date[2]);
                    time_available.setMonth(time_available.getMonth()-1);
                    var now = new Date();
                    if (now.getTime() < time_available.getTime() && $('#availability_date_value').text() != selectedCombination['available_date']['date_formatted'])
                    {
                        $('#availability_date').fadeOut('normal', function(){
                            $('#availability_date_value').text(selectedCombination['available_date']['date_formatted']);
                            $(this).fadeIn();
                        });
                    }
                    else if (now.getTime() < time_available.getTime())
                        $('#availability_date').fadeIn();
                }
                else
                    $('#availability_date').fadeOut();

                //show the 'add to cart' button ONLY IF it's possible to buy when out of stock AND if it was previously invisible
                if (allowBuyWhenOutOfStock && !selectedCombination['unavailable'] && productAvailableForOrder == 1)
                {
                    $('#add_to_cart:hidden').fadeIn(600);

                    if (availableLaterValue != '')
                    {
                        $('#availability_value').text(availableLaterValue);
                        if (stock_management == 1)
                            $('#availability_status:hidden').show('slow');
                    }else{
                        $('#jform_availability_status:visible').hide('slow');
                    }
                }
                else
                {
                    $('#add_to_cart:visible').fadeOut(600);
                    if (options.stock_management == 1){
                        $('#jform_availability_status:hidden').show('slow');
                    }
                }

                if (productAvailableForOrder == 0){
                    $('#jform_availability_status:visible').hide();
                }
            }

            if (selectedCombination['reference'] || productReference)
            {
                if (selectedCombination['reference'])
                    $('#product_reference span').text(selectedCombination['reference']);
                else if (productReference)
                    $('#product_reference span').text(productReference);
                $('#product_reference:hidden').show('slow');
            }
            else
                $('#product_reference:visible').hide('slow');

            // If we have combinations, update price section: amounts, currency, discount amounts,...
            if (productHasAttributes)
                updatePrice();
        }
    }
})(jQuery);