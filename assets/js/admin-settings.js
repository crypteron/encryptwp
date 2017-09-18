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

});