( ( $ ) => {

	$( () => {
		const $container = $( '#bs-rating-special-rating-container' );
		if ( $container.length === 0 ) {
			return;
		}

		const panel = new ext.bluespice.rating.panel.SpecialRatingPanel();

		$container.append( panel.$element );
	} );

} )( jQuery );
