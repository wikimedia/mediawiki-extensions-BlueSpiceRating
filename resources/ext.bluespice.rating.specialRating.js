( ( $ ) => {

	$( () => {
		const $container = $( '#bs-rating-special-rating-container' ); // eslint-disable-line no-jquery/no-global-selector
		if ( $container.length === 0 ) {
			return;
		}

		const panel = new ext.bluespice.rating.panel.SpecialRatingPanel();

		$container.append( panel.$element );
	} );

} )( jQuery );
