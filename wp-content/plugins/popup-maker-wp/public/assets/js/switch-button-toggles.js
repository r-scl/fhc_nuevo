/**
 * sgpm_lc_switch.js
 * Version: 1.0
 * Author: LCweb - Luca Montanari
 * Website: http://www.lcweb.it
 * Licensed under the MIT license
 */

(function($){
  if(typeof($.fn.sgpm_lc_switch) != 'undefined') {return false;} // prevent dmultiple scripts inits

  $.fn.sgpm_lc_switch = function(on_text, off_text) {

    // destruct
    $.fn.sgpm_lcs_destroy = function() {

      $(this).each(function() {
                var $wrap = $(this).parents('.sgpm_lcs_wrap');

        $wrap.children().not('input').remove();
        $(this).unwrap();
            });

      return true;
    };


    // set to ON
    $.fn.sgpm_lcs_on = function() {

      $(this).each(function() {
                var $wrap = $(this).parents('.sgpm_lcs_wrap');
        var $input = $wrap.find('input');

        if(typeof($.fn.prop) == 'function') {
          $wrap.find('input').prop('checked', true);
        } else {
          $wrap.find('input').attr('checked', true);
        }

        $wrap.find('input').trigger('sgpm_lcs-on');
        $wrap.find('input').trigger('sgpm_lcs-statuschange');
        $wrap.find('.sgpm_lcs_switch').removeClass('sgpm_lcs_off').addClass('sgpm_lcs_on');

        // if radio - disable other ones
        if( $wrap.find('.sgpm_lcs_switch').hasClass('sgpm_lcs_radio_switch') ) {
          var f_name = $input.attr('name');
          $wrap.parents('form').find('input[name='+f_name+']').not($input).sgpm_lcs_off();
        }
            });

      return true;
    };


    // set to OFF
    $.fn.sgpm_lcs_off = function() {

      $(this).each(function() {
                var $wrap = $(this).parents('.sgpm_lcs_wrap');

        if(typeof($.fn.prop) == 'function') {
          $wrap.find('input').prop('checked', false);
        } else {
          $wrap.find('input').attr('checked', false);
        }

        $wrap.find('input').trigger('sgpm_lcs-off');
        $wrap.find('input').trigger('sgpm_lcs-statuschange');
        $wrap.find('.sgpm_lcs_switch').removeClass('sgpm_lcs_on').addClass('sgpm_lcs_off');
            });

      return true;
    };


    // construct
    return this.each(function(){

      // check against double init
      if( !$(this).parent().hasClass('sgpm_lcs_wrap') ) {

        // default texts
        var ckd_on_txt = (typeof(on_text) == 'undefined') ? 'ON' : on_text;
        var ckd_off_txt = (typeof(off_text) == 'undefined') ? 'OFF' : off_text;

         // labels structure
        var on_label = (ckd_on_txt) ? '<div class="sgpm_lcs_label sgpm_lcs_label_on">'+ ckd_on_txt +'</div>' : '';
        var off_label = (ckd_off_txt) ? '<div class="sgpm_lcs_label sgpm_lcs_label_off">'+ ckd_off_txt +'</div>' : '';


        // default states
        var disabled  = ($(this).is(':disabled')) ? true: false;
        var active    = ($(this).is(':checked')) ? true : false;

        var status_classes = '';
        status_classes += (active) ? ' sgpm_lcs_on' : ' sgpm_lcs_off';
        if(disabled) {status_classes += ' sgpm_lcs_disabled';}


        // wrap and append
        var structure =
        '<div class="sgpm_lcs_switch '+status_classes+'">' +
          '<div class="sgpm_lcs_cursor"></div>' +
          on_label + off_label +
        '</div>';

        if( $(this).is(':input') && ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') ) {

          $(this).wrap('<div class="sgpm_lcs_wrap"></div>');
          $(this).parent().append(structure);

          $(this).parent().find('.sgpm_lcs_switch').addClass('sgpm_lcs_'+ $(this).attr('type') +'_switch');
        }
      }
        });
  };



  // handlers
  $(document).ready(function() {

    // on click
    $(document).delegate('.sgpm_lcs_switch:not(.sgpm_lcs_disabled)', 'click tap', function(e) {

      if( $(this).hasClass('sgpm_lcs_on') ) {
        if( !$(this).hasClass('sgpm_lcs_radio_switch') ) { // not for radio
          $(this).sgpm_lcs_off();
        }
      } else {
        $(this).sgpm_lcs_on();
      }
    });


    // on checkbox status change
    $(document).delegate('.sgpm_lcs_wrap input', 'change', function() {

      if( $(this).is(':checked') ) {
        $(this).sgpm_lcs_on();
      } else {
        $(this).sgpm_lcs_off();
      }
    });

  });

})(jQuery);
