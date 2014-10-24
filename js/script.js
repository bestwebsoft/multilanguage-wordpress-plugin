(function( $ ) {
	$( document ).ready( function() {
		/* Show and hide Add new language form */
		$( '#mltlngg-add-new-language-form' ).css( 'display', 'none' );
		$( '#mltlngg-add-lang-link' ).click( function() {
			$( '#mltlngg-add-new-language-form' ).slideToggle();
		});

		/*
		 *add notice about changing in the settings page
		 */
		$( '#mltlngg-current-languages-form input' ).bind( "change click select", function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#mltlngg-settings-notice' ).css( 'display', 'block' );
			};
		});

		/* Do not save post if Title and Content is empty */
		$( '#publish' ).on( 'click', function() {
			var mltlnggCurrentTitle = $( '#title' ).val(),
				mltlnggCurrentContent;
			if ( $( '#content' ).is( ":hidden" ) ) {
				mltlnggCurrentContent = tinymce.activeEditor.getContent();
			} else {
				mltlnggCurrentContent = $( '#content' ).val();
			}
			if ( "" == mltlnggCurrentTitle && "" == mltlnggCurrentContent ) {
				alert( mltlngg_vars.update_post_error );
				return false;
			}
		});
	});
})( jQuery );