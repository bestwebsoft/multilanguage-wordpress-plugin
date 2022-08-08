/* 
 * gutenberg script
 *
 * @since V1.3.6
 *
 * Notice: "Translation Saving Mode" plugin option is ignored. 
*/
(function( i18n, element, components, compose ) {
	(function( $ ) {

		$( window ).on( 'load', function() {
			var timescript = window.performance;
			/* React componenets we will needed */
			var getPost 					= wp.data.select( 'core/editor' ),
				editPost 					= wp.data.dispatch( 'core/editor' ),
				editNotice 					= wp.data.dispatch( 'core/notices' ),

				currentLang = mltlngg_vars.current_language,
				currentLangName;

			var onLangChange = function( newLang ) {
				
				var postData = getPost.getCurrentPost(),
					data;

				editNotice.removeNotice( 'mltlnggInfo' );
				editNotice.createNotice(
					'warning',
					i18n.__( 'Updating language', 'multilanguage' ) + ': ' + currentLangName + ' ... ',
					{
						id : 'mltlnggUpdate',
						isDismissible : false
					}
				);

				single = mltlngg_vars.active_language_list[newLang];
				currentLangName = single.name;

				editNotice.createNotice(
					'info',
					i18n.__( 'Edit for language', 'multilanguage' ) + ': ' + currentLangName,
					{
						id : 'mltlnggInfo',
						isDismissible : false
					}
				);

				var postEditedTitle 	= getPost.getEditedPostAttribute( 'title' ), /* Get the edited title from the Block editor */
					postEditedContent	= getPost.getEditedPostAttribute( 'content' ), /* Get the edited content from the Block editor */
					postEditedExcerpt	= getPost.getEditedPostAttribute( 'excerpt' ),/* Get the edited excerpt from the Block editor editor */
					postEditedMeta		= getPost.getEditedPostAttribute( 'meta' ); /* Get the edited meta data from the Block editor editor */
				data = {
					'action': 'mltlngg_ajax_callback',
					'is_gutenberg': true,
					'get_data': 'get_data',
					'new_lang': newLang,
					'old_lang': currentLang,
					'mltlngg_post_id': postData.id,
					'mltlngg_old_title': postEditedTitle,
					'mltlngg_old_content': postEditedContent,
					'mltlngg_old_excerpt': postEditedExcerpt,
					'security': mltlngg_vars.ajax_nonce
				};

				$.post( ajaxurl, data, function( response ) {			

					/* get only content in multilanguage comments */
					var regExp = /<!--mltlngg-ajax-results-->([^]+)<!--end-mltlngg-ajax-results-->/,
						matches = regExp.exec( response );
					if ( matches ) {
						response = matches[1];
					}

					let mltlnggNew = JSON.parse( response );

					let blocks = wp.blocks.parse( mltlnggNew.post_content );
					wp.data.dispatch( 'core/block-editor' ).resetBlocks( blocks );
					
					editPost.editPost( {
						title: mltlnggNew.post_title,
						excerpt: mltlnggNew.post_excerpt
					} );

					editNotice.removeNotice( 'mltlnggUpdate' );

					currentLang = newLang;

					is_saving = false;
				} );
			}

			/* Adding switcher to the Gutenberg editor */
			var dropdownMenu = function() {
				var mltlnggIcon = element.createElement( 'svg', { width: 24, height: 24 },
						element.createElement( 'path', { d: "M 15.394531 18.730469 L 17.832031 18.925781 L 16.992188 21.402344 L 16.40625 20.445312 C 15.453125 21.03125 14.320312 21.460938 13.285156 21.632812 C 12.957031 21.691406 12.546875 21.769531 12.195312 21.769531 L 11.21875 21.769531 C 9.972656 21.769531 7.671875 21.03125 6.65625 20.289062 C 6.519531 20.191406 6.480469 20.09375 6.480469 19.957031 C 6.480469 19.722656 6.65625 19.546875 6.871094 19.546875 C 6.96875 19.546875 7.125 19.625 7.515625 19.839844 C 7.613281 19.898438 7.710938 19.957031 7.75 19.976562 C 8.664062 20.425781 10.148438 20.953125 11.355469 20.953125 C 12.800781 20.953125 13.773438 20.757812 15.042969 20.210938 C 15.277344 20.113281 15.511719 19.976562 15.742188 19.839844 L 15.9375 19.722656 Z M 18.980469 0.75 C 19.117188 0.75 19.214844 0.847656 19.214844 1.003906 L 19.214844 5.859375 L 20.929688 6.40625 L 20.929688 18.984375 C 12.601562 16.320312 11.988281 16.128906 11.945312 16.117188 C 11.746094 16.195312 3.421875 19.003906 3.304688 19.003906 C 3.207031 19.003906 3.128906 18.945312 3.089844 18.847656 L 3.070312 18.808594 L 3.070312 6.269531 C 3.070312 6.230469 3.089844 6.171875 3.109375 6.152344 C 3.1875 6.054688 3.265625 6.035156 3.34375 6.015625 C 3.382812 5.996094 4.082031 5.761719 5.078125 5.429688 L 5.078125 0.964844 L 11.570312 3.265625 C 11.648438 3.246094 18.882812 0.75 18.980469 0.75 Z M 11.570312 4.105469 L 3.886719 6.816406 L 3.886719 17.890625 L 11.570312 15.355469 Z M 9.816406 8.550781 L 9.875 8.726562 C 9.894531 8.824219 9.875 9.117188 9.855469 9.175781 C 9.855469 9.214844 9.816406 9.273438 9.425781 10.070312 C 9.308594 10.285156 9.230469 10.480469 9.191406 10.539062 C 9.191406 10.558594 8.917969 11.105469 8.410156 11.902344 C 8.605469 11.980469 8.839844 12.078125 8.957031 12.136719 C 9.152344 12.234375 10.5 12.800781 10.578125 12.820312 C 10.808594 12.898438 10.886719 13.601562 10.867188 13.699219 C 10.847656 13.777344 10.789062 13.855469 10.65625 13.855469 C 10.539062 13.855469 10.363281 13.796875 10.011719 13.640625 C 9.953125 13.621094 9.914062 13.601562 9.875 13.582031 C 9.699219 13.503906 8.996094 13.113281 8.820312 12.996094 C 8.722656 12.9375 8.546875 12.800781 8.351562 12.644531 L 8.042969 12.410156 C 7.554688 13.152344 7.066406 13.816406 6.578125 14.378906 L 6.460938 14.515625 C 6.070312 14.964844 5.644531 15.472656 5.3125 15.6875 C 5.195312 15.765625 4.84375 15.765625 4.84375 15.765625 C 4.765625 15.765625 4.726562 15.765625 4.667969 15.707031 L 4.394531 15.570312 L 4.628906 15.394531 C 4.746094 15.296875 5.097656 14.886719 5.332031 14.613281 C 5.410156 14.515625 5.488281 14.417969 5.546875 14.359375 C 5.839844 14.03125 7.203125 12.117188 7.4375 11.691406 C 7.632812 11.320312 8.0625 10.460938 8.296875 9.992188 C 8.101562 10.050781 7.828125 10.148438 7.476562 10.265625 L 7.398438 10.285156 C 7.300781 10.324219 7.144531 10.363281 6.988281 10.402344 C 6.851562 10.441406 6.675781 10.480469 6.578125 10.519531 C 6.597656 10.578125 6.597656 10.636719 6.558594 10.695312 C 6.480469 10.851562 6.148438 10.949219 6.070312 10.96875 C 5.972656 11.007812 5.722656 11.046875 5.449219 10.96875 C 5.253906 10.910156 5.078125 10.714844 5.019531 10.636719 C 5 10.617188 4.960938 10.519531 4.941406 10.285156 L 4.941406 10.148438 L 5.078125 10.109375 C 5.15625 10.089844 5.253906 10.070312 5.371094 10.050781 L 5.476562 10.03125 C 5.554688 10.015625 5.644531 10 5.722656 9.972656 C 5.992188 9.894531 6.597656 9.71875 6.929688 9.601562 C 7.027344 9.5625 7.164062 9.523438 7.300781 9.464844 C 7.632812 9.347656 7.984375 9.234375 8.140625 9.195312 C 8.238281 9.195312 8.449219 9.078125 8.625 8.980469 L 8.761719 8.902344 C 8.894531 8.828125 9.03125 8.753906 9.132812 8.726562 C 9.230469 8.6875 9.308594 8.667969 9.386719 8.628906 C 9.523438 8.589844 9.582031 8.550781 9.660156 8.550781 Z M 15.859375 7.144531 L 13.34375 13.367188 L 14.535156 13.71875 L 15.0625 12.429688 L 17.519531 13.191406 L 17.949219 14.75 L 19.136719 15.140625 L 17.03125 7.496094 Z M 16.386719 9.136719 L 17.128906 11.765625 L 15.511719 11.28125 Z M 8.003906 7.59375 C 8.238281 7.59375 8.390625 7.730469 8.449219 7.945312 C 8.488281 8.199219 8.429688 8.335938 8.390625 8.394531 C 8.238281 8.707031 7.867188 8.882812 7.710938 8.941406 C 7.535156 9.019531 7.222656 9.097656 6.949219 9.097656 L 6.929688 9.097656 C 6.773438 9.078125 6.539062 9.019531 6.246094 8.707031 C 6.109375 8.550781 5.933594 8.140625 5.992188 7.984375 L 6.050781 7.867188 L 6.246094 7.867188 L 6.285156 7.90625 C 6.34375 7.925781 6.460938 7.945312 6.578125 7.945312 L 6.675781 7.945312 C 6.792969 7.925781 7.066406 7.847656 7.242188 7.789062 L 7.339844 7.769531 C 7.496094 7.710938 7.867188 7.613281 8.003906 7.59375 Z M 18.375 1.921875 L 12.46875 3.949219 L 18.375 5.703125 Z M 18.375 1.921875" }
					) );
				
				var languageChoice = [],
					i = 0;
				for ( const language in mltlngg_vars.active_language_list ) {
					var single = mltlngg_vars.active_language_list[language];
					languageChoice[i] = { 
						value: single.locale,
						label: single.name,
					};
					if ( language == currentLang )
						currentLangName = single.name;
					i++;					
				}
				function MyMenuItemsChoice( { lang, choices, setState } ) {
					return element.createElement( components.MenuItemsChoice, {
							value: lang,
							choices: choices,
							onSelect: function(value) {
								onLangChange( value );
								setState({ lang: value });
							}
						} )
					};

				return element.createElement(components.Dropdown, {
					className: 'mltlngg-components-dropdown',
					renderToggle: function(_ref) {
						var isOpen = _ref.isOpen,
		  					onToggle = _ref.onToggle;
						return element.createElement(components.Button, {
							icon: mltlnggIcon,
							onClick: onToggle,
							"aria-expanded": isOpen,
							label: i18n.__( 'Select Language', 'multilanguage' )
						}, currentLangName )
					},
					renderContent: function() {
						return element.createElement(element.Fragment, null, element.createElement(
							components.NavigableMenu, {
									role: "menu"
								}, 
								element.createElement(
									compose.withState({ 
										lang: currentLang,
										choices: languageChoice
									})( MyMenuItemsChoice )
								)
							),
							element.createElement("div", {
									className: "block-editor-tool-selector__help"
								},
								element.createElement(components.ExternalLink, {
									href: mltlngg_vars.add_new_lang_link
								}, i18n.__( 'Add Language', 'multilanguage' ) )
							)
						)
					}
				})
			};
			
			if( ! document.body.classList.contains( 'widgets-php' ) ) {
				setTimeout(function() {
					e = document.querySelector(".edit-post-header-toolbar");
					if (e) {
						var postData = getPost.getCurrentPost(),
							data;
						var postEditedTitle 	= getPost.getEditedPostAttribute( 'title' ), /* Get the edited title from the Block editor */
							postEditedContent	= getPost.getEditedPostAttribute( 'content' ), /* Get the edited content from the Block editor */
							postEditedExcerpt	= getPost.getEditedPostAttribute( 'excerpt' ),/* Get the edited excerpt from the Block editor editor */
							postEditedMeta		= getPost.getEditedPostAttribute( 'meta' ); /* Get the edited meta data from the Block editor editor */
						data = {
							'action': 'mltlngg_ajax_callback',
							'is_gutenberg': true,
							'get_data': 'get_data',
							'new_lang': mltlngg_vars.default_language,
							'mltlngg_post_id': postData.id,
							'mltlngg_old_title': postEditedTitle,
							'mltlngg_old_content': postEditedContent,
							'mltlngg_old_excerpt': postEditedExcerpt,
							'security': mltlngg_vars.ajax_nonce
						};

						$.post( ajaxurl, data, function( response ) {			

							/* get only content in multilanguage comments */
							var regExp = /<!--mltlngg-ajax-results-->([^]+)<!--end-mltlngg-ajax-results-->/,
								matches = regExp.exec( response );
							if ( matches ) {
								response = matches[1];
							}

							let mltlnggNew = JSON.parse( response );

							let blocks = wp.blocks.parse( mltlnggNew.post_content );
							wp.data.dispatch( 'core/block-editor' ).resetBlocks( blocks );
							
							editPost.editPost( {
								title: mltlnggNew.post_title,
								excerpt: mltlnggNew.post_excerpt
							} );

							currentLang = mltlngg_vars.default_language;

							is_saving = false;
						} );
						var t = document.createElement("div");
						e.appendChild(t), Object(element.render)(element.createElement(dropdownMenu, null), t)
					}

					editNotice.createNotice(
						'info',
						i18n.__( 'Edit for language', 'multilanguage' ) + ': ' + currentLangName,
						{
							id : 'mltlnggInfo',
							isDismissible : false
						}
					);
				}, timescript) ;
			}

			var is_saving = false;
			const unsubscribe = wp.data.subscribe( function() {
				if ( !is_saving ) {
					var isSavingPost = getPost.isSavingPost();
					var isAutosavingPost = getPost.isAutosavingPost();
					var didPostSaveRequestSucceed = getPost.didPostSaveRequestSucceed();

					if (isSavingPost && !isAutosavingPost && !is_saving) {
						is_saving = true;
						onLangChange( currentLang );
					}
				}
			});
		} );
	})( jQuery );
} )(
	window.wp.i18n,
	window.wp.element,
	window.wp.components,
	window.wp.compose
);