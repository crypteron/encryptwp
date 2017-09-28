var TrestianCore;

jQuery(document).ready(function($) {

    var alertContainer = $('<div id="twpm-alert-success" class="twpm-alert twpm-alert-success"></div><div id="twpm-alert-error" class="twpm-alert twpm-alert-error"></div>');

    /**
     * Generic error message
     * @param msg
     * @returns {string}
     */
    var genericError = function(msg){
        var error = 'An error has occurred.  Please <a href="/contact">contact support</a>.';
        if(msg){
            error += ' Error details: ' + msg;
        }

        return error;
    };


    /**
     * Hide all alerts
     */
    var hideAlerts = function(){
      $('.twpm-alert').text('').hide();
    };


    /**
     * Display a loading animation
     */
    var showLoading = function(){
        $('body').append('<div class="twpm-loading"></div>');
    };

    /**
     * Hide a loading animation
     */
    var hideLoading = function(){
        $('.twpm-loading').hide();
    };

    /**
     * Handle an AJAX success response with an alert and optional redirect
     * @param response
     * @returns {boolean}
     */
    var success = function(response){
        if(!response.hasOwnProperty('success')){
            $('#twpm-alert-error').html(genericError('No success in AJAX response')).fadeIn();
            return false;
        }
        if(!response.success){
            // Operation was not successful.  Display error message or default
            msg = response.message ? response.message : genericError('No error message returned');
            $('#twpm-alert-error').html(msg).fadeIn();
            return false;
        }

        // Display any response messages
        if(response.message){
            $('#twpm-alert-success').html(response.message).fadeIn();
        }

        // Redirect to page
        if(response.redirect){
            location.href = response.redirect;
        }

        return true;
    };

    /**
     * Before an AJAX submission, hide fields and show loading indicator
     * @param args
     */
    var beforeSubmit = function(args){
        var defaults = {
            form: null,
            fieldsSelector: null
        };
        args = $.extend({}, defaults, args);

        hideAlerts();

        if(args.form != null){
            args.form.find('input, button').prop('disabled', true);
        }

        if(args.fieldsSelector != null){
            $(args.fieldsSelector).prop('disabled', true);
        }

        showLoading();
    };

    /**
     * On an AJAX completion, re-enable fields and hide loading indicator
     * @param args
     */
    var complete = function(args){
        var defaults = {
            form: null,
            fieldsSelector: null
        };
        args = $.extend({}, defaults, args);

        if(args.form != null){
            args.form.find('input, button').prop('disabled', false);
        }

        if(args.fieldsSelector != null){
            $(args.fieldsSelector).prop('disabled', false);
        }

        hideLoading();
    };

    /**
     * Handle AJAX error response with an alert
     * @param errorThrown
     */
    var error = function(errorThrown){
        $('#twpm-alert-error').text('An error has occurred.  Please contact support.  Error details: "' + errorThrown + '"').fadeIn();
    };

    /**
     * Get settings object to use in AJAX call
     * @param args
     * @returns {{url, type: string, beforeSubmit: beforeSubmit, clearForm: boolean, dataType: string, complete: complete, error: error, success: success}}
     */
    var getAjaxSettings = function(args){
        var defaults = {
            action: null,
            nonce: null,
            data: null,
            form: null,
            fieldsSelector: null,
            beforeSubmit: function(arr, f, options){
                return true;
            },
            clearForm: false,
            complete: function(xhr, textStatus){},
            error: function(xhr, textStatus, errorThrown){},
            success: function(response){}
        };
        args = $.extend({}, defaults, args);

        var settings = {
            url: ajaxurl,
            type: 'POST',
            beforeSubmit: function (arr, f, options) {
                beforeSubmit({form: args.form, fieldsSelector: args.fieldsSelector});
                if(args.beforeSubmit(arr, f, options) === false){
                    complete({form: args.form, fieldsSelector: args.fieldsSelector});
                    return false;
                };
            },
            clearForm: args.clearForm, // clear form after posting
            dataType: 'json',
            complete: function (xhr, textStatus) {
                complete({form: args.form, fieldsSelector: args.fieldsSelector});
                args.complete(xhr, textStatus);
            },
            error: function (xhr, textStatus, errorThrown) {
                error(errorThrown);
                args.error(xhr, textStatus, errorThrown);
            },
            success: function (response) {
                success(response);
                args.success(response);
            }
        };

        // Use data if provided, else initialize empty object
        if(args.data != null){
            if(typeof args.data === 'function'){
                settings.data = args.data();
            } else {
                settings.data = args.data;
            }
        } else {
            settings.data = {};
        }

        // Override action if provided
        if(args.action != null){
            settings.data.action = args.action;
        }

        // Override nonce if provided
        if(args.nonce != null){
            settings.data.nonce = args.nonce;
        }

        return settings;
    };

    /**
     * AJAXify a form element
     * @param form
     * @param args
     */
    var ajaxForm = function(form, args){
        args.form = form;
        var settings = getAjaxSettings(args);

        form.prepend(alertContainer);

        form.ajaxForm(settings);
    };

    /**
     * AJAXify a button element
     * @param button
     * @param args
     */
    var ajaxButton = function(button, args){
        button.before(alertContainer);

        button.click(function(e){
            e.preventDefault();
            var settings = getAjaxSettings(args);
            beforeSubmit({fieldsSelector: args.fieldsSelector});
            if(settings.beforeSubmit(settings.data, null, settings) === false){
                complete({fieldsSelector: args.fieldsSelector});
                return;
            }
            $.ajax(settings);
        });

    }


    var ajax = {
        beforeSubmit: beforeSubmit,
        complete: complete,
        error: error,
        hideAlerts: hideAlerts,
        showLoading: showLoading,
        hideLoading: hideLoading,
        success: success,
        genericError: genericError,
        ajaxForm: ajaxForm,
        ajaxButton: ajaxButton
    };


    TrestianCore = {
        ajax: ajax
    };
});
