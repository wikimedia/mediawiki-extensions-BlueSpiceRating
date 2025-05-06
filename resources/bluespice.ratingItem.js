bs.rating.Item = function ( $el, type, data ) {
	this.REF = 'rat_ref';
	this.REFTYPE = 'rat_reftype';
	this.SUBTYPE = 'rat_subtype';
	this.CONTEXT = 'rat_context';
	this.USERID = 'rat_userid';
	this.USERIP = 'rat_userip';
	this.VALUE = 'rat_value';

	this.RATINGS = 'ratings';
	this.USERRATINGS = 'userratings';

	OO.EventEmitter.call( this );
	this.$el = $el;
	this.type = type;
	this.votetask = 'vote';
	this.reloadtask = 'reload';
	this.taskApi = 'rating';
	this.data = new mw.Map();
	this.data.set( data );
};
OO.initClass( bs.rating.Item );
OO.mixinClass( bs.rating.Item, OO.EventEmitter );

bs.rating.Item.prototype.makeUiID = function () {
	const uiID = bs.rating.getUiID( this.getEl() );
	if ( uiID ) {
		return uiID;
	}
	if ( !this.$el.attr( 'id' ) || this.$el.attr( 'id' ).length < 1 ) {
		this.$el.attr( 'id', bs.rating.generateUniqueId() );
	}
	return bs.rating.getUiID( this.$el );
};
bs.rating.Item.prototype.reset = function ( data ) {
	this.getEl().attr( 'data-item', data );
	this.data = new mw.Map();
	this.data.set( JSON.parse( data ) );
};
bs.rating.Item.prototype.getEl = function () {
	return this.$el;
};
bs.rating.Item.prototype.getConfig = function () {
	return bs.rating.config[ this.getType() ];
};
bs.rating.Item.prototype.getType = function () {
	return this.type;
};
bs.rating.Item.prototype.getData = function () {
	const data = {};
	data[ this.REF ] = this.data.get( this.REF, '' );
	data[ this.SUBTYPE ] = this.data.get( this.SUBTYPE, 'default' );
	data[ this.REFTYPE ] = this.getType();
	data[ this.CONTEXT ] = this.data.get( this.CONTEXT, 0 );

	return data;
};
bs.rating.Item.prototype.getRatings = function () {
	const res = [];
	const ratings = this.data.get( this.RATINGS, {} );
	for ( const i in ratings ) {
		res.push( ratings[ i ] );
	}
	return res;
};
bs.rating.Item.prototype.getCurrentUserRatings = function () {
	const res = [];
	const ratings = this.data.get( this.USERRATINGS, {} );
	for ( const i in ratings ) {
		res.push( ratings[ i ] );
	}
	return res;
};
bs.rating.Item.prototype.filterRatings = function ( field, value, ratings ) {
	ratings = ratings || this.getRatings();
	if ( ratings.length < 1 ) {
		return ratings;
	}

	return $.grep( ratings, ( e ) => { // eslint-disable-line no-jquery/no-grep
		if ( typeof e[ field ] === 'undefined' ) {
			return false;
		}
		return e[ field ] === value;
	} );
};
bs.rating.Item.prototype.userVoted = function ( context ) {
	if ( mw.config.get( 'wgUserId', 0 ) === 0 ) {
		return false;
	}

	context = context || this.data.get( this.CONTEXT, '0' );
	context = String( context );
	const ratings = this.filterRatings(
		this.CONTEXT,
		context,
		this.getCurrentUserRatings()
	);

	return ratings.length > 0;
};
bs.rating.Item.prototype.getUserVotes = function ( sID, context ) {
	context = context || this.data.get( this.CONTEXT, '0' );
	context = String( context );
	sID = String( sID ); // needs to be string -.-
	let ratings = this.filterRatings(
		this.CONTEXT,
		context
	);
	if ( this.getConfig().IsAnonymous ) {
		return ratings;
	}
	ratings = this.filterRatings(
		this.USERID,
		sID,
		ratings
	);
	return ratings;
};
bs.rating.Item.prototype.getVoteCount = function ( context ) {
	context = context || this.data.get( this.CONTEXT, '0' );
	context = String( context );
	const ratings = this.filterRatings(
		this.CONTEXT,
		context
	);
	return ratings.length;
};
bs.rating.Item.prototype.getVoteTotal = function ( context ) {
	context = context || this.data.get( this.CONTEXT, '0' );
	context = String( context );
	let total = 0;
	const ratings = this.filterRatings(
		this.CONTEXT,
		context
	);
	for ( let i = 0; i < ratings.length; i++ ) {
		total = total + parseInt( ratings[ i ][ this.VALUE ] );
	}
	return total;
};
bs.rating.Item.prototype.getVoteAverage = function () {
	const total = this.getVoteTotal();
	const count = this.getVoteCount();
	if ( total < 1 ) {
		return total;
	}
	return total / count;
};
bs.rating.Item.prototype.getVoteTask = function () {
	return this.voteTask || 'vote';
};
bs.rating.Item.prototype.getReloadTask = function () {
	return this.reloadTask || 'reload';
};
bs.rating.Item.prototype.getTaskApi = function () {
	return this.taskApi;
};

bs.rating.Item.prototype.vote = function ( rating ) {
	const data = this.getData();
	data[ this.VALUE ] = rating;
	return bs.api.tasks.execSilent(
		this.getTaskApi(),
		this.getVoteTask(),
		data
	);
};
bs.rating.Item.prototype.reload = function () {
	const data = this.getData();
	this.addLoadingMask();
	return bs.api.tasks.execSilent(
		this.getTaskApi(),
		this.getReloadTask(),
		data
	).done( ( result ) => {
		if ( !result.success ) {
			return;
		}
		this.reset( result.payload.data );
		this.removeLoadingMask();
	} );
};
bs.rating.Item.prototype.addLoadingMask = function () {
	this.getEl().append(
		'<div class="bs-rating-loading-overlay">' +
		'<div class="bs-rating-loader"></div>' +
		'</div>'
	);
};
bs.rating.Item.prototype.removeLoadingMask = function () {
	this.getEl().find( '.bs-rating-loading-overlay' ).remove();
};

bs.rating.register( 'base', '\\BlueSpice\\Rating\\RatingItem', bs.rating.Item );
