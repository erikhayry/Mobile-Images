if(typeof(jQuery)!="undefined"){
	jQuery(document).ready(function() {
		toggleCssClassField(jQuery('#option_add_css').prop('checked'));
	
		function toggleCssClassField(checked){
			if(checked){
				jQuery('#option_add_css_class_wrapper').removeClass('disabled');
				jQuery('#option_add_css_class').prop('disabled', false);
			}
			else{
				jQuery('#option_add_css_class_wrapper').addClass('disabled');
				jQuery('#option_add_css_class').prop('disabled', true);
			}
		}
		
		jQuery('#option_add_css').change(function(){
			toggleCssClassField(this.checked);
		});
		
		jQuery('#option_add_css_class').focus(function(){
			jQuery(this).keyup(function(){
				if(this.value == ''){
					jQuery('#content-class').text('entry-content');
				}
				else{
					jQuery('#content-class').text(this.value);				
				}
			});
		});
		
	});
}	