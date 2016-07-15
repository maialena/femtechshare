$(function() {
    $('.profilepic').on('click', function(e) {
        var $biginfo = $('#teamcontent');
        var $bigname = $('#bigname');
        var $bigjob = $('#bigjob');
        var $bigdesc = $('#bigdesc');
        var $pic = $(this).attr('src');
        $('.bigimg').attr('src', $pic);
        var newname = $(this).attr('alt');
        var newrole = $(this).siblings('.job').eq(0).html();
        var newdesc = $(this).siblings('.desc').eq(0).html();

        $bigname.html(newname);
        $bigjob.html(newrole);
        $bigdesc.html(newdesc);

        if ($biginfo.css('display') == 'none') {
            $biginfo.slideDown(350);
        }
    });
});