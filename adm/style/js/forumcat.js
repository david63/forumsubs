;(function($, document)
{
	'use strict';

	$('tr.forum-cat').click(function(){
    	$(this).nextUntil('tr.forum-cat').css('display', function(i,v){
        return this.style.display === 'table-row' ? 'none' : 'table-row';
    	});
	});

	$('div.forum-cat').click(function(){
    	$(this).nextUntil('div.forum-cat').css('display', function(i,v){
        return this.style.display === 'table-row' ? 'none' : 'table-row';
    	});
	});
})(jQuery, document);
