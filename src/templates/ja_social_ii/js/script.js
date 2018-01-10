/**
 * ------------------------------------------------------------------------
 * JA Social II template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */


 (function($) {
    var scrollLastPos = 0,
        scrollDir = 0, // -1: up, 1: down
        scrollTimeout = 0;
    $(window).on ('scroll', function (e) {
        var st = $(this).scrollTop();
        //Determines up-or-down scrolling
        if (st < 1) {
            if (scrollDir != 0) {
                scrollDir = 0;
                scrollToggle();
            }
        } else if (st > scrollLastPos){
            //Replace this with your function call for downward-scrolling
            if (scrollDir != 1) {
                scrollDir = 1;
                scrollToggle();
            }
        } else if (st < scrollLastPos){
            //Replace this with your function call for upward-scrolling
            if (scrollDir != -1) {
                scrollDir = -1;
                scrollToggle();
            }
        }
        //Updates scroll position
        scrollLastPos = st;
    });

    $('.ja-header').on ('hover', function () {
        $('html').removeClass ('scrollDown scrollUp').addClass ('hover');
        scrollDir = 0;
    })

    scrollToggle = function () {
        $('html').removeClass ('hover');
        if (scrollDir == 1) {
            $('html').addClass ('scrollDown').removeClass ('scrollUp');
        } else if (scrollDir == -1) {
            $('html').addClass ('scrollUp').removeClass ('scrollDown');
        } else {
            $('html').removeClass ('scrollUp scrollDown');
        }
    $('html').addClass ('animating');
    setTimeout(function(){ $('html').removeClass ('animating'); }, 1000);
    }

})(jQuery);

(function($){
  $(document).ready(function(){

  ////////////////////////////////
  // equalheight for col
  ////////////////////////////////
  var ehArray = ehArray2 = [],
    i = 0;

  $('.equal-height').each (function(){
    var $ehc = $(this);
    if ($ehc.has ('.equal-height')) {
      ehArray2[ehArray2.length] = $ehc;
    } else {
      ehArray[ehArray.length] = $ehc;
    }
  });
  for (i = ehArray2.length -1; i >= 0; i--) {
    ehArray[ehArray.length] = ehArray2[i];
  }

  var equalHeight = function() {
    for (i = 0; i < ehArray.length; i++) {
      var $cols = ehArray[i].children().filter('.col'),
        maxHeight = 0,
        equalChildHeight = ehArray[i].hasClass('equal-height-child');

    // reset min-height
      if (equalChildHeight) {
        $cols.each(function(){$(this).children().first().css('min-height', 0)});
      } else {
        $cols.css('min-height', 0);
      }
      $cols.each (function() {
        maxHeight = Math.max(maxHeight, equalChildHeight ? $(this).children().first().innerHeight() : $(this).innerHeight());
      });
      if (equalChildHeight) {
        $cols.each(function(){$(this).children().first().css('min-height', maxHeight)});
      } else {
        $cols.css('min-height', maxHeight);
      }
    }
    // store current size
    $('.equal-height > .col').each (function(){
      var $col = $(this);
      $col.data('old-width', $col.width()).data('old-height', $col.innerHeight());
    });
  };

  equalHeight();

  // monitor col width and fire equalHeight
  setInterval(function() {
    $('.equal-height > .col').each(function(){
      var $col = $(this);
      if (($col.data('old-width') && $col.data('old-width') != $col.width()) ||
          ($col.data('old-height') && $col.data('old-height') != $col.innerHeight())) {
        equalHeight();
        // break each loop
        return false;
      }
    });
  }, 500);

  // add active for radio is not on off button.
  $('html.com_config fieldset.radio').filter(function(){return $(this).find('input').length >2}).find('input:checked').next().addClass('active');
  
  });

})(jQuery);

(function($){
    $(window).load(function() {
      $(".features-loader").fadeOut("slow");;

      if($('.joms-input--datepicker').length > 0) {
        $('.joms-input--datepicker').removeAttr("style");
      }

      if($('.features-page .t3-mainbody .t3-content').height() > 0) {
        if($('.features-page .t3-mainbody').length > 0) {
          var heightscreen = $(window).height(),
                  pdcenter         = (heightscreen - $('.features-page .t3-content').height())/2;

          if (!(($('.features-page .t3-mainbody .t3-content').outerHeight()) > heightscreen))
          	$('.features-page .t3-mainbody').height(heightscreen);
          else {
            $('.features-page .t3-mainbody').css('height','auto');
            pdcenter = $('.features-page .t3-header').outerHeight();
          }
          	
          $('.features-page .t3-content').css('padding-top',pdcenter);

          if($('.features-page .features-intro').height()>0) {
            if (!(($('.features-page .t3-mainbody .t3-content').outerHeight()) > heightscreen))
            	$('.features-page .features-intro').css('margin-top',heightscreen);
            else
                $('.features-page .features-intro').css('margin-top','0px');
          } else {
            $('.features-page .t3-footer').css('margin-top',heightscreen);
          }

          if (($('.features-page .t3-mainbody .t3-content').outerHeight()) > heightscreen)
			$('.features-page .t3-mainbody').css('position','relative');
		  else
			$('.features-page .t3-mainbody').css('position','fixed');

          $(window).resize(function(){
              var heightscreen = $(window).height(),
                  pdcenter         = (heightscreen - $('.features-page .t3-content').height())/2;

              if (!(($('.features-page .t3-mainbody .t3-content').outerHeight()) > heightscreen))
              	$('.features-page .t3-mainbody').height(heightscreen);
              else {
                $('.features-page .t3-mainbody').css('height','auto');
                pdcenter = $('.features-page .t3-header').outerHeight();
              }

              $('.features-page .t3-content').css('padding-top',pdcenter);

              if($('.features-page .features-intro').outerHeight()>0) {
                if (!(($('.features-page .t3-mainbody .t3-content').outerHeight()) > heightscreen))
                	$('.features-page .features-intro').css('margin-top',heightscreen);
                else
                	$('.features-page .features-intro').css('margin-top','0px');
              } else {
                $('.features-page .t3-footer').css('margin-top',heightscreen);
              }

              if (($('.features-page .t3-mainbody .t3-content').outerHeight()) > heightscreen) {
              	$('.features-page .t3-mainbody').css('position','relative');
              } else {
              	$('.features-page .t3-mainbody').css('position','fixed');
              }
          });
        }
      }

      //inview events
      $('.t3-section').bind('inview', function (event, visible, visiblePartX, visiblePartY) {
        if (visible) {
          if (visiblePartY == 'bottom' || visiblePartY == 'both') {
            if(!$(this).hasClass('section-mask')){
              $(this).addClass('inview');
            }
          }
        }
      });
    });
})(jQuery);

(function($){
$(document).ready(function(){
    $('.container').each(function(){  
        var highestBox = 0;
        $(this).find('.K2EqualHeight .catItemView').each(function(){
            if($(this).height() > highestBox){  
                highestBox = $(this).height();  
            }
        })
        $(this).find('.K2EqualHeight .catItemView').height(highestBox);
    });
});
})(jQuery);

// TAB
// -----------------
(function($){
  $(document).ready(function(){
    if($('.nav.nav-tabs').length > 0 && !$('.nav.nav-tabs').hasClass('nav-stacked')){
      $('.nav.nav-tabs a').click(function (e) {
          e.preventDefault();
          $(this).tab('show');
      })
     }
  });
})(jQuery);