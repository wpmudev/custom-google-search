(function($){
	$( document ).ready(function(){
		var cgs_settings_style = $( '#settings_style' );

		$( '#settings_style' ).on( 'change', function(){
			var search_results_option = $( 'tr#cgs-search-results-page' );
			var show_sidebar_option = $( 'tr#cgs-show-sidebar' );

			$( '#cgs_form' ).submit( function () {
	            if ( '' == $( '#settings_embed_code' ).val() ) {
	                $( '#settings_embed_code' ).parent().parent().css( 'background-color', '#FFEBE8' );
	                $( '#settings_embed_code' ).focus();
	                return false;
	            }
	            return true;
	        });

			if( $(this).val() == 'custom_page' ){
				search_results_option.show( '300', function(){
					search_results_option.removeClass( 'hidden' );
				});
			}
			else{
				search_results_option.hide( '300', function(){
					search_results_option.addClass( 'hidden' );
				});
			}

			if( $(this).val() == 'generated' ){
			show_sidebar_option.show( '300', function(){
					show_sidebar_option.removeClass( 'hidden' );
				});
			}
			else{
				show_sidebar_option.hide( '300', function(){
					show_sidebar_option.addClass( 'hidden' );
				});
			}

		});
	});
})(jQuery)
