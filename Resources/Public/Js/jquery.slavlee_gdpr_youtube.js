// Create closure.
(function( $ ) { 
    // Plugin definition.
    $.fn.slavlee_gdpr_youtube = function( options ) {
        /*************************************************************************
         * PUBLIC Methods - START
         ************************************************************************/
        
        /*************************************************************************
         * PUBLIC Methods - END
         ************************************************************************/
        
        /*************************************************************************
         * MAGIC - START
         ************************************************************************/
        var cObj = this;
        
        $(cObj).find("img").click(function(){
        	var iframe = atob($(this).parent().attr("data-iframe"));
        	var parent = $(this).parents(".slavleeYouTube-iframe:first");
        	
        	if (parent && iframe)
    		{
        		// Set iframe to HTML
        		// check if there are elements before the iframe
        		if (parent.prev().length > 0)
    			{
        			// if there are elements, then append to immediate prev sibling
        			parent.prev().first().after(iframe);
    			}else
				{
    				// if there are no elements before, then prepend to parent
    				parent.parent().prepend(iframe);    				
				}
        		
        		// Remove Slavlee YouTube Wrapper
        		parent.parent().addClass("slavleeYouTube-animStart");
        		parent.fadeOut(function(){
        			$(this).parent().removeClass("slavleeYouTube-animStart");
        			$(this).remove();        			
        		});
    		}
        });
        /*************************************************************************
         * MAGIC - START
         ************************************************************************/
    };
    
    $(document).ready(function(){
    	$(".slavleeYouTube-iframe").slavlee_gdpr_youtube();
    });
// End of closure.
})( jQuery );