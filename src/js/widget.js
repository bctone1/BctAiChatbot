console.log('connect');
var bctai_ajax_url = "https://c0018.bizhomepass.kr/wp-admin/admin-ajax.php";// 항상 바꿔줘야한다

jQuery(document).ready(function ($) {

    $.ajax({
        url: bctai_ajax_url,
        method: 'POST',
        data: {
            action: 'bctai_chatbox_cafe24',
        },
        success: function(response) {
            // console.log(response);
            jQuery("body").append(response.content);
    
            // CSS 파일을 동적으로 추가
            if (response.styles) {
                $.each(response.styles, function(key, url) {
                    $('<link/>', {
                        rel: 'stylesheet',
                        type: 'text/css',
                        href: url
                    }).appendTo('head');
                });
            }
            // JavaScript 파일을 동적으로 추가
            if (response.scripts) {
                $.each(response.scripts, function(key, url) {
                    $('<script/>', {
                        src: url,
                        type: 'text/javascript',
                        defer: true
                    }).appendTo('body');
                });
            }
        },
        error: function(error) {
            console.error('Error:', error);
        }
    });
});
