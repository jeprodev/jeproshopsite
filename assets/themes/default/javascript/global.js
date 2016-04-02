/**
 * Created by jeproQxT on 09/02/15.
 */

var responsiveFlag = false;

jQuery(document).ready(function(){
    highDpiInit();
    responsiveResize();
    jQuery(window).resize(responsiveResize);
    if(navigator.userAgent.match(/Android/i)){
        var view_port = document.querySelector('meta[name="viewprot"]');
        view_port.setAttribute('content', 'initial-scale=1.0, maximum-scale=1.0, user-scalable=0, width=device-width, height=device-height');
        view_port.scrollTo(0, 1);
    }
    blockHover();
    if(typeof quick_view !== 'undefined' && quick_view){ quickView(); }
    dropDown();

    if(typeof page_name != 'undefined' && !in_array(page_name, ['', 'product'])){
        bindGrid();
        jQuery(document).on('change','.select_product_sort', function(evt){
            if(typeof request != 'undefined' && request){
                var productsSortRequest = request;
            }
            var splitData = jQuery(this).val().split(':');
            if(typeof productsSortRequest != 'undefined' && productsSortRequest){
                document.location.href = productsSortRequest + ((productsSortRequest.indexOf('?') < 0 ) ? '?option=com_jeproshop&' : '&') + 'order_by=' + splitData[0] + '&order_way=' + splitData[1];
            }
        });

        jQuery(document).on('change', 'select[name="n"]', function(){ jQuery(this.form).submit(); });

        jQuery(document).on('change', 'select[name="manufacturer_list"], select[name="supplier_list"]', function() {
            if (this.value != ''){  location.href = this.value; }
        });

        jQuery(document).on('change', 'select[name="currency_payment"]', function(){ setCurrency(jQuery(this).val()); });
    }

    jQuery(document).on('click', '.back', function(e){
        e.preventDefault();
        history.back();
    });

    jQuery.curCSS = jQuery.css;
    if (!!jQuery.prototype.cluetip){
        jQuery('a.cluetip').cluetip({ local:true, cursor: 'pointer', dropShadow: false, dropShadowSteps: 0, showTitle: false,
            tracking: true, sticky: false, mouseOutClose: true, fx: { open: 'fadeIn', openSpeed:  'fast' }
        }).css('opacity', 0.8);
    }
    if (!!jQuery.prototype.fancybox){
        /*jQuery.extend(jQuery.fancybox.defaults.tpl, {
            closeBtn : '<a title="' + FancyboxI18nClose + '" class="fancybox-item fancybox-close" href="javascript:;"></a>',
            next     : '<a title="' + FancyboxI18nNext + '" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
            prev     : '<a title="' + FancyboxI18nPrev + '" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>'
        }); */
    }
});

function highDpiInit(){
    if(jQuery(".replace-2x").css('font-size') == "1px"){
        var elts = jQuery("img.replace-2x").get();
        for(var i = 0; i < elts.length;  i++){
            src = elts[i].src;
            extension = src.substr( (src.lastIndexOf('.') +1) );
            src = src.replace("." + extension, "2x." + extension);
            var img = new Image();
            img.src = src;
            img.height != 0 ? elts[i].src = src : elts[i].src = els[i].src;
        }
    }
}

function responsiveResize(){
    compensate = scrollCompensate();
    if((jQuery(window).width() + scrollCompensate()) <= 767 && responsiveFlag == false){
        accordion('enable')
        responsiveFlag = true
    }else{
        accordion('disable');
        responsiveFlag = true;
    }

    if(typeof page_mame != 'undefined' && in_array(page_name, ['category'])){ resizeCategoryImage(); }
}

function blockHover(status){
    jQuery(document).off('mouseenter').on('mouseenter', '.product_list.grid li.ajax_block_product .product_container', function(e){

        if (jQuery('body').find('.container').width() == 1170) {
            var pcHeight = jQuery(this).parent().outerHeight();
            var pcPHeight = jQuery(this).parent().find('.button-container').outerHeight() + jQuery(this).parent().find('.comments_note').outerHeight() + jQuery(this).parent().find('.functional-buttons').outerHeight();
            jQuery(this).parent().addClass('hovered').css({'height':pcHeight + pcPHeight, 'margin-bottom':pcPHeight * (-1)});
        }
    });

    jQuery(document).off('mouseleave').on('mouseleave', '.product_list.grid li.ajax_block_product .product-container', function(e){
        if (jQuery('body').find('.container').width() == 1170)
            jQuery(this).parent().removeClass('hovered').css({'height':'auto', 'margin-bottom':'0'});
    });
}

function quickView(){
    jQuery(document).on('click', '.quick_view:visible, .quick_view_mobile:visible', function(e){
        e.preventDefault();
        var url = this.rel;
        if (url.indexOf('?') != -1)
            url += '&';
        else
            url += '?option=com_jeproshop';

        if (!!jQuery.prototype.fancybox)
            jQuery.fancybox({ 'padding':  0, 'width': 1087, 'height': 610, 'type': 'iframe', 'href':  url + 'content_only=1' });
    });
}

function bindGrid()
{
    var view = jQuery.totalStorage('display');

    if (!view && (typeof displayList != 'undefined') && displayList)
        view = 'list';

    if (view && view != 'grid')
        display(view);
    else
        jQuery('.display').find('li#grid').addClass('selected');

    jQuery(document).on('click', '#grid', function(e){
        e.preventDefault();
        display('grid');
    });

    jQuery(document).on('click', '#list', function(e){
        e.preventDefault();
        display('list');
    });
}

function display(view)
{
    if (view == 'list')
    {
        jQuery('ul.product_list').removeClass('grid').addClass('list row');
        jQuery('.product_list > li').removeClass('col-xs-12 col-sm-6 col-md-4').addClass('col-xs-12');
        jQuery('.product_list > li').each(function(index, element) {
            html = '';
            html = '<div class="product-container"><div class="row">';
            html += '<div class="left-block col-xs-4 col-xs-5 col-md-4">' + jQuery(element).find('.left-block').html() + '</div>';
            html += '<div class="center-block col-xs-4 col-xs-7 col-md-4">';
            html += '<div class="product-flags">'+ jQuery(element).find('.product-flags').html() + '</div>';
            html += '<h5 itemprop="name">'+ jQuery(element).find('h5').html() + '</h5>';
            var rating = jQuery(element).find('.comments_note').html(); // check : rating
            if (rating != null) {
                html += '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="comments_note">'+ rating + '</div>';
            }
            html += '<p class="product-desc">'+ jQuery(element).find('.product-desc').html() + '</p>';
            var colorList = jQuery(element).find('.color-list-container').html();
            if (colorList != null) {
                html += '<div class="color-list-container">'+ colorList +'</div>';
            }
            var availability = jQuery(element).find('.availability').html();	// check : catalog mode is enabled
            if (availability != null) {
                html += '<span class="availability">'+ availability +'</span>';
            }
            html += '</div>';
            html += '<div class="right-block col-xs-4 col-xs-12 col-md-4"><div class="right-block-content row">';
            var price = jQuery(element).find('.content_price').html();       // check : catalog mode is enabled
            if (price != null) {
                html += '<div class="content_price col-xs-5 col-md-12">'+ price + '</div>';
            }
            html += '<div class="button-container col-xs-7 col-md-12">'+ jQuery(element).find('.button-container').html() +'</div>';
            html += '<div class="functional-buttons clearfix col-sm-12">' + jQuery(element).find('.functional-buttons').html() + '</div>';
            html += '</div>';
            html += '</div></div>';
            jQuery(element).html(html);
        });
        jQuery('.display').find('li#list').addClass('selected');
        jQuery('.display').find('li#grid').removeAttr('class');
        jQuery.totalStorage('display', 'list');
    }
    else
    {
        jQuery('ul.product_list').removeClass('list').addClass('grid row');
        jQuery('.product_list > li').removeClass('col-xs-12').addClass('col-xs-12 col-sm-6 col-md-4');
        jQuery('.product_list > li').each(function(index, element) {
            html = '';
            html += '<div class="product-container">';
            html += '<div class="left-block">' + jQuery(element).find('.left-block').html() + '</div>';
            html += '<div class="right-block">';
            html += '<div class="product-flags">'+ jQuery(element).find('.product-flags').html() + '</div>';
            html += '<h5 itemprop="name">'+ jQuery(element).find('h5').html() + '</h5>';
            var rating = jQuery(element).find('.comments_note').html(); // check : rating
            if (rating != null) {
                html += '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="comments_note">'+ rating + '</div>';
            }
            html += '<p itemprop="description" class="product-desc">'+ jQuery(element).find('.product-desc').html() + '</p>';
            var price = jQuery(element).find('.content_price').html(); // check : catalog mode is enabled
            if (price != null) {
                html += '<div class="content_price">'+ price + '</div>';
            }
            html += '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="button-container">'+ jQuery(element).find('.button-container').html() +'</div>';
            var colorList = jQuery(element).find('.color-list-container').html();
            if (colorList != null) {
                html += '<div class="color-list-container">'+ colorList +'</div>';
            }
            var availability = jQuery(element).find('.availability').html(); // check : catalog mode is enabled
            if (availability != null) {
                html += '<span class="availability">'+ availability +'</span>';
            }
            html += '</div>';
            html += '<div class="functional-buttons clearfix">' + jQuery(element).find('.functional-buttons').html() + '</div>';
            html += '</div>';
            jQuery(element).html(html);
        });
        jQuery('.display').find('li#grid').addClass('selected');
        jQuery('.display').find('li#list').removeAttr('class');
        jQuery.totalStorage('display', 'grid');
    }
}

function dropDown(){
    elementClick = '#header .current';
    elementSlide =  'ul.toogle_content';
    activeClass = 'active';

    jQuery(elementClick).on('click', function(e){
        e.stopPropagation();
        var subUl = jQuery(this).next(elementSlide);
        if(subUl.is(':hidden'))
        {
            subUl.slideDown();
            jQuery(this).addClass(activeClass);
        }
        else
        {
            subUl.slideUp();
            jQuery(this).removeClass(activeClass);
        }
        jQuery(elementClick).not(this).next(elementSlide).slideUp();
        jQuery(elementClick).not(this).removeClass(activeClass);
        e.preventDefault();
    });

    jQuery(elementSlide).on('click', function(e){
        e.stopPropagation();
    });

    jQuery(document).on('click', function(e){
        e.stopPropagation();
        var elementHide = jQuery(elementClick).next(elementSlide);
        jQuery(elementHide).slideUp();
        jQuery(elementClick).removeClass('active');
    });
}

function accordion(status)
{
    leftColumnBlocks = jQuery('#left_column');
    if(status == 'enable')
    {
        jQuery('#right_column .block .title_block, #left_column .block .title_block, #left_column #newsletter_block_left h4').on('click', function(){
            jQuery(this).toggleClass('active').parent().find('.block_content').stop().slideToggle('medium');
        })
        jQuery('#right_column, #left_column').addClass('accordion').find('.block .block_content').slideUp('fast');
    }
    else
    {
        jQuery('#right_column .block .title_block, #left_column .block .title_block, #left_column #newsletter_block_left h4').removeClass('active').off().parent().find('.block_content').removeAttr('style').slideDown('fast');
        jQuery('#left_column, #right_column').removeClass('accordion');
    }
}

function resizeCategoryImage()
{
    var div = jQuery('.cat_desc').parent('div');
    var image = new Image;
    jQuery(image).load(function(){
        var width  = image.width;
        var height = image.height;
        var ratio = parseFloat(height / width);
        var calc = Math.round(ratio * parseInt(div.outerWidth(false)));
        div.css('min-height', calc);
    });
    if (div.length)
        image.src = div.css('background-image').replace(/url\("?|"?\)jQuery/ig, '');
}

function scrollCompensate(){
    var inner = document.createElement('p');
    inner.style.width = "100%";
    inner.style.height = "200px";

    var outer = document.createElement('div');
    outer.style.position = "absolute";
    outer.style.top = "0px";
    outer.style.left = "0px";
    outer.style.visibility = "hidden";
    outer.style.width = "200px";
    outer.style.height = "150px";
    outer.style.overflow = "hidden";
    outer.appendChild(inner);

    document.body.appendChild(outer);
    var w1 = inner.offsetWidth;
    outer.style.overflow = 'scroll';
    var w2 = inner.offsetWidth;
    if (w1 == w2) w2 = outer.clientWidth;

    document.body.removeChild(outer);

    return (w1 - w2);
}
