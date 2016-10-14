/**
*	jQuery.noticeAdd() and jQuery.noticeRemove()
*	These functions create and remove growl-like notices
*		
*   Copyright © 2009 Tim Benniks
*
*	Permission is hereby granted, free of charge, to any person obtaining a copy
*	of this software and associated documentation files (the "Software"), to deal
*	in the Software without restriction, including without limitation the rights
*	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
*	copies of the Software, and to permit persons to whom the Software is
*	furnished to do so, subject to the following conditions:
*
*	The above copyright notice and this permission notice shall be included in
*	all copies or substantial portions of the Software.
*
*	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
*	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
*	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
*	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
*	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
*	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
*	THE SOFTWARE.
*	
*	@author 	Tim Benniks <tim@timbenniks.com>
* 	@copyright  2009 timbenniks.com
*	@version    $Id: jquery.notice.js 1 2009-01-24 12:24:18Z timbenniks $
**/
( function( $ ) {

	$.extend({

		noticeAdd : function( options ) {

			var defaults = {
				inEffect :         { opacity: 'show' },
				inEffectDuration : 600,
				stayTime :         parseInt( myCRED_Notice.duration, 10 ),
				text :             '',
				stay :             true,
				type :             'positive',
				classes :          'notice-item'
			}

			// declare varaibles
			var options, noticeWrapAll, noticeItemOuter, noticeItemInner, noticeItemClose;

			options 		= $.extend( {}, defaults, options );
			noticeWrapAll	= $( '#mycred-notificiation-wrap' );
			noticeItemOuter	= $( '<div></div>' ).addClass( 'notice-item-wrapper' );
			noticeItemInner	= $( '<div></div>' ).hide().addClass( options.classes + ' ' + options.type ).appendTo( noticeWrapAll ).append( options.text ).animate( options.inEffect, options.inEffectDuration ).wrap( noticeItemOuter );
			noticeItemClose	= $( '<div></div>' ).addClass( 'notice-item-close' ).prependTo( noticeItemInner ).html( '&times;' ).click(function() { $.noticeRemove( noticeItemInner ) });

			// hmmmz, zucht
			if ( navigator.userAgent.match(/MSIE 6/i) ) {
				noticeWrapAll.css({top: document.documentElement.scrollTop});
			}

			if ( ! options.stay ) {
				setTimeout(function() {
					$.noticeRemove( noticeItemInner );
				}, options.stayTime );
			}

		},

		noticeRemove : function( obj ) {
			obj.animate({ opacity: '0' }, 200, function() {
				obj.parent().animate({ height: '0px' }, 100, function() {
					obj.parent().remove();
				});
			});
		}

	});

} )( jQuery );