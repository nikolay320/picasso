
(function($){
var jq=jQuery;
/*
 * Thanks to http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery/2819568#2819568
 * for insertAtCaret function
 */
jq.fn.extend({

insertSmileyAtCaret: function(myValue){
  return this.each(function(i) {
    if (document.selection) {
      //For browsers like Internet Explorer
      this.focus();
      sel = document.selection.createRange();
      sel.animate = myValue;
      this.focus();
    }
    else if (this.selectionStart || this.selectionStart == '0') {
      //For browsers like Firefox and Webkit based
      var startPos = this.selectionStart;
      var endPos = this.selectionEnd;
      var scrollTop = this.scrollTop;
      this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
      this.focus();
      this.selectionStart = startPos + myValue.length;
      this.selectionEnd = startPos + myValue.length;
      this.scrollTop = scrollTop;
    } else {
      this.value += myValue;
      this.focus();
    }
  })
}
});
jq(document).ready(function(){
jq('.smiley').live('click',function(){
    
    var $this=$(this);
    var form=$this.parents('form').get(0);
    var where=jq(form).find('textarea').get(0);
    //console.log($this.html());
    var code=$this.attr('data-code');
    jq(where).insertSmileyAtCaret(''+code);
})

});

		$('.bp-smiley-button').live('click', function(){
		
	    $(this).hide();
		$('.bp-smiley-no').show();
		  $.ajax({
     url: ajaxurl,
	 type: 'post',
	 data: {'action': 'bp_sticker_ajax' },	
            success: function (html) {                
				  $('#sl').toggleClass ('smiley-buttons')
                  $(".smiley-buttons").html(html);
				  $(".bp-smiley-no").click(function() {                       
                         $(".divsti").remove();	
						 $('.bp-smiley-button').show();
						 $('.bp-smiley-no').hide();
						  $('#sl').removeClass ('smiley-buttons')
						 });
				 
				  
				  }
			});
	});	
	
	
		$('.bp-smiley-button-comment').live('click', function(event){	   
            var elem_var = String(jQuery(this).attr('rel')).split('_');           
            var elemID = elem_var[1];
		$('.bp-smiley-button-comment').hide();
		$('.bp-smiley-no-comment').show();

   $.ajax({
     url: ajaxurl,
	 type: 'post',
	 data: {'action': 'bp_sticker_ajax' },
        success:function (html) {
                  //$('#loading').removeClass('bpci-loading');
				  $('.sl-' + elemID + '-comm').addClass ('smiley-buttons')
                  $(".smiley-buttons").html(html);
				  $(".bp-smiley-no-comment").click(function() {                       
                         $(".divsti").remove();	
						 $('.bp-smiley-button-comment').show();
						 $('.bp-smiley-no-comment').hide();
						  $('.sl-' + elemID + '-comm').removeClass ('smiley-buttons')
						 });
				 $(".acomment-reply").click(function() {                       
                         $(".divsti").remove();	
						 $('.bp-smiley-button-comment').show();
						 $('.bp-smiley-no-comment').hide();
						  $('.sl-' + elemID + '-comm').removeClass ('smiley-buttons')
				});
				 
				  
				  }
     }); 

	});


})(jQuery);