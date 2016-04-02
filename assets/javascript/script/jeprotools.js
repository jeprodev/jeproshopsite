/** jeproshop tools class **
 *
 * @param opts
 * @constructor
 */
var JeproshopTools = function(opts){
    var defaults = {};
    this.options = jQuery.extend(opts, defaults);
};

jQuery.extend(JeproshopTools.prototype, {
    isName :function(str){
        var reg = /^[^0-9!<>,;?=+()@#"°{}_$%:]+$/;
        return reg.test(str);
    },
    isGenericName :function(str){
        var reg = /^[^<>={}]+$/;
        return reg.test(str);
    },
    isAddress :function(str){
        var reg = /^[^!<>?=+@{}_$%]+$/;
        return reg.test(str);
    },
    isPostCode:function (str, pattern) {
        if (typeof(pattern) == 'undefined' || pattern.length == 0) {
            pattern = '[a-z 0-9-]+';
        }else{
            var replacements = {
                ' ': '( |)',
                '-': '(-|)',
                'N': '[0-9]',
                'L': '[a-zA-Z]'
            };

            for (var new_value in replacements)
                pattern = pattern.split(new_value).join(replacements[new_value]);
        }
        var reg = new RegExp('^'+pattern+'$', 'i');
        return reg.test(str);
    },
    isCityName : function(str){
        var reg = /^[^!<>;?=+@#"°{}_$%]+$/;
        return reg.test(str);
    },
    isMessage : function(str){
        var reg = /^[^<>{}]+$/;
        return reg.test(str);
    },
    isPhoneNumber : function(str){
        var reg = /^[+0-9. ()-]+$/;
        return reg.test(str);
    },
    isDniLite : function (str){
        var reg = /^[0-9a-z-.]{1,16}$/i;
        return reg.test(s);
    },
    isEmail : function(str){
        var reg = unicode_hack(/^[a-z\p{L}0-9!#$%&'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+[._a-z\p{L}0-9-]*\.[a-z\p{L}0-9]+$/i, false);
        return reg.test(str);
    },
    isPasswd : function(str){
        return (str.length >= 5 && str.length < 255);
    },
    formatedNumberToFloat : function(price, currencyFormat, currencySign){
        price = price.replace(currencySign, '');
        if (currencyFormat === 1) {
            return parseFloat(price.replace(',', '').replace(' ', ''));
        }else if (currencyFormat === 2) {
            return parseFloat(price.replace(' ', '').replace(',', '.'));
        }else if (currencyFormat === 3) {
            return parseFloat(price.replace('.', '').replace(' ', '').replace(',', '.'));
        }else if (currencyFormat === 4) {
            return parseFloat(price.replace(',', '').replace(' ', ''));
        }
        return price;
    },
    formatNumber : function(value, numberOfDecimal, thousEndSeparator, virgule){
        value = value.toFixed(numberOfDecimal);
        var val_string = value+'';
        var tmp = val_string.split('.');
        var abs_val_string = (tmp.length === 2) ? tmp[0] : val_string;
        var decimal_string = ('0.' + (tmp.length === 2 ? tmp[1] : 0)).substr(2);
        var nb = abs_val_string.length;

        for (var i = 1 ; i < 4; i++)
            if (value >= Math.pow(10, (3 * i)))
                abs_val_string = abs_val_string.substring(0, nb - (3 * i)) + thousEndSeparator + abs_val_string.substring(nb - (3 * i));

        if (parseInt(numberOfDecimal) === 0)
            return abs_val_string;
        return abs_val_string + virgule + (decimal_string > 0 ? decimal_string : '00');
    },
    formatCurrency : function(price, currencyFormat, currencySign, currencyBlank){
        // if you modified this function, don't forget to modify the PHP function displayPrice (in the Tools.php class)
        var blank = '';
        price = parseFloat(price.toFixed(6));
        price = ps_round(price, priceDisplayPrecision);
        if (currencyBlank > 0)
            blank = ' ';
        if (currencyFormat == 1)
            return currencySign + blank + formatNumber(price, priceDisplayPrecision, ',', '.');
        if (currencyFormat == 2)
            return (formatNumber(price, priceDisplayPrecision, ' ', ',') + blank + currencySign);
        if (currencyFormat == 3)
            return (currencySign + blank + formatNumber(price, priceDisplayPrecision, '.', ','));
        if (currencyFormat == 4)
            return (formatNumber(price, priceDisplayPrecision, ',', '.') + blank + currencySign);
        if (currencyFormat == 5) {
            return (currencySign + blank + formatNumber(price, priceDisplayPrecision, '\'', '.'));
        }
        return price;
    },
    roundPrice : function(value, precision){
        if (typeof(roundMode) === 'undefined')
            roundMode = 2;
        if (typeof(precision) === 'undefined')
            precision = 2;

        var method = roundMode;
        if (method === 0)
            return ceilf(value, precision);
        else if (method === 1)
            return floorf(value, precision);
        var precisionFactor = precision === 0 ? 1 : Math.pow(10, precision);
        return Math.round(value * precisionFactor) / precisionFactor;
    },
    autoUrl : function(name, dest){
        var loc;
        var list_id;

        list_id = document.getElementById(name);
        loc = list_id.options[list_id.selectedIndex].value;
        if (loc != 0)
            location.href = dest+loc;
        return null;
    },
    autoUrlNoList : function(name, dest){
        var loc;
        loc = document.getElementById(name).checked;
        location.href = dest + (loc == true ? 1 : 0);
        return ;
    },
    toggle : function(e, show){
        e.style.display = show ? '' : 'none';
    },
    toggleMultiple : function(tab){
        var len = tab.length;

        for (var i = 0; i < len; i++)
            if (tab[i].style)
                toggle(tab[i], tab[i].style.display == 'none');
    },
    showElemFromSelect : function (select_id, elem_id){
        var select = document.getElementById(select_id);
        for (var i = 0; i < select.length; ++i){
            var elem = document.getElementById(elem_id + select.options[i].value);
            if (elem != null)
                toggle(elem, i == select.selectedIndex);
        }
    },
    openCloseAllDiv : function(name, option)
    {
        var tab = $('*[name='+name+']');
        for (var i = 0; i < tab.length; ++i)
            toggle(tab[i], option);
    },
    toggleDiv : function(name, option){
        $('*[name='+name+']').each(function(){
            if (option == 'open'){
                $('#buttonall').data('status', 'close');
                $(this).hide();
            } else{
                $('#buttonall').data('status', 'open');
                $(this).show();
            }
        })
    },
    toggleButtonValue : function(button_id, text1, text2){
        var buttonWrapper = $('#'+button_id);
        if (buttonWrapper.find('i').first().hasClass('process-icon-compress')){
            buttonWrapper.find('i').first().removeClass('process-icon-compress').addClass('process-icon-expand');
            buttonWrapper.find('span').first().html(text1);
        }else{
            buttonWrapper.find('i').first().removeClass('process-icon-expand').addClass('process-icon-compress');
            buttonWrapper.find('span').first().html(text2);
        }
    },
    toggleElemValue : function(button_id, text1, text2){
        var obj = document.getElementById(button_id);
        if (obj)
            obj.value = ((!obj.value || obj.value == text2) ? text1 : text2);
    },
    addBookmark : function(url, title){
        if (window.sidebar && window.sidebar.addPanel)
            return window.sidebar.addPanel(title, url, "");
        else if ( window.external && ('AddFavorite' in window.external))
            return window.external.AddFavorite( url, title);
    },
    writeBookmarkLink : function(url, title, text, img){
        var insert = '';
        if (img)
            insert = writeBookmarkLinkObject(url, title, '<img src="' + img + '" alt="' + escape(text) + '" title="' + removeQuotes(text) + '" />') + '&nbsp';
        insert += writeBookmarkLinkObject(url, title, text);
        if (window.sidebar || window.opera && window.print || (window.external && ('AddFavorite' in window.external)))
            $('.add_bookmark, #header_link_bookmark').append(insert);
    },
    writeBookmarkLinkObject : function(url, title, insert){
        if (window.sidebar || window.external)
            return ('<a href="javascript:addBookmark(\'' + escape(url) + '\', \'' + removeQuotes(title) + '\')">' + insert + '</a>');
        else if (window.opera && window.print)
            return ('<a rel="sidebar" href="' + escape(url) + '" title="' + removeQuotes(title) + '">' + insert + '</a>');
        return ('');
    },
    ceilf : function(value, precision){
        if (typeof(precision) === 'undefined')
            precision = 0;
        var precisionFactor = precision === 0 ? 1 : Math.pow(10, precision);
        var tmp = value * precisionFactor;
        var tmp2 = tmp.toString();
        if (tmp2[tmp2.length - 1] === 0)
            return value;
        return Math.ceil(value * precisionFactor) / precisionFactor;
    },
    floorf : function(value, precision){
        if (typeof(precision) === 'undefined')
            precision = 0;
        var precisionFactor = precision === 0 ? 1 : Math.pow(10, precision);
        var tmp = value * precisionFactor;
        var tmp2 = tmp.toString();
        if (tmp2[tmp2.length - 1] === 0)
            return value;
        return Math.floor(value * precisionFactor) / precisionFactor;
    },
    setCurrency : function(currency_id){
        $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: baseDir + 'index.php' + '?rand=' + new Date().getTime(),
            data: 'controller=change-currency&currency_id='+ parseInt(currency_id),
            success: function(msg){
                location.reload(true);
            }
        });
    },
    removeQuotes : function(value){
        value = value.replace(/\\"/g, '');
        value = value.replace(/"/g, '');
        value = value.replace(/\\'/g, '');
        value = value.replace(/'/g, '');

        return value;
    },
    sprintf : function(format){
        for(var i=1; i < arguments.length; i++)
            format = format.replace(/%s/, arguments[i]);

        return format;
    },
    fancyMessageBox : function(msg, title){
        if (title) msg = "<h2>" + title + "</h2><p>" + msg + "</p>";
        msg += "<br/><p class=\"submit\" style=\"text-align:right; padding-bottom: 0\"><input class=\"button\" type=\"button\" value=\"OK\" onclick=\"$.fancybox.close();\" /></p>";
        if(!!$.prototype.fancybox)
            $.fancybox( msg, {'autoDimensions': false, 'autoSize': false, 'width': 500, 'height': 'auto', 'openEffect': 'none', 'closeEffect': 'none'} );
    },
    fancyChooseBox : function(question, title, buttons, otherParams){
        var msg, funcName, action;
        msg = '';
        if (title)
            msg = "<h2>" + title + "</h2><p>" + question + "</p>";
        msg += "<br/><p class=\"submit\" style=\"text-align:right; padding-bottom: 0\">";
        var i = 0;
        for (var caption in buttons) {
            if (!buttons.hasOwnProperty(caption)) continue;
            funcName = buttons[caption];
            if (typeof otherParams == 'undefined') otherParams = 0;
            otherParams = escape(JSON.stringify(otherParams));
            action = funcName ? "$.fancybox.close();window['" + funcName + "'](JSON.parse(unescape('" + otherParams + "')), " + i + ")" : "$.fancybox.close()";
            msg += '<button type="submit" class="button btn-default button-medium" style="margin-right: 5px;" value="true" onclick="' + action + '" >';
            msg += '<span>' + caption + '</span></button>'
            i++;
        }
        msg += "</p>";
        if(!!$.prototype.fancybox)
            $.fancybox(msg, {'autoDimensions': false, 'width': 500, 'height': 'auto', 'openEffect': 'none', 'closeEffect': 'none'});
    },
    toggleLayer : function(whichLayer, flag){
        if (!flag)
            $(whichLayer).hide();
        else
            $(whichLayer).show();
    },
    openCloseLayer : function(whichLayer, action){
        if (!action)
        {
            if ($(whichLayer).css('display') == 'none')
                $(whichLayer).show();
            else
                $(whichLayer).hide();
        }
        else if (action == 'open')
            $(whichLayer).show();
        else if (action == 'close')
            $(whichLayer).hide();
    },
    updateTextWithEffect : function(jQueryElement, text, velocity, effect1, effect2, newClass){
        if(jQueryElement.text() !== text)
        {
            if(effect1 === 'fade')
                jQueryElement.fadeOut(velocity, function(){
                    $(this).addClass(newClass);
                    if(effect2 === 'fade') $(this).text(text).fadeIn(velocity);
                    else if(effect2 === 'slide') $(this).text(text).slideDown(velocity);
                    else if(effect2 === 'show')	$(this).text(text).show(velocity, function(){});
                });
            else if(effect1 === 'slide')
                jQueryElement.slideUp(velocity, function(){
                    $(this).addClass(newClass);
                    if(effect2 === 'fade') $(this).text(text).fadeIn(velocity);
                    else if(effect2 === 'slide') $(this).text(text).slideDown(velocity);
                    else if(effect2 === 'show')	$(this).text(text).show(velocity);
                });
            else if(effect1 === 'hide')
                jQueryElement.hide(velocity, function(){
                    $(this).addClass(newClass);
                    if(effect2 === 'fade') $(this).text(text).fadeIn(velocity);
                    else if(effect2 === 'slide') $(this).text(text).slideDown(velocity);
                    else if(effect2 === 'show')	$(this).text(text).show(velocity);
                });
        }
    },
    dbg : function(value){
        var active = false;//true for active
        var firefox = true;//true if debug under firefox

        if (active)
            if (firefox)
                console.log(value);
            else
                alert(value);
    },
    printArray : function(arr, level){
        var dumped_text = "";
        if (!level)
            level = 0;

        //The padding given at the beginning of the line.
        var level_padding = "";
        for (var j = 0 ; j < level + 1; j++)
            level_padding += "    ";

        if (typeof(arr) === 'object'){ //Array/Hashes/Objects
            for (var item in arr){
                var value = arr[item];
                if (typeof(value) === 'object') { //If it is an array,
                    dumped_text += level_padding + "'" + item + "' ...\n";
                    dumped_text += dump(value,level+1);
                }
                else
                {
                    dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
                }
            }
        }
        else
        { //Stings/Chars/Numbers etc.
            dumped_text = "===>" + arr + "<===("+typeof(arr)+")";
        }
        return dumped_text;
    },
    inArray : function(value, array){
        for (var i in array)
            if ((array[i] + '') === (value + ''))
                return true;
        return false;
    },
    isCleanHtml : function(content){
        var events = 'onmousedown|onmousemove|onmmouseup|onmouseover|onmouseout|onload|onunload|onfocus|onblur|onchange';
        events += '|onsubmit|ondblclick|onclick|onkeydown|onkeyup|onkeypress|onmouseenter|onmouseleave|onerror|onselect|onreset|onabort|ondragdrop|onresize|onactivate|onafterprint|onmoveend';
        events += '|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onmove';
        events += '|onbounce|oncellchange|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondeactivate|ondrag|ondragend|ondragenter|onmousewheel';
        events += '|ondragleave|ondragover|ondragstart|ondrop|onerrorupdate|onfilterchange|onfinish|onfocusin|onfocusout|onhashchange|onhelp|oninput|onlosecapture|onmessage|onmouseup|onmovestart';
        events += '|onoffline|ononline|onpaste|onpropertychange|onreadystatechange|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onsearch|onselectionchange';
        events += '|onselectstart|onstart|onstop';

        var script1 = /<[\s]*script/im;
        var script2 = new RegExp('('+events+')[\s]*=', 'im');
        var script3 = /.*script\:/im;
        var script4 = /<[\s]*(i?frame|embed|object)/im;

        if(script1.test(content) || script2.test(content) || script3.test(content) || script4.test(content)) {
            return false;
        }
        return true;
    },
    responsiveResize : function(){
        compensante = scrollCompensate();
        if (($(window).width()+scrollCompensate()) <= 767 && responsiveflag == false)
        {
            accordion('enable');
            accordionFooter('enable');
            responsiveflag = true;
        }
        else if (($(window).width()+scrollCompensate()) >= 768)
        {
            accordion('disable');
            accordionFooter('disable');
            responsiveflag = false;
        }
        if (typeof page_name != 'undefined' && in_array(page_name, ['category']))
            resizeCatimg();
    },
    blockHover : function(status){
        $(document).off('mouseenter').on('mouseenter', '.product_list.grid li.ajax_block_product .product-container', function(e){

            if ($('body').find('.container').width() == 1170)
            {
                var pcHeight = $(this).parent().outerHeight();
                var pcPHeight = $(this).parent().find('.button-container').outerHeight() + $(this).parent().find('.comments_note').outerHeight() + $(this).parent().find('.functional-buttons').outerHeight();
                $(this).parent().addClass('hovered').css({'height':pcHeight + pcPHeight, 'margin-bottom':pcPHeight * (-1)});
            }
        });

        $(document).off('mouseleave').on('mouseleave', '.product_list.grid li.ajax_block_product .product-container', function(e){
            if ($('body').find('.container').width() == 1170)
                $(this).parent().removeClass('hovered').css({'height':'auto', 'margin-bottom':'0'});
        });
    },
    quickView : function(){
        $(document).on('click', '.quick-view:visible, .quick-view-mobile:visible', function(e)
        {
            e.preventDefault();
            var url = this.rel;
            if (url.indexOf('?') != -1)
                url += '&';
            else
                url += '?';

            if (!!$.prototype.fancybox)
                $.fancybox({
                    'padding':  0,
                    'width':    1087,
                    'height':   610,
                    'type':     'iframe',
                    'href':     url + 'content_only=1'
                });
        });
    },
    bindGrid : function(){
        var view = $.totalStorage('display');

        if (!view && (typeof displayList != 'undefined') && displayList)
            view = 'list';

        if (view && view != 'grid')
            display(view);
        else
            $('.display').find('li#grid').addClass('selected');

        $(document).on('click', '#grid', function(e){
            e.preventDefault();
            display('grid');
        });

        $(document).on('click', '#list', function(e){
            e.preventDefault();
            display('list');
        });
    },
    display : function(view){
        if (view == 'list')
        {
            $('ul.product_list').removeClass('grid').addClass('list row');
            $('.product_list > li').removeClass('col-xs-12 col-sm-6 col-md-4').addClass('col-xs-12');
            $('.product_list > li').each(function(index, element) {
                html = '';
                html = '<div class="product-container"><div class="row">';
                html += '<div class="left-block col-xs-4 col-xs-5 col-md-4">' + $(element).find('.left-block').html() + '</div>';
                html += '<div class="center-block col-xs-4 col-xs-7 col-md-4">';
                html += '<div class="product-flags">'+ $(element).find('.product-flags').html() + '</div>';
                html += '<h5 itemprop="name">'+ $(element).find('h5').html() + '</h5>';
                var rating = $(element).find('.comments_note').html(); // check : rating
                if (rating != null) {
                    html += '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="comments_note">'+ rating + '</div>';
                }
                html += '<p class="product-desc">'+ $(element).find('.product-desc').html() + '</p>';
                var colorList = $(element).find('.color-list-container').html();
                if (colorList != null) {
                    html += '<div class="color-list-container">'+ colorList +'</div>';
                }
                var availability = $(element).find('.availability').html();	// check : catalog mode is enabled
                if (availability != null) {
                    html += '<span class="availability">'+ availability +'</span>';
                }
                html += '</div>';
                html += '<div class="right-block col-xs-4 col-xs-12 col-md-4"><div class="right-block-content row">';
                var price = $(element).find('.content_price').html();       // check : catalog mode is enabled
                if (price != null) {
                    html += '<div class="content_price col-xs-5 col-md-12">'+ price + '</div>';
                }
                html += '<div class="button-container col-xs-7 col-md-12">'+ $(element).find('.button-container').html() +'</div>';
                html += '<div class="functional-buttons clearfix col-sm-12">' + $(element).find('.functional-buttons').html() + '</div>';
                html += '</div>';
                html += '</div></div>';
                $(element).html(html);
            });
            $('.display').find('li#list').addClass('selected');
            $('.display').find('li#grid').removeAttr('class');
            $.totalStorage('display', 'list');
        }
        else
        {
            $('ul.product_list').removeClass('list').addClass('grid row');
            $('.product_list > li').removeClass('col-xs-12').addClass('col-xs-12 col-sm-6 col-md-4');
            $('.product_list > li').each(function(index, element) {
                html = '';
                html += '<div class="product-container">';
                html += '<div class="left-block">' + $(element).find('.left-block').html() + '</div>';
                html += '<div class="right-block">';
                html += '<div class="product-flags">'+ $(element).find('.product-flags').html() + '</div>';
                html += '<h5 itemprop="name">'+ $(element).find('h5').html() + '</h5>';
                var rating = $(element).find('.comments_note').html(); // check : rating
                if (rating != null) {
                    html += '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="comments_note">'+ rating + '</div>';
                }
                html += '<p itemprop="description" class="product-desc">'+ $(element).find('.product-desc').html() + '</p>';
                var price = $(element).find('.content_price').html(); // check : catalog mode is enabled
                if (price != null) {
                    html += '<div class="content_price">'+ price + '</div>';
                }
                html += '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="button-container">'+ $(element).find('.button-container').html() +'</div>';
                var colorList = $(element).find('.color-list-container').html();
                if (colorList != null) {
                    html += '<div class="color-list-container">'+ colorList +'</div>';
                }
                var availability = $(element).find('.availability').html(); // check : catalog mode is enabled
                if (availability != null) {
                    html += '<span class="availability">'+ availability +'</span>';
                }
                html += '</div>';
                html += '<div class="functional-buttons clearfix">' + $(element).find('.functional-buttons').html() + '</div>';
                html += '</div>';
                $(element).html(html);
            });
            $('.display').find('li#grid').addClass('selected');
            $('.display').find('li#list').removeAttr('class');
            $.totalStorage('display', 'grid');
        }
    },
    dropDown : function(){
        elementClick = '#header .current';
        elementSlide =  'ul.toogle_content';
        activeClass = 'active';

        $(elementClick).on('click', function(e){
            e.stopPropagation();
            var subUl = $(this).next(elementSlide);
            if(subUl.is(':hidden'))
            {
                subUl.slideDown();
                $(this).addClass(activeClass);
            }
            else
            {
                subUl.slideUp();
                $(this).removeClass(activeClass);
            }
            $(elementClick).not(this).next(elementSlide).slideUp();
            $(elementClick).not(this).removeClass(activeClass);
            e.preventDefault();
        });

        $(elementSlide).on('click', function(e){
            e.stopPropagation();
        });

        $(document).on('click', function(e){
            e.stopPropagation();
            var elementHide = $(elementClick).next(elementSlide);
            $(elementHide).slideUp();
            $(elementClick).removeClass('active');
        });
    },
    accordionFooter : function(status){
        if(status == 'enable')
        {
            $('#footer .footer-block h4').on('click', function(){
                $(this).toggleClass('active').parent().find('.toggle-footer').stop().slideToggle('medium');
            })
            $('#footer').addClass('accordion').find('.toggle-footer').slideUp('fast');
        }
        else
        {
            $('.footer-block h4').removeClass('active').off().parent().find('.toggle-footer').removeAttr('style').slideDown('fast');
            $('#footer').removeClass('accordion');
        }
    },
    accordion : function(status){
        leftColumnBlocks = $('#left_column');
        if(status == 'enable')
        {
            $('#right_column .block .title_block, #left_column .block .title_block, #left_column #newsletter_block_left h4').on('click', function(){
                $(this).toggleClass('active').parent().find('.block_content').stop().slideToggle('medium');
            })
            $('#right_column, #left_column').addClass('accordion').find('.block .block_content').slideUp('fast');
        }
        else
        {
            $('#right_column .block .title_block, #left_column .block .title_block, #left_column #newsletter_block_left h4').removeClass('active').off().parent().find('.block_content').removeAttr('style').slideDown('fast');
            $('#left_column, #right_column').removeClass('accordion');
        }
    },
    resizeCategoryImage : function(){
        var div = $('.cat_desc').parent('div');
        var image = new Image;
        $(image).load(function(){
            var width  = image.width;
            var height = image.height;
            var ratio = parseFloat(height / width);
            var calc = Math.round(ratio * parseInt(div.outerWidth(false)));
            div.css('min-height', calc);
        });
        if (div.length)
            image.src = div.css('background-image').replace(/url\("?|"?\)$/ig, '');
    }
});