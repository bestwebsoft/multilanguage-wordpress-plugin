(function( $ ) {
	$( document ).ready( function() {
		$( '.mltlngg_role' ).on( 'change', function() {
			var checkboxes = $( '.mltlngg_role' );
			if ( checkboxes.filter( ':checked' ).length == checkboxes.length ) {
				$( '.mltlngg_select_all' ).prop( 'checked', true );
			} else {
				$( '.mltlngg_select_all' ).prop( 'checked', false );
			}
		} ).trigger( 'change' );

		$( '.mltlngg_select_all' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.mltlngg_role' ).each( function(){
					$( this ).prop( 'checked', true );
				});
			} else {
				$( '.mltlngg_role' ).each( function(){
					$( this ).prop( 'checked', false );
				});
			}
		} );
	});
})( jQuery );