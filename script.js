jQuery(document).ready(function() {
	
	var loading;
	var results;
	var display;

		jQuery("div[id='domain-form']").on("submit", function(){	
			var form = this;
		
			if(jQuery("input[name='domain']",form).val() == "")
				{alert('please enter your domain');return false;}

			var domain = jQuery("input[name='domain']",form).val();
			jQuery("div[id='results']",form).css('display','none');
			jQuery("div[id='results']",form).html('');
			jQuery("div[id='loading']",form).css('display','inline');
			var data = {
		      		'action': 'wdc_display',
		      		'domain': domain,
		      		'security' : wdc_ajax.wdc_nonce
		    		};
			jQuery.post(wdc_ajax.ajaxurl, data, function(response) {
			var x = JSON.parse(response);
				if(x.status == 1){
					display = x.text;
					link = '';
				}else if(x.status == 0) {
					display = x.text;
				}else{
					display = "Error occured.";
				}
			jQuery("div[id='results']",form).css('display','block');
			jQuery("div[id='loading']",form).css('display','none');
			jQuery("div[id='results']",form).html(unescape(display));

		});
		return false;
	});
	
});