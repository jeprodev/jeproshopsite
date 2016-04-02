/**
 * Created by jeproQxT on 26/07/2015.
 */
jQuery.noConflict();
(function($) {
    $.fn.JeproshopAuthentication = function (opts) {
        //setting default options
        var defaults = {};
        var currentEmail = '';
        var options = $.extend(defaults, opts);
        var jeproTool = new JeproshopTools({});

        $('#create_account_form').submit(function(evt){
            evt.stopPropagation();
            submitFunction();
            evt.preventDefault();
        });

        $('#jform_account_creation_form').submit(function(evt){
            evt.stopPropagation();
            registerFunction();
            evt.preventDefault();
        });

        function registerFunction(){
            var customerFirstName = $('#jform_customer_firstname');     console.log('first name ' + customerFirstName.val());
            var customerLastName = $('#jform_customer_lastname');       console.log("last name " + customerLastName.val());
            var customerEmail = $('#jform_customer_email');             console.log('email ' + customerEmail.val());
            var customerPassword = $('#jform_customer_passwd');         console.log('passwd ' + customerPassword.val());
            var birthDay = $('#jform_customer_days');                   console.log('day ' + birthDay.find(':selected').val());
            var birthMonth = $('#jform_customer_months');               console.log('Month ' + birthMonth.find(':selected').val());
            var birthYear = $('#jform_customer_years');                 console.log('year' + birthYear.find(":selected").val());
            var newsLetter = $('#jform_customer_newsletter');           console.log('nesletter ' + newsLetter.is(':checked'));
            var optin = $('#jform_customer_option');                    console.log('optin :' + optin.is(':checked'));
             /*
            /** case b2b is enabled ** /
            var company = $('#jform_company');
            var siret = $('#jform_siret');
            var ape = $('#jform_ape');
            var webSite = $('#jform_website');
            var vatNumber = $('#jform_vat_number');
            var customerAddress1 = $('#jform_address1');
            var customerAddress2 = $('#jform_address2');
            var zipPostalCode = $('#jform_postcode');
            var city = $('#jform_city');
            var country = $('#jform_country_id');
            var state = $('#jform_state_id');
            var otherInformation = $('#jform_other');
            var homePhone = $('#jform_phone');
            var mobilePhone = $('#jform_phone_mobile');
            var addressAlias = $('#jform_alias');
            var taxIdentification = $('#jform_dni');

            /*if(typeof customerFirstName != 'undefined' && jeproTool.nameValidation(customerFirstName.val())){}
            if(typeof customerLastName != 'undefined' && jeproTool.nameValidation(customerLastName.val())){}
            if(typeof customerEmail != 'undefined' && jeproTool.emailValidation(customerEmail.val())){}
            if(typeof customerPassword  != 'undefined' && jeproTool.passwordValidation(customerPassword.val())){}*/

            var data = "&firstname=" + customerFirstName.val() + "&lastname=" + customerLastName.val() + "&email=" + customerEmail.val() + "&passwd=" + customerPassword.val() + "&day=" + birthDay.find(":selected").val();
            data += "&month=" + birthMonth.find(":selected").val() + "&year=" + birthYear.find(":selected").val() + "&newsletter=" + (newsLetter.is(':checked') ? 1 : 0) + "&optin=" + (optin.is(':checked') ? 1 : 0) + "&is_new_customer=1";
            $.ajax({
                type : "POST",
                url : 'index.php?option=com_jeproshop&view=authentication&use_ajax=1&task=register',
                async: true,
                cache: false,
                dataType : "json",
                data : data,
                encode :  true,
                success: function(jsonData){
console.log(data);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown){
                    Joomla.renderMessages(errorThrown);  console.log('an  error occurred');
                }
            });
        }

        function submitFunction(){
            $('#create_account_error').html('').hide();

            $.ajax({
                type: "POST",
                url: 'index.php?option=com_jeproshop&view=authentication&use_ajax=1&task=create', //&email_create=' + $('#jform_email_create').val() ,
                async: true,
                cache: false,
                dataType : "json",
                data : "email_create=" + $('#jform_email_create').val(),
                encode :  true,
                success: function(jsonData){
                    if(jsonData.has_error){
                        var errors = '';
                        for(var error in jsonData.errors)
                            //IE6 bug fix
                            if(error != 'indexOf')
                                errors += '<li>' + jsonData.errors[error] + '</li>';
                        $('#jform_create_account_error').html('<ol>' + errors + '</ol>').show();
                    }else{
                        currentEmail = jsonData.email;
                        $('#jform_checker_login_wrapper').fadeOut('slow', function(){

                        });
                        $('#jform_account_creation_form').css("display", "block");
                        /*// adding a div to display a transition
                        $('#center_column').html('<div id="noSlide">' + $('#center_column').html() + '</div>');
                        $('#noSlide').fadeOut('slow', function()
                        {
                            $('#noSlide').html(jsonData.page);
                            $(this).fadeIn('slow', function()
                            {
                                if (typeof bindUniform !=='undefined')
                                    bindUniform();
                                if (typeof bindStateInputAndUpdate !=='undefined')
                                    bindStateInputAndUpdate();
                                document.location = '#account-creation';
                            });
                        }); */
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown){
                    /*if (jsonData.has_errors){ //console.log(errorThrown);
                 /* var error = "TECHNICAL ERROR: unable to load form.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
                    if (!!$.prototype.fancybox){
                        $.fancybox.open([
                                {
                                    type: 'inline',
                                    autoScale: true,
                                    minHeight: 30,
                                    content: "<p class='fancybox-error'>" + error + '</p>'
                                }],
                            {
                                padding: 0
                            });
                    }
                    else
                        alert(error);*/
                }
            });
            return false;
        }
    };
})(jQuery);