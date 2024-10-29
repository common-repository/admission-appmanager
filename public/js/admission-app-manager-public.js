(function( $ ) {
	'use strict';

	$(document).ready(function() {
        $('.aam-progress-bar').each(function() {
            $(this).find('span').animate({
                width:$(this).attr('data-completion') + '%'
            }, 1000);
        });
    });

})( jQuery );
