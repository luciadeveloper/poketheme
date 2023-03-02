	jQuery(document).ready(function() { 
		var loaded = false;
		jQuery("#show_pokedex").click(function () {
			
			var post_id = jQuery(".pokemon").attr("data-post-id");

			if(loaded) return;

			jQuery.ajax({
				type: "POST",
				url: "/wp-admin/admin-ajax.php",
				data: {
					action: 'get_pokedex_number',
					message_id: post_id
				},
				success: function (data) {
					
					jQuery( "#pokedex_number" ).append( data.data.pokedex_number );
					jQuery( "#pokedex_name" ).append( data.data.pokedex_name );
					
				},
				});
				loaded = true;
			});
		});
