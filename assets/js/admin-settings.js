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
    $('input[name="encrypt_enabled"]').change(function(){
       var enabled = $('input[name="encrypt_enabled"]:checked').val();
       if(enabled == '1'){
           $('.ewp-field_encrypt_enabled').fadeIn();
       } else {
           $('.ewp-field_encrypt_enabled').fadeOut();
       }
    });

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

    $('.buttonset').each(function(){
        $(this).htmlClean().buttonset();
    });

    $('#encrypt_email_off').click(function(e){
        if(ENCRYPT_WP_ADMIN.options.encrypt_email){
         if(!window.confirm("Disabling email encryption will also decrypt all of your encrypted email addresses. Are you sure you want to continue?")){
             e.preventDefault();
         }
        }
    });

    $('#encrypt_enabled_off').click(function(e){
        if(ENCRYPT_WP_ADMIN.options.encrypt_email){
            window.alert('NOTE: Data that is already encrypted will remain encrypted. To decrypt all data click the "Decrypt All Fields" button at the bottom of this page');
        }
    });

    TrestianCore.ajax.ajaxButton($('#encrypt-all'), {
        action: ENCRYPT_WP_ADMIN.encrypt_all_action,
        nonce: ENCRYPT_WP_ADMIN.encrypt_all_nonce,
        beforeSubmit: function(data, form, options){
            return window.confirm("This action cannot be undone. Please ensure you have taken a backup of your database before proceeding.")
        }
    });

    TrestianCore.ajax.ajaxButton($('#decrypt-all'), {
        action: ENCRYPT_WP_ADMIN.decrypt_all_action,
        nonce: ENCRYPT_WP_ADMIN.decrypt_all_nonce,
        beforeSubmit: function(data, form, options){
            return window.confirm("This action cannot be undone. Please ensure you have taken a backup of your database before proceeding.")
        }
    });

});