//****************** YOUR CUSTOMIZED JAVASCRIPT **********************//

$(function () {
    var $displayarea = $(".page-content");
    var parentWidth = $('.main-content').height();
    var footer = $('.footer');

    if ($displayarea.height()+footer.height() > parentWidth) {
        /* alert('lebih besar');
        alert($displayarea.height());*/
        footer.css({
                position: 'static'
            });
    } else {
        /* alert('lebih kecil');
        alert($displayarea.height()); */
        footer.css({
                position: 'fixed'
            });
    }

    $page = jQuery.url.attr("file");    
    if(!$page) {
        $page = 'index.php';
    }
    $('ul.navigation li a').each(function(){
        var $href = $(this).attr('href');
        if ( ($href == $page) || ($href == '') ) {
            $(this).parent().addClass('active');

            if( $(this).closest("li.nav-parent").length == 1 ) {

                $(this).closest("li.nav-parent").addClass('active');

            }

        } else {
            $(this).parent().removeClass('active');
        }
    });

});