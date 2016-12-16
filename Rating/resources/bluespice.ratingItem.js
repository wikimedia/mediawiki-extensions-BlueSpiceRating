/**
 * Js for Rating extension
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    2.27.0
 * @package    BluespiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

bs.rating.Item = function( $el, type, data ) {
	OO.EventEmitter.call( this );
	this.$el = $el;
	this.type = type;
	this.task = 'vote';
	this.taskApi = 'rating';
	this.data = new mw.Map( data );
};
OO.initClass( bs.rating.Item );
OO.mixinClass( bs.rating.Item, OO.EventEmitter );

bs.rating.Item.prototype.reset = function( data ) {
	this.getEl().attr( 'data-item', data );
	this.data = new mw.Map( JSON.parse( data ) );
};
bs.rating.Item.prototype.getEl = function() {
	return this.$el;
};
bs.rating.Item.prototype.getConfig = function() {
	return bs.rating.config.get( this.getType() );
};
bs.rating.Item.prototype.getType = function() {
	return this.type;
};
bs.rating.Item.prototype.getData = function() {
	return {
		ref: this.data.get( 'ref', '' ),
		subtype: this.data.get( 'subtype', 'default' ),
		reftype: this.getType(),
		context: this.data.get( 'context', 0 )
	};
};
bs.rating.Item.prototype.filterRatings = function( field, value ) {
	var ratings = [];
	var oRatings = this.data.get( 'ratings', {});
	for( var k in oRatings ) {
		ratings.push( oRatings[k] );
	}

	return $.grep( ratings, function( e, i ) {
		if( typeof e[field] === 'undefined' ) {
			return false;
		}
		return e[field] === value;
	});
};
bs.rating.Item.prototype.getVoteCount = function() {
	var ratings = this.filterRatings(
		'context',
		this.data.get( 'context', '0' )
	);
	return ratings.length;
};
bs.rating.Item.prototype.getVoteTotal = function() {
	var total = 0;
	var ratings = this.filterRatings(
		'context',
		this.data.get( 'context', '0' )
	);
	for( var i = 0; i < ratings.length; i++ ) {
		total = total + parseInt( ratings[i].value );
	}
	return total;
};
bs.rating.Item.prototype.getVoteAverage = function() {
	var total = this.getVoteTotal();
	var count = this.getVoteCount();
	if( total < 1 ) {
		return total;
	}
	return total / count;
};
bs.rating.Item.prototype.getTask = function() {
	return this.task;
};
bs.rating.Item.prototype.getTaskApi = function() {
	return this.taskApi;
};

bs.rating.Item.prototype.vote = function( rating ) {
	var data = this.getData();
	data.value = rating;
	return bs.api.tasks.execSilent(
		this.getTaskApi(),
		this.getTask(),
		data
	);
};
bs.rating.Item.prototype.addLoadingMask = function() {
	this.getEl().append(
		'<div class="bs-rating-loading-overlay">'
		+ '<div class="bs-rating-loader"></div>'
		+ '</div>'
	);
};
bs.rating.Item.prototype.removeLoadingMask = function() {
	this.getEl().find('.bs-rating-loading-overlay').remove();
};

bs.rating.register( 'base', 'RatingItem', bs.rating.Item );