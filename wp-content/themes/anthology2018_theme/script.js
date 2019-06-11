jQuery('document').ready(function($){

	$.fn.setHomeSliderHeight = function () { 
		var top = $('#menu').height();
		if(top < 500) top = 500;
		this.css('max-height', top+33);
	}

	$.fn.makeSquare = function() {	
		var theWidth = this.width();
		$('#visual-menu .col-sm-3').css('height', theWidth)
	}

	$('#home-slider').setHomeSliderHeight();	
	$('#img-header.large').setHomeSliderHeight();	
	$('#visual-menu .col-sm-3:eq(0)').makeSquare();

	$(window).resize(function(){
		$('#home-slider').setHomeSliderHeight();		
		$('#visual-menu .col-sm-3:eq(0)').makeSquare();		
	});
	
	var myTimeOut; 
	
	$('.menu-item-has-children').mouseenter(function() { 
		var position = $(this).position(); 
		$(this).children('.sub-menu').css('top', position.top);
		$(this).children('.sub-menu').show();
	}).mouseleave(function() { 
		var submenu = $(this).children(".sub-menu");
		myTimeOut = setTimeout(function() {
					closeSubMenu(submenu);
				   }, 200);
	});
	
	$('.sub-menu').mouseenter(function(){
		clearTimeout(myTimeOut);
	});
	
	function closeSubMenu(submenu) { 
		submenu.fadeOut();
	}
	
	$('#dealer-login a.dealer-header').click(function() {
		if($('#dealer-login-panel').css('display') == 'none') { 
			$('#dealer-login-panel').slideDown('slow');			
		} else { 
			$('#dealer-login-panel').slideUp('slow');			
		}
		return false;
	});
	
	$('#visual-menu .col-sm-3').each(function() {
		var img = $(this).children('img').attr('src');
		if (typeof(img) != "undefined") {
			$(this).css('background-image', "url('"+img+"')");
		}
	}); 
	

	$('#inspiration-gallery-home .col-sm-3').each(function() {
		var img = $(this).children('img').attr('src');
		if (typeof(img) != "undefined") {
			$(this).css('background-image', "url('"+img+"')");
		}
	}); 

	$('#user_login, #user_pass').addClass('form-control');
	$('.login-submit #wp-submit').addClass('btn');

	$('#visual-menu .col-sm-3').click(function() {
		var link = $(this).children('a').attr('href');
		document.location = link;
		return true;
	}); 

	$('#home-gallery').click(function() {
		var link = $(this).children('a').attr('href');
		document.location = link;
		return true;
	}); 
	
	$('.woocommerce-Address:first a.edit').remove();

});