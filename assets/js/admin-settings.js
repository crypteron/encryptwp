jQuery(document).ready(function($) {
    if(ENCRYPT_WP_ADMIN.encrypt_email_enabled == false){
        $.get(ENCRYPT_WP_ADMIN.encrypt_email_path, function(data){
            $('.ewp-loading').hide();
            if(data == 0){
                $('.ewp-encrypt-email-unsupported').show();
            } else if(data == 1){
                $('.ewp-encrypt-email-supported').show();
            }
        });
    }

    TrestianCore.ajax.ajaxButton($('#encrypt-all'), {
        action: ENCRYPT_WP_ADMIN.encrypt_all_action,
        nonce: ENCRYPT_WP_ADMIN.encrypt_all_nonce,
        beforeSubmit: function(data, form, options){
            return window.confirm("This action cannot be undone. Please ensure you have taken a backup of your database before proceeding.")
        }
    });

});