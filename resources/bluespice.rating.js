// To make OOJS available within the RL modules
OO = window.OO; // eslint-disable-line no-global-assign, no-implicit-globals
window.bs = window.bs || {};
window.bs.rating = window.bs.rating || {};
bs.rating.factory = new OO.Factory();
bs.rating.types = {};
bs.rating.itemStore = {};
bs.rating.register = function ( sType, sStaticName, oClass ) {
	oClass.static.name = sStaticName;
	bs.rating.factory.register( oClass );
	bs.rating.types[ sType ] = sStaticName;
};
bs.rating.uuid = 0;
bs.rating.generateUniqueId = function () {
	return 'bsr-ui-' + ( ++bs.rating.uuid );
};
const config = require( './config.json' );
bs.rating.config = config.ratingConfig;
bs.rating.getUiID = function ( $el ) {
	if ( $el.attr( 'id' ) && $el.attr( 'id' ).length > 0 ) {
		return $el.attr( 'id' );
	}
	return false;
};
bs.rating.getFromStore = function ( uiID ) {
	if ( !uiID || !bs.rating.itemStore[ uiID ] ) {
		return null;
	}
	return bs.rating.itemStore[ uiID ];
};

bs.rating.newFromEl = function ( $el ) {
	if ( !$el || $el.length < 1 ) {
		return null;
	}
	const uiID = bs.rating.getUiID( $el );
	const item = bs.rating.getFromStore( uiID );
	if ( item ) {
		return item;
	}
	return bs.rating.createFromEl( $el );
};
bs.rating.createFromEl = function ( $el ) {
	if ( !$el || $el.length < 1 ) {
		return null;
	}
	let type = $el.attr( 'data-type' );
	if ( !type || type === '' || typeof type === 'undefined' ) {
		type = 'base';
	}

	if ( typeof bs.rating.types[ type ] === 'undefined' ) {
		throw new Error( `Unregistered type: ${ type }` );
	}

	if ( typeof $el.attr( 'data-item' ) === 'undefined' ) {
		throw new Error( "The field 'data-item' is missing." );
	}

	const data = JSON.parse( $el.attr( 'data-item' ) );

	const ratingitem = bs.rating.factory.create(
		bs.rating.types[ type ],
		$el,
		type,
		data
	);
	bs.rating.itemStore[ ratingitem.makeUiID() ] = ratingitem;
};
bs.rating.init = function () {
	$( '.bs-rating' ).each( function () {
		if ( bs.rating.getUiID( $( this ) ) ) {
			// already exists
			return null;
		}
		bs.rating.createFromEl( $( this ) );
	} );
};

mw.loader.using( config.ratingModules ).done( () => {
	bs.rating.init();
} );
