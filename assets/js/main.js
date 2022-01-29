;(function($){
    $(document).ready(function(){
        $(".portfolio-image").on("click", function (e) {
            e.preventDefault();
           data = {
               'action' : 'ajax-portfolio',
               'post_id': $(this).data('id')

           };
           console.log(data);

            $.ajax({
                url: portfolio_fetch.ajaxurl,
                type: 'post',
                data: data,
                beforeSend: function () {
                    $(this).text('Loading...'); // change the button text, you can also add a preloader image
                },
                success: function (data) {              
                    $('.portfolio_load').hide().html(data).fadeIn('slow');;
                }
            });
        });
    });
})(jQuery);


