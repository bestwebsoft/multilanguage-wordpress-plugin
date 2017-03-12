(function( $ ) {
	$( document ).ready( function() {
		/* Show and hide Add new language form */
		$( '#mltlngg-add-new-language-form' ).css( 'display', 'none' );
		$( '#mltlngg-add-lang-link' ).click( function() {
			$( '#mltlngg-add-new-language-form' ).slideToggle();
		});

		/* Do not save post if Title and Content is empty */
		$( '#publish' ).on( 'click', function() {
			var mltlnggCurrentTitle = $( '#title' ).val(),
				mltlnggCurrentContent;
			if ( $( '#content' ).is( ":hidden" ) ) {
				mltlnggCurrentContent = tinymce.get('content').getContent();
			} else {
				mltlnggCurrentContent = $( '#content' ).val();
			}
			if ( "" == mltlnggCurrentTitle && "" == mltlnggCurrentContent ) {
				alert( mltlngg_vars.update_post_error );
				return false;
			}
		});

		/* Get option how to update translation */
		var getLangContentDiv = $( '#get-lang-content' ); /* Find DIV id=get-lang-content */
		$( '#excerpt' ).val( $( 'input#excerpt-' + getLangContentDiv.find( 'a.nav-tab-active' ).data( 'lang' ) ).val() );/* show excerpt loaded language */
		$( 'li[id^="category-"]' ).each( function( i,elem ) {
			var old_cat = $( elem ).find( 'label' ).text();
			old_cat = old_cat.trim();
			var old_key = $( elem ).find( 'input' ).text();
			$( elem ).append(
				$( '<input/>' ) /* Create hidden field with categories */
				.attr( 'type', 'hidden' )
				.attr( 'id', 'cat-' + getLangContentDiv.find( 'a.nav-tab-active' ).data( 'lang' ) )
				.attr( 'name', 'cat_' + getLangContentDiv.find( 'a.nav-tab-active' ).data( 'lang' ) )
				.val( old_cat )
			);
		});

		/* If Fusion Page Builder Plugin is enabled we will need to reload it */
		if (isFusionPageBuilderInstalled) {
			reloadFusionPageBuilder();
		}

		/* Function for click on another language tab to edit translation */
		getLangContentDiv.on( 'click', 'a.nav-tab', function() {
			var inputTitle = $( '#title' ), /* Find INPUT id=title */
				inputContent = $( '#content' ), /* Find INPUT id=content */
				inputExcerpt = $( '#excerpt' ),
				mltlnggPostId = $( '#post_ID' ).attr( 'value' ), /* Get current post ID */
				cat_id = [],/* Array for category */
				newLang = ( $( this ).data( 'lang' ) ), /* Get language code from current language tab */
				newLangName = ( $( this ).attr( 'value' ) ), /* Get language name from current language tab */
				inputTitleNewLang = $( 'input#title-' + newLang ), /* Find INPUT id=title-{new lang code} */
				oldLang = getLangContentDiv.find( 'a.nav-tab-active' ).data( 'lang' ), /* Get language code from previous language tab */
				inputTitleOldLang = $( 'input#title-' + oldLang ), /* Find INPUT id=title-{old lang code} */
				mltlnggOldTitle = inputTitle.val(), /* Get title from previous language tab */
				mltlnggOldExcerpt = $( '#excerpt' ).val(),
				tmceEnabled = $( '#wp-content-wrap' ).hasClass('tmce-active'),
				fusionPageBuilderIsEnabled = isFusionPageBuilderEnabled(),
				mltlnggOldContent, data;

			$( 'input[id^="in-category-"]' ).each( function( i,elem ) {
				cat_id[i] = $( elem ).val();
			});
			getLangContentDiv.find( 'a.nav-tab-active' ).removeClass( 'nav-tab-active' ); /* Change previous language tab from active to inactive */
			$( this ).addClass( 'nav-tab-active' ); /* Change current language tab from inactive to active */
			/* Get content from previous language tab */
			if ( tmceEnabled ) { /* If TinyMCE editor or Fusion Page Builder is active */
				mltlnggOldContent = tinymce.get('content').getContent(); /* Get content from TinyMCE */
			} else { /* If Text editor is active */
				mltlnggOldContent = inputContent.val(); /* Get content from Text editor */
			}
			inputTitleOldLang.val( mltlnggOldTitle ); /* Insert Title from previous language tab to hidden field */
			$( 'textarea#content-' + oldLang ).val( mltlnggOldContent ); /* Insert Content from previous language tab to hidden field */
			$( 'input#excerpt-' + oldLang ).val( mltlnggOldExcerpt );
			if ( inputTitleNewLang.length > 0 && $( '#after-save-' + newLang ).length > 0 ) { /* If hidden blocks is exist, get Title & Content from them */
				inputTitle.val( inputTitleNewLang.val() ); /* Set title to current language tab */

				/* Set content to current language tab */
				if ( tmceEnabled ) { /* If TinyMCE editor is active */
					tinymce.get('content').setContent( $( 'textarea#content-' + newLang ).val() ); /* Set content to TinyMCE */
				} else { /* If Text editor is active */
					inputContent.val( $( 'textarea#content-' + newLang ).val() ); /* Set content to Text editor */
				}

				inputExcerpt.val( $( 'input#excerpt-' + newLang ).val() );
				$( 'li[id^="category-"]' ).each(function( i, elem ) {
					var html_object = $( elem ).find( 'label' );
					var old_cat = html_object.text();
					var content = html_object.html();
					if ( $( elem ).find( 'input[id^="cat-' + newLang +'"]' ).length > 0 ) {
						content = content.replace( old_cat, ' ' + $( elem ).find( 'input[id^="cat-' + newLang +'"]' ).val() )
						$( html_object ).html( content );
					}
				} );

				/* If autoupdate translation is enabled, save changes */
				if ( 1 == mltlngg_vars.autosave ) {
					data = {
						'action': 'mltlngg_ajax_callback',
						'old_lang': oldLang,
						'cat_id' : cat_id,
						'mltlngg_post_id': mltlnggPostId,
						'mltlngg_old_title': mltlnggOldTitle,
						'mltlngg_old_content': mltlnggOldContent,
						'mltlngg_old_excerpt': mltlnggOldExcerpt,
						'security': mltlngg_vars.ajax_nonce
					};
					$.post( ajaxurl, data );
				}
				/* If Fusion Page Builder Plugin was enabled, reload it */
				if ( fusionPageBuilderIsEnabled ) {
					DdHelper.shortCodestoBuilderElements();
				}
			} else {
				/* If hidden blocks do not exist, get Title & Content from database, then create hidden blocks */
				data = {
					'action': 'mltlngg_ajax_callback',
					'get_data': 'get_data',
					'cat_id' : cat_id,
					'new_lang': newLang,
					'old_lang': oldLang,
					'mltlngg_post_id': mltlnggPostId,
					'mltlngg_old_title': mltlnggOldTitle,
					'mltlngg_old_content': mltlnggOldContent,
					'mltlngg_old_excerpt': mltlnggOldExcerpt,
					'security': mltlngg_vars.ajax_nonce
				};
				$.post( ajaxurl, data, function ( response ) {
					/* get only content in multilanguage comments */
					var regExp = /<!--mltlngg-ajax-results-->([^]+)<!--end-mltlngg-ajax-results-->/;
					var matches = regExp.exec( response );
					if ( matches ) {
						response = matches[1];
					}

					var mltlnggNew = $.parseJSON( response );

					if (  $( 'input#title-' + newLang ).length == 0 ) {
						$( '#post-body-content' ).append(
							$( '<input/>' ) /* Create hidden field with Title */
								.attr( 'type', 'hidden' )
								.attr( 'id', 'title-' + newLang )
								.attr( 'name', 'title_' + newLang )
								.val( mltlnggNew.post_title ),
							$( '<textarea/>' ) /* Create hidden field with Content */
								.css( 'display', 'none' )
								.attr( 'id', 'content-' + newLang )
								.attr( 'name', 'content_' + newLang )
								.val( mltlnggNew.post_content ),
							$( '<input/>' ) /* Create hidden field with Excerpt */
								.attr( 'type', 'hidden' )
								.attr( 'id', 'excerpt-' + newLang )
								.attr( 'name', 'excerpt_' + newLang )
								.val( mltlnggNew.post_excerpt )
						);
					} else {
						/* when autosave is off and we come to post where multi has no date - do not lost default lang */
						mltlnggNew.post_title = $( 'input#title-' + newLang ).val();
						mltlnggNew.post_content = $( 'textarea#content-' + newLang ).val();
						mltlnggNew.post_excerpt = $( 'input#excerpt-' + newLang ).val();
					}
					inputTitle.val( mltlnggNew.post_title ); /* Set title to current language tab */
					/* Set content to current language tab */
					if ( tmceEnabled ) { /* If TinyMCE editor is active */
						tinymce.get('content').setContent( mltlnggNew.post_content );
					} else { /* If Text editor is active */
						inputContent.val( mltlnggNew.post_content );
					}

					$( '#excerpt' ).val( mltlnggNew.post_excerpt );
					for( var key in mltlnggNew.cat_translate ) {
						var html_object = $( '#category-' + key + ' label' );
						var old_cat = html_object.text();
						var content = html_object.html();
						content = content.replace( old_cat, ' ' + mltlnggNew.cat_translate[key] );
						$( 'li#category-' + key + ' label' ).html( content );
						$( 'li#category-' + key + '' ).append(
							$( '<input/>' ) /* Create hidden field with Categories */
							.attr( 'type', 'hidden' )
							.attr( 'id', 'cat-' + newLang + "-" + key )
							.attr( 'name', 'cat_' + newLang + "_" + key )
							.val( mltlnggNew.cat_translate[key] )
						);
					}
					if ( $( '#after-save-' + newLang ).length == 0 ) {
						$( getLangContentDiv ).append(
							$( '<input/>' ) /* Create hidden field with mark on the save */
							.attr( 'type', 'hidden' )
							.attr( 'id', 'after-save-' + newLang )
							.val( newLang )
							);
					}
					/* If Fusion Page Builder Plugin was enabled, reload it */
					if ( fusionPageBuilderIsEnabled ) {
						DdHelper.shortCodestoBuilderElements();
					}
				});
			}
			$( '#mltlngg-current-lang' ).text( newLangName );
			$( '#mltlngg-active-language' ).val( newLang ); /* Set new active language */
			return false;
		});

		function isFusionPageBuilderInstalled()
		{
			return ($('#fusion-page-builder').length > 0);
		}

		function isFusionPageBuilderEnabled()
		{
			var fusionPageBuilder = $('#fusion-page-builder');
			return (fusionPageBuilder.length > 0 && !fusionPageBuilder.hasClass('fusion-page-builder-hide'));
		}

		function reloadFusionPageBuilder()
		{
			// Wait until fusion page builder is completely loaded
			if (window.fusion_builder_fully_loaded != true) {
				setTimeout(reloadFusionPageBuilder, 100);
			} else {
				var fusionPageBuilderIsEnabled = isFusionPageBuilderEnabled();
				if (fusionPageBuilderIsEnabled) {
					DdHelper.shortCodestoBuilderElements();
				}
			}
		}
	});
})( jQuery );
