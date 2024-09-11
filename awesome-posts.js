jQuery(document).ready(function($) {
    $('#upload_image_button').click(function(e) {
        e.preventDefault();
        var custom_uploader = wp.media({
            title: 'Upload Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        }).open().on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#image').val(attachment.url);
        });
    });
});
