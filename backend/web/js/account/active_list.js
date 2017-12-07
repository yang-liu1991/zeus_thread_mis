    $(document).ready(function(){
        $('#my_accounts').attr("aria-expanded", true).addClass('in');
        $('#my_accounts').parent().find('a.nav-header').attr("aria-expanded", true).removeClass('collapsed');
    });

