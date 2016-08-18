(function( $ ) {
	$( document ).ready( function() {
		$( '#update-nav-menu' ).bind( 'click', function( e ) {
			if ( e.target && e.target.className && -1 != e.target.className.indexOf( 'item-edit' ) ) {
				$( "input[value='#mltlngg-switcher'][type=text]" ).parent().parent().parent().each( function() {
					var item = $( this ).attr( 'id' ).substring( 19 );
					/* remove default fields */
					$( this ).children( 'p:not( .field-move )' ).remove(); 
					
					/* add new hidden elements */
					$( this ).append( $( '<input id="edit-menu-item-title-' + item + '" value="' + mltlngg_var.title + '" name="menu-item-title[' + item + ']" type="hidden" />' ) );
					$( this ).append( $( '<input id="edit-menu-item-url-' + item + '" value="#mltlngg-switcher" name="menu-item-url[' + item + ']" type="hidden" />' ) );
					/* custom hidden field which exits only if js code has been executed */
					$( this ).append( $( '<input id="edit-menu-item-mltlngg-nonce-' + item + '" value="1" name="menu-item-mltlngg-nonce[' + item + ']" type="hidden" />' ) );

					/* add new elements - option */
					var i = 1,
						this_block = $( this ) ;
					$.each( mltlngg_var.switcher.input, function( key, value ) {
						var check = '';
						if ( ( typeof( mltlngg_var.value[ item ] ) != 'undefined' && mltlngg_var.value[ item ] == key ) || ( typeof( mltlngg_var.value[ item ] ) == 'undefined' && key == 'drop-down-list' ) ) {
							check = ' checked="checked"';
						}
						$( this_block ).prepend( $( '<div class="mltlngg-menu-item-radio"><label for="edit-menu-item-' + key + i + '-' + item + '"><input' + check + ' id="edit-menu-item-' + key + i + '-' + item + '" value="' + key + '" name="menu-item-mltlngg-switcher[' + item + ']" type="radio" /> ' + value + '</label></div>' ) );
						i++;
					});
				});
			}
		});
	});
})( jQuery );