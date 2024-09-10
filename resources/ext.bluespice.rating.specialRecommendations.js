( ( $ ) => {

	$( () => {
		const $container = $( '#bs-rating-special-recommendations-container' ); // eslint-disable-line no-jquery/no-global-selector
		if ( $container.length === 0 ) {
			return;
		}

		const panel = new ext.bluespice.rating.panel.SpecialRecommendationsPanel();

		$container.append( panel.$element );
	} );

} )( jQuery );
