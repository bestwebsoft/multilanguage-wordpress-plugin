function mltlnggGoogleTranslateElementInit() {
	var options = {
		pageLanguage: mltlngg_lang_var.current_lang,
	};
	switch ( mltlngg_lang_var.layout ) {
		case 'dropdown':
			options.layout = google.translate.TranslateElement.InlineLayout.SIMPLE;
			break;
		case 'horizontal':
			options.layout = google.translate.TranslateElement.InlineLayout.HORIZONTAL;
			break;
		default:
	}
	new google.translate.TranslateElement( options, 'mltlngg_google_switcher' );
}