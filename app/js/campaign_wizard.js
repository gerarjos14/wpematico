

jQuery(document).ready(function($){
	var cont_wizard = 0;
	var wizard_name_array = [];
	var wizard_class_array =[];
	var wizard_id_array = [];
	if(cont_wizard==0) {
		$("#prev_wizard").hide(0);
	} 
	
	//button custom tytle
	$(".wp-heading-inline").after('<a href="#wizard" class="page-title-action thickbox_open">'+wpematico_object.text_wizard+'</a>');

	function center_function_wizard(){
		var window_height = $(window).height();
		var window_width = $(window).width();

		var div_height = $('#thickbox_wizard').height();
		var div_width = $('#thickbox_wizard').width();
		$('#thickbox_wizard').css('margin-top',(window_height/2)-(div_height/2)).css('margin-left',(window_width /2)-(div_width/2) );
	}

	$(window).resize(function(){center_function_wizard();});

	function each_metabox_wizard() {
		$cont_wizard = 0;
		$(".postbox").each(function(i){
						
			if ($(this).find('h2 span').text().length>0  && jQuery(this).is(':visible') && !jQuery(this).is(':hidden')) {
							
				$(this).attr("wizard","wizard_metabox_"+$cont_wizard);
				$(this).addClass("wizard_metabox_"+$cont_wizard);
				//save data array name
				wizard_name_array.push($(this).find('h2 span').text());
				//save data class array
				wizard_class_array.push("wizard_metabox_"+$cont_wizard);
				//save data ID array
				wizard_id_array.push($(this).attr("id"));

				$cont_wizard++;
			}	
		});
	}
		//sort array
	function sort_array_wizard(){
					
		temp_wizard_array_name = new Array();
		temp_wizard_class_name = new Array();
		temp_wizard_id_array = new Array();
					
		for(i=0;i<wizard_name_array.length;i++){
			if (wizard_id_array[i] == 'campaign_types') {
				temp_wizard_array_name.push(wizard_name_array[i]);
				temp_wizard_class_name.push(wizard_class_array[i]);
				temp_wizard_id_array.push(wizard_id_array[i]);
			}
		}
		for(i=0;i<wizard_name_array.length;i++){
			if (wizard_id_array[i] == 'feeds-box') {
				temp_wizard_array_name.push(wizard_name_array[i]);
				temp_wizard_class_name.push(wizard_class_array[i]);
				temp_wizard_id_array.push(wizard_id_array[i]);
			}
		}
					
		for(i=0;i<wizard_name_array.length;i++){
			if (wizard_id_array[i] != 'submitdiv' && wizard_id_array[i] != 'feeds-box' && wizard_id_array[i] != 'campaign_types') {
				temp_wizard_array_name.push(wizard_name_array[i]);
				temp_wizard_class_name.push(wizard_class_array[i]);
				temp_wizard_id_array.push(wizard_id_array[i]);
			}
		}
		for(i=0;i<wizard_name_array.length;i++){
			if (wizard_id_array[i] == 'submitdiv') {
				temp_wizard_array_name.push(wizard_name_array[i]);
				temp_wizard_class_name.push(wizard_class_array[i]);
				temp_wizard_id_array.push(wizard_id_array[i]);
			}
		}
			//closed for
			//sort original array
		wizard_name_array = new Array();
		wizard_class_array = new Array();
		wizard_id_array = new Array();
		for(j=0;j<temp_wizard_array_name.length;j++){
			wizard_name_array[j] = temp_wizard_array_name[j];
			wizard_class_array[j] = temp_wizard_class_name[j];
			wizard_id_array[j] = temp_wizard_id_array[j];
		}

	}//closed function
	function clear_list_wizard(){
		$("#temp_postbox").find(">div.inside").each(function(i){
			class_wizard = $(this).attr("wizard");
			$(this).appendTo("."+class_wizard);
		});
	}
	function events_submit_post_wizard($) {
		var $submitButtons = $('#temp_postbox').find(':submit, a.submitdelete, #post-preview');
		$submitButtons.click(function(e) {
			e.preventDefault();
			events_wizard_popup_close();
			$('#post').submit();
		});
	}
	function events_wizard_popup_close() {
		$(".title_wizard").find("#titlewrap").appendTo("#post-body-content #titlediv");
		jQuery('#thickbox_wizard .postbox').css({'height':'30vh'});
		$("#temp_postbox").find(">div.inside").each(function(i){
			class_wizard = $(this).attr("wizard");
			$(this).appendTo("."+class_wizard);						
		});
		$("#thickbox_wizard").slideUp(500,function(){
			$("#prev_wizard").hide(0);
			$("#next_wizard").show(0);
			$("#wizard_mask").fadeOut(500);
			cont_wizard=0;
		});
		//We will delete all elements of classes and names
		for($i=0; $i<wizard_class_array.length; $i++){
			$('.postbox').removeClass(wizard_class_array[$i]);
			$('.postbox').find(">div.inside").removeClass(wizard_class_array[$i]);
			wizard_class_array[$i] = null;
			wizard_name_array[$i] = null;
			wizard_id_array[$i] = null;
		}
		wizard_class_array.length = 0;
		wizard_name_array.length = 0;
		wizard_id_array.length = 0;
		$('.postbox').removeAttr('wizard');
		$("#temp_postbox").find('h2.temp_uisortable span').text("");
	}
	
	jQuery(document).on('click','#next_wizard',function(){
		cont_wizard++;
		tam_array_metabox = parseInt(wizard_class_array.length);
		if(cont_wizard<=tam_array_metabox){
			color_background_title_wizard = $("."+wizard_class_array[cont_wizard]).find("h2.ui-sortable-handle").css("background-color");
			//clear list
			clear_list_wizard();
			//show prev wizard
			$("#prev_wizard").show(0);
			$("."+wizard_class_array[cont_wizard]).find('>div.inside').attr("wizard",$("."+wizard_class_array[cont_wizard]).attr("wizard"));
			$("."+wizard_class_array[cont_wizard]).find('>div.inside').appendTo("#temp_postbox");
			events_submit_post_wizard($);
			$('h2.postbox-title').attr('data-background-color','postbox-'+wizard_id_array[cont_wizard]);
			$(".temp_uisortable span").text($("."+wizard_class_array[cont_wizard]).find('h2 span').text());
			//$(".temp_uisortable").css({'background':''+$("."+wizard_class_array[cont_wizard]).find("h2.ui-sortable-handle").css("background")+''});
			//$(".temp_uisortable").css({'background-color':color_background_title_wizard});
			$(".temp_uisortable").css('background-color',color_background_title_wizard);
			//help line
			$(".help_wizard").text('').html($("."+wizard_class_array[cont_wizard]).find('h2 span span').attr("title-heltip"));
			if ($(".help_wizard").text() != '') {
				jQuery('.wpematico_divider_list_wizard').show(); 
				jQuery('#thickbox_wizard .postbox').css({'height':'30vh'});
							
			} else {
				jQuery('.wpematico_divider_list_wizard').hide(); 
				jQuery('#thickbox_wizard .postbox').css({'height':'42vh'});
			}
			jQuery('#tiptip_holder').fadeOut();
			if((cont_wizard+1)>=tam_array_metabox) $(this).hide(0);

		}
	});//close nextWizard
		
	jQuery(document).on('click','#prev_wizard',function(){
		clear_list_wizard();
		cont_wizard--;
		color_background_title_wizard = $("."+wizard_class_array[cont_wizard]).find("h2.ui-sortable-handle").css("background-color");
		$("#next_wizard").show(0);
		$("."+wizard_class_array[cont_wizard]).find('>div.inside').attr("wizard",$("."+wizard_class_array[cont_wizard]).attr("wizard"));
		$("."+wizard_class_array[cont_wizard]).find('>div.inside').appendTo("#temp_postbox");
		$('h2.postbox-title').attr('data-background-color','postbox-'+wizard_id_array[cont_wizard]);
		$(".temp_uisortable span").text($("."+wizard_class_array[cont_wizard]).find('h2 span').text());
		$(".temp_uisortable").css('background-color',color_background_title_wizard);
		//help line
		$(".help_wizard").text('').html($("."+wizard_class_array[cont_wizard]).find('h2 span span').attr("title-heltip"));
		if ($(".help_wizard").text() != '') {
			jQuery('.wpematico_divider_list_wizard').show(); 
			jQuery('#thickbox_wizard .postbox').css({'height':'30vh'});
							
		} else {
			jQuery('.wpematico_divider_list_wizard').hide(); 
			jQuery('#thickbox_wizard .postbox').css({'height':'42vh'});
		}
		jQuery('#tiptip_holder').fadeOut();
		if(cont_wizard<=0) $(this).hide(0);

	});//close prevWizard


	jQuery(document).on('click','.thickbox_open',function(e){
		each_metabox_wizard();
		sort_array_wizard();
		disable_run_now();
		color_background_title_wizard = $("."+wizard_class_array[cont_wizard]).find("h2.ui-sortable-handle").css("background-color");
		$("#wizard_mask").fadeIn(500,function(){
			center_function_wizard();
			$("#thickbox_wizard").slideDown(500,function(){
				$("#titlewrap").appendTo(".title_wizard");
				$("."+wizard_class_array[0]).find('>div.inside').attr("wizard",$("."+wizard_class_array[0]).attr("wizard"));
				$("."+wizard_class_array[0]).find('>div.inside').appendTo("#temp_postbox");
				$(".temp_uisortable span").text($("."+wizard_class_array[0]).find('h2 span').text());
				$(".temp_uisortable").css('background-color',color_background_title_wizard);
				$(".help_wizard").text('').html($("."+wizard_class_array[0]).find('h2 span span').attr("title-heltip"));
				if ($(".help_wizard").text() != '') {
					jQuery('.wpematico_divider_list_wizard').show(); 
					jQuery('#thickbox_wizard .postbox').css({'height':'30vh'});
								
				} else {
					jQuery('.wpematico_divider_list_wizard').hide(); 
					jQuery('#thickbox_wizard .postbox').css({'height':'42vh'});
				}
				jQuery('#tiptip_holder').fadeOut();
			});
		});
		e.preventDefault();
	});


	jQuery(document).on('click',".closed_wizard,#wizard_mask",function(){
		events_wizard_popup_close();
	});

});//Close jquery