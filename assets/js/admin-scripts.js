(function( $ ) {

    jQuery('table').DataTable();

    function flipLoading( selector = ''){
      if(jQuery(selector).attr('state') == 'loading' ){
        jQuery(selector).html(jQuery(selector).attr('ero-afterload-data'));
        jQuery(selector).removeAttr( 'disabled');
        jQuery(selector).attr( 'state', 'normal' );
        eroLoader( false );
      }else {
        jQuery(selector).html(jQuery(selector).attr('ero-loading-data'));
        jQuery(selector).attr( 'disabled', 'disabled' );
        jQuery(selector).attr( 'state', 'loading' );
        eroLoader( true );
      }
    }

    function eroLoader( status ) {
      const body = $( 'body' );
      if( status && $( '.ero-loader--wrapper' ).length == 0 ) {
        const loaderWrapperElem = $( document.createElement( 'div' ) );
        loaderWrapperElem.addClass( 'ero-loader--wrapper' );
        const loaderElem = $( document.createElement( 'div' ) );
        loaderElem.addClass( 'ero-loader' );
        loaderWrapperElem.append( loaderElem );
        body.append( loaderWrapperElem );
        loaderWrapperElem.animate( {
          opacity: 1,
          visibilty: 'visible'
        }, 500);
      } else {
        const loaderWrapperElem = $( '.ero-loader--wrapper' );
        loaderWrapperElem.animate( {
          opacity: 0,
          visibilty: 'hidden'
        }, 500, function () {
          loaderWrapperElem.remove();
        } );
      }
    }

    jQuery('.trigger_backup_create').click(function(){
      var elem = jQuery(this);
      var data = {
  			'action': elem.attr('ero-ajax-action')
  		};
      flipLoading(elem);
      jQuery.ajax( {
        url: ajaxurl,
        method: 'post',
        data: data,
        dataType: 'json',
        beforeSend: function () {

        },
        success: function ( response ) {
          if( response.status ) {
            document.location.reload();
          } else {
            alert( response.message );
          }
        },
        complete: function () {

        }
      } );
    });

    jQuery('.trigger_backup_delete').click(function(){
      var elem = jQuery(this);
      var data = {
  			'action': elem.attr('ero-ajax-action'),
        'file': jQuery(this).attr('file')
  		};
      flipLoading(elem);

      jQuery.ajax( {
        url: ajaxurl,
        method: 'post',
        data: data,
        dataType: 'json',
        beforeSend: function () {

        },
        success: function ( response ) {
          if( response.status ) {
            document.location.reload();
          } else {
            alert( response.message );
          }
        },
        complete: function () {

        }
      } );
    });
})( jQuery );
