/**
 * Created by Jon on 4/24/2016.
 */
if (typeof jQuery === 'undefined') {
    throw new Error('Common\'s JavaScript requires jQuery')
}

+function($) {
    'use strict';

    window.COMMON = {};
    COMMON.ajaxRequest = function (){
        $.ajax({
            // Uncomment the following to send cross-domain cookies:
            //xhrFields: {withCredentials: true},
            url: 'http://webtest.com/tamcongbuild.com/web/app_dev.php/ajax/index',
            dataType: 'json',
            context: ''
        }).always(function () {console.log('ttt');

        }).done(function (result) {
            console.log(result);
            //$("#product-slides").html(result);
        });
    }

}(jQuery);
$(document).ready(function() {
    $('.project-tab_span').click(function () {
        $('.slider_div').css('display', 'none');
        $('.tab-cates > li').removeClass('active');
        var dataId = $(this).data();
        $('.slider_id_' + dataId.id).css('display', 'block');
        $(this).parent().addClass("active");
    });
});
// $('.project-tab_span').click(function(){
//     console.log('ttt');
//     COMMON.ajaxRequest();
// });

