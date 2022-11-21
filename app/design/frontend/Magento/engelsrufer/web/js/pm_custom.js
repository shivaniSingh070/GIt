// Updated by AA, 25.04.2019
require([
    'jquery',
    "jquery/ui"
], function($){
    'use strict';

    
    //Updated by AA, 25.04.2019
    $(function() {

    // jQuery in order to show and hide the myaccount dropdown
        $(".myaccout-icon").on( "click", function(e) {
       	    event.stopPropagation();
            $('.myaccount-dropdown').toggle();
        });
        
    // stop event on clicking on myaccount-dropdown in order to protect the hide the dropdown
        $(".myaccount-dropdown").on("click", function (/*nothing here*/) {
            event.stopPropagation();
        });
        
    // add toggle on on category filter updated by AA on 6.2019 https://trello.com/c/pjgXbpAN/ 
        $(".filter-options-title").on( "click", function() {
           $(this).next().toggle();
           $(this).toggleClass("toggle-hide");
       });

    // added toggle for cms pages updated by AA 0n 4.6.2019
        $(".heading-toggle").on( "click", function() {
            $('.toggle-content').hide();
            $(this).next().toggle();
            $(this).toggleClass("toggle-hide");
        });
        
    // go back button for 404 page    
         $("#back_btn").click(function (){
            event.stopPropagation();
            window.history.back();
          });

   // Show popup on clicking of "Learn more" in product detail page, updated by AA 0n 18.6.2019 
         $('.show-concept').click(function() {
              event.stopPropagation();
              $('#engelsrufer-concept-popup').show();
            });

    // Hide popup on clicking of engelsrufer-concept-popup updated by AA 0n 18.6.2019 
        $(".engelsrufer-concept-popup").on("click", function (/*nothing here*/) {
           $('#engelsrufer-concept-popup').hide();
        });

     // stop event on clicking on concept-popup in order to protect the hide the popup updated by AA 0n 18.6.2019
         $(".concept-popup").on("click", function (/*nothing here*/) {
            event.stopPropagation();
        });

    // Hide popup on clicking of "close" in product detail page, updated by AA 0n 18.6.2019 
        $(".hide-concept").on("click", function (/*nothing here*/) {
           $('#engelsrufer-concept-popup').hide();
        });
      
     // Show and hide more information on cookie confirmation block, updated by AA on 26.06.2019
         $("#btn-more-info").on("click", function (/*nothing here*/) {
           $('.more-info').show();
           $('#btn-less-info').show();
           $("#btn-more-info").hide();
        });
        
    // more and less for cookie msg
        $("#btn-less-info").on("click", function (/*nothing here*/) {
           $('.more-info').hide();
           $('#btn-less-info').hide();
           $("#btn-more-info").show();
        });
        
      // scroll to Article detail section of product detail page  from atricle link
        $(".article-link span").click(function (){
                $('html, body').animate({
                    scrollTop: $(".detail-container").offset().top
                }, 2000);
        });
        
       // Search validation for 3 characters, updated by AA on 30.03.2019  
        $('#search').on('keydown keyup change', function(){
	        var char = $(this).val();
	        var charLength = $(this).val().length;
	        var form = document.getElementById("search_mini_form");
	        if(charLength < 3){
				form.onsubmit = function() {
				  return false;
				}
	        }
	        else{
				form.onsubmit = function() {
				  return true;
				}
	        }
        });

    });
    
    // hide the account dropdown on clicking outside
    $(document).on("click", function () {
         $(".myaccount-dropdown").hide();
         $('#engelsrufer-concept-popup').hide();
    });

 // Toggle the Menu Tab in left Navigation on Category Page By NA on 24-05-2019  
    $('.c-sidebar--categories h3').on('click', function(){
        $(this).next().toggle();
        $(this).toggleClass("toggle-hide");
    }); 

    // Toggle the Shop By Tab in left Navigation on Category Page By NA on 19.06.2019  
    $('.custf-toggle').on('click', function(){
        console.log($(this).next());
        $(this).next().toggle();
        $(this).next().removeClass('hidef');
        $(this).next().addClass('showf');
        //$(this).toggleClass("toggle-hide");
    }); 
    /**
     * Added by N.A on 23.01.2020
     * Remove the sale badge from the PDP if configurable product's variant does not have the special price.
     * Link: https://trello.com/c/DAB1vbqW/87-sale-article
     */
    $(document).on("click", function(){
        //console.log($(this).find('.old-price:visible').length);
        if($(this).find('.old-price:visible').length == 0){
            $(this).find('.label-row .sale-label').css('display','none');
        }else{
            $(this).find('.label-row .sale-label').css('display','block'); 
        }
    });

});



