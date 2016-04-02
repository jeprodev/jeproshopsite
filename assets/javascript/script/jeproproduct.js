/**
 * Created by jeproQxT on 26/07/2015.
 */
(function($){
    $.fn.JeproshopProduct= function(opts){
        //setting default options
        var defaults = {};

        /** calling default options **/
        var options = $.extend(defaults, opts);

        var selectedCombination = [];
        var globalQuantity = 0;
        var colors = [];

        function arrayUnique(a){
            return a.reduce(function(p, c){
                if (p.indexOf(c) < 0)
                    p.push(c);
                return p;
            }, []);
        }

        //check if a function exists
        function function_exists(function_name){
            if (typeof function_name == 'string')
                return (typeof window[function_name] == 'function');
            return (function_name instanceof Function);
        }

        //execute oosHook js code
        function oosHookJsCode(){
            for (var i = 0; i < oosHookJsCodeFunctions.length; i++)
            {
                if (function_exists(oosHookJsCodeFunctions[i]))
                    setTimeout(oosHookJsCodeFunctions[i] + '()', 0);
            }
        }

        //add a combination of attributes in the global JS sytem
        function addCombination(idCombination, arrayOfIdAttributes, quantity, price, ecotax, id_image, reference, unit_price, minimal_quantity, available_date, combination_specific_price)
        {
            globalQuantity += quantity;

            var combination = [];
            combination['idCombination'] = idCombination;
            combination['quantity'] = quantity;
            combination['idsAttributes'] = arrayOfIdAttributes;
            combination['price'] = price;
            combination['ecotax'] = ecotax;
            combination['image'] = id_image;
            combination['reference'] = reference;
            combination['unit_price'] = unit_price;
            combination['minimal_quantity'] = minimal_quantity;
            combination['available_date'] = [];
            combination['available_date'] = available_date;
            combination['specific_price'] = [];
            combination['specific_price'] = combination_specific_price;
            combinations.push(combination);
        }

        // search the combinations' case of attributes and update displaying of availability, prices, ecotax, and image
        function findCombination(firstTime)
        {
            $('#minimal_quantity_wanted_p').fadeOut();
            if (typeof $('#minimal_quantity_label').text() === 'undefined' || $('#minimal_quantity_label').html() > 1)
                $('#quantity_wanted').val(1);

            //create a temporary 'choice' array containing the choices of the customer
            var choice = [];
            var radio_inputs = parseInt($('#attributes .checked > input[type=radio]').length);
            if (radio_inputs)
                radio_inputs = '#attributes .checked > input[type=radio]';
            else
                radio_inputs = '#attributes input[type=radio]:checked';

            $('#attributes select, #attributes input[type=hidden], ' + radio_inputs).each(function(){
                choice.push(parseInt($(this).val()));
            });

            if (typeof combinations == 'undefined' || !combinations)
                combinations = [];
            //testing every combination to find the conbination's attributes' case of the user
            for (var combination = 0; combination < combinations.length; ++combination)
            {
                //verify if this combinaison is the same that the user's choice
                var combinationMatchForm = true;
                $.each(combinations[combination]['idsAttributes'], function(key, value)
                {
                    if (!in_array(parseInt(value), choice))
                        combinationMatchForm = false;
                });

                if (combinationMatchForm)
                {
                    if (combinations[combination]['minimal_quantity'] > 1)
                    {
                        $('#minimal_quantity_label').html(combinations[combination]['minimal_quantity']);
                        $('#minimal_quantity_wanted_p').fadeIn();
                        $('#quantity_wanted').val(combinations[combination]['minimal_quantity']);
                        $('#quantity_wanted').bind('keyup', function() {checkMinimalQuantity(combinations[combination]['minimal_quantity']);});
                    }
                    //combination of the user has been found in our specifications of combinations (created in back office)
                    selectedCombination['unavailable'] = false;
                    selectedCombination['reference'] = combinations[combination]['reference'];
                    $('#idCombination').val(combinations[combination]['idCombination']);

                    //get the data of product with these attributes
                    quantityAvailable = combinations[combination]['quantity'];
                    selectedCombination['price'] = combinations[combination]['price'];
                    selectedCombination['unit_price'] = combinations[combination]['unit_price'];
                    selectedCombination['specific_price'] = combinations[combination]['specific_price'];
                    if (combinations[combination]['ecotax'])
                        selectedCombination['ecotax'] = combinations[combination]['ecotax'];
                    else
                        selectedCombination['ecotax'] = default_eco_tax;

                    //show the large image in relation to the selected combination
                    if (combinations[combination]['image'] && combinations[combination]['image'] != -1)
                        displayImage($('#thumb_' + combinations[combination]['image']).parent());

                    //show discounts values according to the selected combination
                    if (combinations[combination]['idCombination'] && combinations[combination]['idCombination'] > 0)
                        displayDiscounts(combinations[combination]['idCombination']);

                    //get available_date for combination product
                    selectedCombination['available_date'] = combinations[combination]['available_date'];

                    //update the display
                    updateDisplay();

                    if (typeof(firstTime) != 'undefined' && firstTime)
                        refreshProductImages(0);
                    else
                        refreshProductImages(combinations[combination]['idCombination']);
                    //leave the function because combination has been found
                    return;
                }
            }

            //this combination doesn't exist (not created in back office)
            selectedCombination['unavailable'] = true;
            if (typeof(selectedCombination['available_date']) != 'undefined')
                delete selectedCombination['available_date'];

            updateDisplay();
        }

        //update display of the availability of the product AND the prices of the product
        function updateDisplay()
        {
            var productPriceDisplay = productPrice;
            var productPriceWithoutReductionDisplay = productPriceWithoutReduction;

            if (!selectedCombination['unavailable'] && quantityAvailable > 0 && productAvailableForOrder == 1)
            {
                //show the choice of quantities
                $('#quantity_wanted_p:hidden').show('slow');

                //show the "add to cart" button ONLY if it was hidden
                $('#add_to_cart:hidden').fadeIn(600);

                //hide the hook out of stock
                $('#oosHook').hide();

                $('#availability_date').fadeOut();

                //availability value management
                if (availableNowValue != '')
                {
                    //update the availability statut of the product
                    $('#availability_value').removeClass('warning_inline');
                    $('#availability_value').text(availableNowValue);
                    if (stock_management == 1)
                        $('#availability_statut:hidden').show();
                }
                else
                    $('#availability_statut:visible').hide();

                //'last quantities' message management
                if (!allowBuyWhenOutOfStock)
                {
                    if (quantityAvailable <= maxQuantityToAllowDisplayOfLastQuantityMessage)
                        $('#last_quantities').show('slow');
                    else
                        $('#last_quantities').hide('slow');
                }

                if (quantitiesDisplayAllowed)
                {
                    $('#pQuantityAvailable:hidden').show('slow');
                    $('#quantityAvailable').text(quantityAvailable);

                    if (quantityAvailable < 2) // we have 1 or less product in stock and need to show "item" instead of "items"
                    {
                        $('#quantityAvailableTxt').show();
                        $('#quantityAvailableTxtMultiple').hide();
                    }
                    else
                    {
                        $('#quantityAvailableTxt').hide();
                        $('#quantityAvailableTxtMultiple').show();
                    }
                }
            }else {
                //show the hook out of stock
                if (productAvailableForOrder == 1)
                {
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
                            $('#availability_statut:hidden').show('slow');
                    }
                    else
                        $('#availability_statut:visible').hide('slow');
                }
                else
                {
                    $('#add_to_cart:visible').fadeOut(600);
                    if (stock_management == 1)
                        $('#availability_statut:hidden').show('slow');
                }

                if (productAvailableForOrder == 0)
                    $('#availability_statut:visible').hide();
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

        function updatePrice(){
            // Get combination prices
            combID = $('#idCombination').val();
            combination = combinationsFromController[combID];
            if (typeof combination == 'undefined')
                return;

            // Set product (not the combination) base price
            var basePriceWithoutTax = productBasePriceTaxExcl;
            var priceWithGroupReductionWithoutTax = 0;

            // Apply combination price impact
            // 0 by default, +x if price is inscreased, -x if price is decreased
            basePriceWithoutTax = basePriceWithoutTax + combination.price;

            // If a specific price redefine the combination base price
            if (combination.specific_price && combination.specific_price.price > 0)
                basePriceWithoutTax = combination.specific_price.price;

            // Apply group reduction
            priceWithGroupReductionWithoutTax = basePriceWithoutTax * (1 - group_reduction);
            var priceWithDiscountsWithoutTax = priceWithGroupReductionWithoutTax;

            // Apply Tax if necessary
            if (noTaxForThisProduct || customerGroupWithoutTax)
            {
                basePriceDisplay = basePriceWithoutTax;
                priceWithDiscountsDisplay = priceWithDiscountsWithoutTax;
            }
            else
            {
                basePriceDisplay = basePriceWithoutTax * (taxRate/100 + 1);
                priceWithDiscountsDisplay = priceWithDiscountsWithoutTax * (taxRate/100 + 1);

            }

            if (default_eco_tax)
            {
                // combination.ecotax doesn't modify the price but only the display
                basePriceDisplay = basePriceDisplay + default_eco_tax * (1 + ecotaxTax_rate / 100);
                priceWithDiscountsDisplay = priceWithDiscountsDisplay + default_eco_tax * (1 + ecotaxTax_rate / 100);
            }

            // Apply specific price (discount)
            // Note: Reduction amounts are given after tax
            if (combination.specific_price && combination.specific_price.reduction > 0)
                if (combination.specific_price.reduction_type == 'amount')
                {
                    priceWithDiscountsDisplay = priceWithDiscountsDisplay - combination.specific_price.reduction;
                    // We recalculate the price without tax in order to keep the data consistency
                    priceWithDiscountsWithoutTax = priceWithDiscountsDisplay * ( 1/(1+taxRate) / 100 );
                }
                else if (combination.specific_price.reduction_type == 'percentage')
                {
                    priceWithDiscountsDisplay = priceWithDiscountsDisplay * (1 - combination.specific_price.reduction);
                    // We recalculate the price without tax in order to keep the data consistency
                    priceWithDiscountsWithoutTax = priceWithDiscountsDisplay * ( 1/(1+taxRate) / 100 );
                }

            // Compute discount value and percentage
            // Done just before display update so we have final prices
            if (basePriceDisplay != priceWithDiscountsDisplay)
            {
                var discountValue = basePriceDisplay - priceWithDiscountsDisplay;
                var discountPercentage = (1-(priceWithDiscountsDisplay/basePriceDisplay))*100;
            }

            /*  Update the page content, no price calculation happens after */

            // Hide everything then show what needs to be shown
            $('#reduction_percent').hide();
            $('#reduction_amount').hide();
            $('#old_price,#old_price_display,#old_price_display_taxes').hide();
            $('.price-ecotax').hide();
            $('.unit-price').hide();


            $('#our_price_display').text(formatCurrency(priceWithDiscountsDisplay * currencyRate, currencyFormat, currencySign, currencyBlank));

            // If the calculated price (after all discounts) is different than the base price
            // we show the old price striked through
            if (priceWithDiscountsDisplay.toFixed(2) != basePriceDisplay.toFixed(2))
            {
                $('#old_price_display').text(formatCurrency(basePriceDisplay * currencyRate, currencyFormat, currencySign, currencyBlank));
                $('#old_price,#old_price_display,#old_price_display_taxes').show();

                // Then if it's not only a group reduction we display the discount in red box
                if (priceWithDiscountsWithoutTax != priceWithGroupReductionWithoutTax)
                {
                    if (combination.specific_price.reduction_type == 'amount')
                    {
                        $('#reduction_amount_display').html('-' + formatCurrency(parseFloat(discountValue), currencyFormat, currencySign, currencyBlank));
                        $('#reduction_amount').show();
                    }
                    else
                    {
                        $('#reduction_percent_display').html('-' + parseFloat(discountPercentage).toFixed(0) + '%');
                        $('#reduction_percent').show();
                    }
                }
            }

            // Green Tax (Eco tax)
            // Update display of Green Tax
            if (default_eco_tax)
            {
                ecotax = default_eco_tax;

                // If the default product ecotax is overridden by the combination
                if (combination.ecotax)
                    ecotax = combination.ecotax;

                if (!noTaxForThisProduct)
                    ecotax = ecotax * (1 + ecotaxTax_rate/100)

                $('#ecotax_price_display').text(formatCurrency(ecotax * currencyRate, currencyFormat, currencySign, currencyBlank));
                $('.price-ecotax').show();
            }

            // Unit price are the price per piece, per Kg, per m²
            // It doesn't modify the price, it's only for display
            if (productUnitPriceRatio > 0)
            {
                unit_price = priceWithDiscountsDisplay / productUnitPriceRatio;
                $('#unit_price_display').text(formatCurrency(unit_price * currencyRate, currencyFormat, currencySign, currencyBlank));
                $('.unit-price').show();
            }

            // If there is a quantity discount table,
            // we update it according to the new price
            updateDiscountTable(priceWithDiscountsDisplay);


            //
        }

        //update display of the large image
        function displayImage(domAAroundImgThumb, no_animation)
        {
            if (typeof(no_animation) == 'undefined')
                no_animation = false;
            if (domAAroundImgThumb.prop('href'))
            {
                var new_src = domAAroundImgThumb.attr('href').replace('thickbox', 'large');
                var new_title = domAAroundImgThumb.attr('title');
                var new_href = domAAroundImgThumb.attr('href');
                if ($('#bigpic').prop('src') != new_src)
                {
                    $('#bigpic').attr({
                        'src' : new_src,
                        'alt' : new_title,
                        'title' : new_title
                    }).load(function(){
                        if (typeof(jqZoomEnabled) != 'undefined' && jqZoomEnabled)
                            $(this).attr('rel', new_href);
                    });
                }
                $('#views_block li a').removeClass('shown');
                $(domAAroundImgThumb).addClass('shown');
            }
        }

        //update display of the discounts table
        function displayDiscounts(combination){
            $('#quantityDiscount tbody tr').each(function(){
                if (($(this).attr('id') != 'quantityDiscount_0') &&
                    ($(this).attr('id') != 'quantityDiscount_' + combination) &&
                    ($(this).attr('id') != 'noQuantityDiscount'))
                    $(this).fadeOut('slow');
            });

            if ($('#quantityDiscount_' + combination+',.quantityDiscount_' + combination).length != 0
                || $('#quantityDiscount_0,.quantityDiscount_0').length != 0)
            {
                $('#quantityDiscount').parent().show();
                $('#quantityDiscount_' + combination+',.quantityDiscount_' + combination).show();
                $('#noQuantityDiscount').hide();
            }
            else
            {
                $('#quantityDiscount').parent().hide();
                $('#noQuantityDiscount').show();
            }
        }

        function updateDiscountTable(newPrice){
            $('#quantityDiscount tbody tr').each(function(){
                var type = $(this).data("discount-type");
                var discount = $(this).data("discount");
                var quantity = $(this).data("discount-quantity");

                if (type == 'percentage')
                {
                    var discountedPrice = newPrice * (1 - discount/100);
                    var discountUpTo = newPrice * (discount/100) * quantity;
                }
                else if (type == 'amount')
                {
                    var discountedPrice = newPrice - discount;
                    var discountUpTo = discount * quantity;
                }

                if (displayDiscountPrice != 0)
                    $(this).children('td').eq(1).text( formatCurrency(discountedPrice, currencyFormat, currencySign, currencyBlank) );
                $(this).children('td').eq(2).text(upToTxt + ' ' + formatCurrency(discountUpTo, currencyFormat, currencySign, currencyBlank));
            });
        }

        // Serialscroll exclude option bug ?
        function serialScrollFixLock(event, targeted, scrolled, items, position){
            serialScrollNbImages = $('#thumbs_list li:visible').length;
            serialScrollNbImagesDisplayed = 3;

            var leftArrow = position == 0 ? true : false;
            var rightArrow = position + serialScrollNbImagesDisplayed >= serialScrollNbImages ? true : false;

            $('#view_scroll_left').css('cursor', leftArrow ? 'default' : 'pointer').css('display', leftArrow ? 'none' : 'block').fadeTo(0, leftArrow ? 0 : 1);
            $('#view_scroll_right').css('cursor', rightArrow ? 'default' : 'pointer').fadeTo(0, rightArrow ? 0 : 1).css('display', rightArrow ? 'none' : 'block');
            return true;
        }

        // Change the current product images regarding the combination selected
        function refreshProductImages(id_product_attribute){
            $('#thumbs_list_frame').scrollTo('li:eq(0)', 700, {axis:'x'});

            id_product_attribute = parseInt(id_product_attribute);

            if (id_product_attribute > 0 && typeof(combinationImages) != 'undefined' && typeof(combinationImages[id_product_attribute]) != 'undefined')
            {
                $('#thumbs_list li').hide();
                $('#thumbs_list').trigger('goto', 0);
                for (var i = 0; i < combinationImages[id_product_attribute].length; i++)
                    if (typeof(jqZoomEnabled) != 'undefined' && jqZoomEnabled)
                        $('#thumbnail_' + parseInt(combinationImages[id_product_attribute][i])).show().children('a.shown').trigger('click');
                    else
                        $('#thumbnail_' + parseInt(combinationImages[id_product_attribute][i])).show();
            }
            else
                $('#thumbs_list li').show();

            if (parseInt($('#thumbs_list_frame >li:visible').length) != parseInt($('#thumbs_list_frame >li').length))
                $('#wrapResetImages').stop(true, true).show();
            else
                $('#wrapResetImages').stop(true, true).hide();

            var thumb_width = $('#thumbs_list_frame >li').outerWidth() + parseInt($('#thumbs_list_frame >li').css('marginRight'));
            $('#thumbs_list_frame').width((parseInt((thumb_width) * $('#thumbs_list_frame >li').length)) + 'px');
            $('#thumbs_list').trigger('goto', 0);
            serialScrollFixLock('', '', '', '', 0);// SerialScroll Bug on goto 0 ?
        }

        function saveCustomization(){
            $('#quantityBackup').val($('#quantity_wanted').val());
            customAction = $('#customizationForm').attr('action');
            $('body select[id^="group_"]').each(function() {
                customAction = customAction.replace(new RegExp(this.id + '=\\d+'), this.id +'=' + this.value);
            });
            $('#customizationForm').attr('action', customAction);
            $('#customizationForm').submit();
        }

        function submitPublishProduct(url, redirect, token){
            var id_product = $('#admin-action-product-id').val();

            $.ajaxSetup({async: false});
            $.post(url + '/index.php', {
                    action:'publishProduct',
                    id_product: id_product,
                    status: 1,
                    redirect: redirect,
                    ajax: 1,
                    tab: 'AdminProducts',
                    token: token
                },
                function(data)
                {
                    if (data.indexOf('error') === -1)
                        document.location.href = data;
                }
            );
            return true;
        }

        function checkMinimalQuantity(minimal_quantity){
            if ($('#quantity_wanted').val() < minimal_quantity)
            {
                $('#quantity_wanted').css('border', '1px solid red');
                $('#minimal_quantity_wanted_p').css('color', 'red');
            }
            else
            {
                $('#quantity_wanted').css('border', '1px solid #BDC2C9');
                $('#minimal_quantity_wanted_p').css('color', '#374853');
            }
        }

        function colorPickerClick(elt){
            id_attribute = $(elt).attr('id').replace('color_', '');
            $(elt).parent().parent().children().removeClass('selected');
            $(elt).fadeTo('fast', 1, function(){
                $(this).fadeTo('fast', 0, function(){
                    $(this).fadeTo('fast', 1, function(){
                        $(this).parent().addClass('selected');
                    });
                });
            });
            $(elt).parent().parent().parent().children('.color_pick_hidden').val(id_attribute);
            findCombination(false);
        }

        function getProductAttribute(){
            // get product attribute id
            product_attribute_id = $('#idCombination').val();
            product_id = $('#product_page_product_id').val();

            // get every attributes values
            request = '';
            //create a temporary 'tab_attributes' array containing the choices of the customer
            var tab_attributes = [];
            var radio_inputs = parseInt($('#attributes .checked > input[type=radio]').length);
            if (radio_inputs)
                radio_inputs = '#attributes .checked > input[type=radio]';
            else
                radio_inputs = '#attributes input[type=radio]:checked';

            $('#attributes select, #attributes input[type=hidden], ' + radio_inputs).each(function(){
                tab_attributes.push($(this).val());
            });

            // build new request
            for (var i in attributesCombinations)
                for (var a in tab_attributes)
                    if (attributesCombinations[i]['id_attribute'] === tab_attributes[a])
                        request += '/'+attributesCombinations[i]['group'] + attribute_anchor_separator + attributesCombinations[i]['attribute'];
            request = request.replace(request.substring(0, 1), '#/');
            url = window.location + '';

            // redirection
            if (url.indexOf('#') != -1)
                url = url.substring(0, url.indexOf('#'));

            // set ipa to the customization form
            $('#customizationForm').attr('action', $('#customizationForm').attr('action') + request);
            window.location = url + request;
        }

        function initLocationChange(time){
            if (!time) time = 500;
            setInterval(checkUrl, time);
        }

        function checkUrl(){
            if (original_url != window.location || first_url_check){
                first_url_check = false;
                url = window.location + '';
                // if we need to load a specific combination
                if (url.indexOf('#/') != -1)
                {
                    // get the params to fill from a "normal" url
                    params = url.substring(url.indexOf('#') + 1, url.length);
                    tabParams = params.split('/');
                    tabValues = [];
                    if (tabParams[0] == '')
                        tabParams.shift();
                    for (var i in tabParams)
                        tabValues.push(tabParams[i].split(attribute_anchor_separator));
                    product_id = $('#product_page_product_id').val();
                    // fill html with values
                    $('.color_pick').removeClass('selected');
                    $('.color_pick').parent().parent().children().removeClass('selected');
                    count = 0;
                    for (var z in tabValues)
                        for (var a in attributesCombinations)
                            if (attributesCombinations[a]['group'] === decodeURIComponent(tabValues[z][0])
                                && attributesCombinations[a]['attribute'] === decodeURIComponent(tabValues[z][1]))
                            {
                                count++;
                                // add class 'selected' to the selected color
                                $('#color_' + attributesCombinations[a]['id_attribute']).addClass('selected');
                                $('#color_' + attributesCombinations[a]['id_attribute']).parent().addClass('selected');
                                $('input:radio[value=' + attributesCombinations[a]['id_attribute'] + ']').attr('checked', true);
                                $('input[type=hidden][name=group_' + attributesCombinations[a]['id_attribute_group'] + ']').val(attributesCombinations[a]['id_attribute']);
                                $('select[name=group_' + attributesCombinations[a]['id_attribute_group'] + ']').val(attributesCombinations[a]['id_attribute']);
                            }
                    // find combination
                    if (count >= 0)
                    {
                        findCombination(false);
                        original_url = url;
                        return true;
                    }
                    // no combination found = removing attributes from url
                    else
                        window.location = url.substring(0, url.indexOf('#'));
                }
            }
            return false;
        }
    };
})(jQuery);