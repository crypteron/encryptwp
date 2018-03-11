jQuery.fn.htmlClean = function() {
    this.contents().filter(function() {
        if (this.nodeType != 3) {
            jQuery(this).htmlClean();
            return false;
        }
        else {
            this.textContent = jQuery.trim(this.textContent);
            return !/\S/.test(this.nodeValue);
        }
    }).remove();
    return this;
}

jQuery(document).ready(function($) {
    function updateInputStates(encrypt_enabled) {
        // Disable / Enable fields on encrypt enable
        var buttons = $('.ewp-field_encrypt_enabled input.buttonset-item');
        if (encrypt_enabled) {
            buttons.prop('disabled', false);
            $( ".ewp-field_encrypt_enabled .buttonset" ).buttonset( "option", "disabled", false);
        }
        else {
            buttons.prop('disabled', true);
            $( ".ewp-field_encrypt_enabled .buttonset" ).buttonset( "option", "disabled", true);
        }
    }



    // Hide / Show the encrypt fields section when encrypt enabled is toggled
    $('input[name="encrypt_enabled"]').change(function(){
       var enabled = $('input[name="encrypt_enabled"]:checked').val();
       enabled = (enabled == '1');
       if(enabled){
           $('.ewp-field_encrypt_enabled').fadeIn();

       } else {
           $('.ewp-field_encrypt_enabled').fadeOut();
       }
       updateInputStates(enabled);
    });

    // Determine if email encryption is supported
    if(ENCRYPT_WP_ADMIN.options.encrypt_email== false){
        $.get(ENCRYPT_WP_ADMIN.encrypt_email_path, function(data){
            $('.ewp-loading').hide();
            if(data == 0){
                $('.ewp-encrypt-email-unsupported').show();
            } else if(data == 1){
                $('.ewp-encrypt-email-supported').show();
            }
        });
    }

    // Turn radio buttons into toggles
    $('.buttonset').each(function(){
        $(this).htmlClean().buttonset();
    });

    updateInputStates(ENCRYPT_WP_ADMIN.options.encrypt_enabled);

    TrestianCore.ajax.ajaxForm($('#encrypt-wp-settings'), {
        beforeSubmit: function(data, form, options){
            return window.confirm("This action cannot be undone. Please ensure you have taken a backup of your database before proceeding.")
        }
    });

});