;(function($){
    $(document).ready(function(){
        $(".portfolio-image").on("click", function (e) {
            e.preventDefault();
           data = {
               'action' : 'ajax-portfolio',
               'post_id': $(this).data('id'),
               
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
                    $('.portfolio_load').hide().html(data).fadeIn('slow');
                    $('body').addClass('overlay');
                    $('.portfolio_load').on('click',function(){
                        $('.portfolio_load').hide();
                        $('body').removeClass('overlay');
                    });            
            },


            });

            $('.close_button').on('click', function(){
                console.log('closed');
                
            });

        });


        $(".filter-btn").map(function(index, btn){
            $(btn).on('click', function(e){
               let category =  $(e.currentTarget).data('category');

               if(category == 'all'){
                   $(".menu-item").fadeOut(0);
                   $(".menu-item").fadeIn(1000);
               }else{
                   $(".menu-item").hide();
                   $(".menu-item"+"#"+category).fadeIn(1000);
               }
            });
        });
    });
})(jQuery);


