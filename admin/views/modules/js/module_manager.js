jQuery(document).ready(function($) {
    $('.module-item').on('click', function() {
        //alert("click");
        var $moduleItem = $(this);
        var moduleName = $moduleItem.data('module');
        var isActive = $moduleItem.hasClass('active');

        //alert(moduleName);

        if (isActive) {
            $.post({
                url: ajaxurl,
                data: {
                    action: 'deactivate_module',
                    module: moduleName
                },
                success: function(response) {
                    //alert(response);
                    $moduleItem.removeClass('active');
                }
            });
        } else {
            $.post({
                url: ajaxurl,
                data: {
                    action: 'activate_module',
                    module: moduleName
                },
                success: function(response) {
                    //alert(response);
                    $moduleItem.addClass('active');
                }
            });
        }
    });
});
