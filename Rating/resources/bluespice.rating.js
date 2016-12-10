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

OO = window.OO; //To make OOJS available within the RL modules
window.bs = window.bs || {};
window.bs.rating = window.bs.rating || {};
bs.rating.factory = new OO.Factory();

bs.rating.Item = function( $el, config ) {
	OO.EventEmitter.call( this );
	this.$el = $el;
	this.config = new mw.Map( config );
	this.task = 'vote';
	this.taskApi = 'rating';
};
OO.initClass( bs.rating.Item );
OO.mixinClass( bs.rating.Item, OO.EventEmitter );

bs.rating.Item.static.name = 'RatingItem';

bs.rating.Item.prototype.getEl = function() {
	return this.$el;
};

bs.rating.Item.prototype.getConfig = function() {
	return this.config;
};

bs.rating.Item.prototype.getTask = function() {
	return this.task;
};
bs.rating.Item.prototype.getTaskApi = function() {
	return this.taskApi;
};

bs.rating.Item.prototype.vote = function( rating ) {
	console.log(rating);
	return bs.api.tasks.execSilent(
		this.getTaskApi(),
		this.getTask(),
		{}
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
bs.rating.factory.register( bs.rating.Item );

/**
 *
 * @param config
 * @constructor
 */
bs.rating.ItemArticle = function( $el, config ) {
	bs.rating.Item.call( this, $el, config );
	var me = this;
	var $rating = $el.starRating({
		starSize: 14,
		useFullStars: true,
		disableAfterRate: false,
		strokeWidth: 9,
		strokeColor: 'black',
		initialRating: 0,
		starGradient: {
			start: '#93BFE2',
			end: '#105694'
		},
		callback: function(currentRating, $el){
			$el.starRating( 'setReadOnly', true );
			me.addLoadingMask( $el );
			me.vote( currentRating ).done( function( result ) {
				console.log(result);
				$el.starRating( 'setReadOnly', false );
				me.removeLoadingMask( $el );
			});
		}
	});
};

OO.inheritClass( bs.rating.ItemArticle, bs.rating.Item );
bs.rating.ItemArticle.static.name = 'RatingItemArticle';
bs.rating.factory.register( bs.rating.ItemArticle );

bs.rating.config = mw.config.get('BSRatingConfig', {});
bs.rating.init = function(){
	$(".bs-rating").each( function() {
		var config = JSON.parse($(this).attr('data-item'));
		var ratingitem = bs.rating.factory.create(
			'RatingItemArticle',
			$(this),
			config
		);
		console.log(ratingitem);
	});
};

var aModules = mw.config.get( 'BSSocialModuleScripts', [] );
mw.loader.using( aModules ).done(function() {
	bs.rating.init();
});