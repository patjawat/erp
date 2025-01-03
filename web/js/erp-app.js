$(document).ready(function(){
	var scrollBarCont, isfullscreen = false, ddSliderIns;
	$("#date").text(new Date().getFullYear())
	setTimeout(function(){
		$(".preloader").addClass("d-none");
	}, 1000)
	$("#layout").on("click", function(){
		$(".setting-sidebar").addClass("show");
	})
	$(".setting-sidebar .mdi-close").on("click", function(){
		$(".setting-sidebar").removeClass("show");
	})
	$('[data-effect="wave"]').addClass("waves-effect waves-light");
	$('[data-effect="wave-dark"]').addClass("waves-effect waves-float");
	if($('[data-toggle="sidebar"]').length){
		$('[data-toggle="sidebar"]').on("click", function(e){
			e.stopPropagation();
			$('[data-sidebar="mobile"]').toggleClass("show-mobile-sidebar");
		})
	}

	$('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
		if (!$(this).next().hasClass('show')) {
			$(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
		}
		var $subMenu = $(this).next(".dropdown-menu");
		$subMenu.toggleClass('show');

		return false;
});
	$("body").on("click", function(e) {
		if ($(e.target).closest('.side-navbar').length > 0 || $(e.target).closest('[data-sidebar="mobile"]').length > 0) {
			return;
		}
		$("body").removeClass("show-sidebar");
		if($('[data-sidebar="mobile"]').length){
			$('[data-sidebar="mobile"]').removeClass("show-mobile-sidebar")
		}
	});
	Waves.init();
	if($('[data-bs-toggle="tooltip"]').length){
		const tooltips = document.querySelectorAll("[data-bs-toggle='tooltip']");
		tooltips.forEach(function(tooltip) {
			new bootstrap.Tooltip(tooltip)  
		})
		// $('[data-bs-toggle="tooltip"]').tooltip()
	}
	if($('[data-bs-toggle="popover"]').length){
		const popovers = document.querySelectorAll("[data-bs-toggle='popover']");
		popovers.forEach(function(popover) {
			new bootstrap.Popover(popover)  
		})
	}
	if (!$("body").hasClass("horizontal-navbar")) {
		if (document.getElementById("metismenu")) {
			scrollBarCont = Scrollbar.init(document.getElementById("metismenu"));
		}
	}
	$('header .dropdown-mega').on('show.bs.dropdown', function () {
		if(!ddSliderIns){
			setTimeout(function(){
				//Mega dropdown slider
				megaDDSlider();
			}, 200)
		}
	})
	if (document.getElementById("notify-scrollbar")) {
		Scrollbar.init(document.getElementById("notify-scrollbar"));
	}
	$('#side-menu').metisMenu().on('shown.metisMenu', function (event) {
		if (scrollBarCont) scrollBarCont.update()
	}).on('hidden.metisMenu', function (event) {
		if (scrollBarCont) scrollBarCont.update()
	})
	if ($("#dashdaterange").length) {
		$("#dashdaterange").flatpickr({
			convertModelValue: true,
			mode: "range",
			dateFormat: 'd-m-y'
		});
	}

	$(".layout-nav .side-nav-link").on("click", function () {
		var bodyclass = $(this).attr("data-class");
		$('body').removeClass().addClass("pace-done " + bodyclass);
		var classList = document.body.classList.value.trim();
		localStorage.setItem("classes", classList);
	})

	$("#full-screen").on("click", function () {
		$(this).children().toggleClass("bx-fullscreen bx-exit-fullscreen");
		if (!isfullscreen) {
			isfullscreen = fullScreen(isfullscreen);
		}
		else {
			isfullscreen = exitFullScreen(isfullscreen);
		}
	});
	$("#vertical-menu-btn").on("click", function (e) {
		e.preventDefault();
		e.stopPropagation();
	
		if($(window).width() > 1024) {
			if ($("body").hasClass("left-side-menu-condensed")) {
				$("body").removeClass("left-side-menu-condensed");
			}
			else {
				$("body").addClass('left-side-menu-condensed');
			}
			var classList = document.body.classList.value.trim();
			localStorage.setItem("classes", classList);
		}
		else {
			$("body").toggleClass("show-sidebar");
		}
	});

	$("#countries .dropdown-item").on("click", function () {
		var icon = $(this).children("img").attr("src");
		var lang = $(this).children("span").attr("data-lang");
		lang = lang.toUpperCase()
		$("#page-header-country-dropdown").children("img").attr("src", icon)
		$("#page-header-country-dropdown").children("span").text(lang)
	})

	$(".floating-label .form-control").on("change", function () {
		if ($(this).val() !== "") {
			$(this).parents(".floating-label").addClass("enable-floating-label");
		}
		else {
			if (!$(this).parent().hasClass("show-label")) {
				$(this).parents(".floating-label").removeClass("enable-floating-label");
			}
		}
	})

	$(".input-date .form-control").on("focus", function () {
		$(this).parent().find(".date-icon").addClass("active")
	})

	$(".input-date .form-control").on("focusout", function () {
		$(this).parent().find(".date-icon").removeClass("active")
	})

	// activate the menu in left side bar (Vertical Menu) based on url
	$("#side-menu a").each(function () {
		var pageUrl = window.location.href.split(/[?#]/)[0];
		if (this.href == pageUrl) {
			$(this).addClass("active");
			$(this).parents('.mm-collapse').addClass("mm-show");
			$(this).parents('.mm-collapse').parent("li").addClass("mm-active");
		}
	});
	//Initialize radio tab fn call
	initializeRadioTabs();
}(jQuery));

/**
  * Enter into full screen
  */
function fullScreen(isfullscreen) {
	var docBrowserElem = document.documentElement
	if (docBrowserElem.requestFullscreen) {
		docBrowserElem.requestFullscreen();
	} else if (docBrowserElem.mozRequestFullScreen) { /* Firefox */
		docBrowserElem.mozRequestFullScreen();
	} else if (docBrowserElem.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
		docBrowserElem.webkitRequestFullscreen();
	} else if (docBrowserElem.msRequestFullscreen) { /* IE/Edge */
		docBrowserElem.msRequestFullscreen();
	}
	isfullscreen = true;
	return isfullscreen
}

/**
* Exit from full screen
*/
function exitFullScreen(isfullscreen) {
	if (document.exitFullscreen) {
		document.exitFullscreen();
	} else if (document.mozCancelFullScreen) {
		document.mozCancelFullScreen();
	} else if (document.webkitExitFullscreen) {
		document.webkitExitFullscreen();
	} else if (document.msExitFullscreen) {
		document.msExitFullscreen();
	}
	isfullscreen = false;
	return isfullscreen
}

/**
* ----------------------------------------------
* Initialize radio tab fn
* ----------------------------------------------
*/
function initializeRadioTabs() {
	$(".radios-tabs .radio-input").on("change", function () {
		var id = $(this).attr("data-href");
		$(id).addClass("show active").siblings().removeClass("show active");
	})
}

/**
 * Mega DD slider
 */
function megaDDSlider() {
	return $(".mega-dd-slider .owl-carousel").owlCarousel({
		loop: true,
		margin: 0,
		nav: false,
		dots: false,
		autoplay: true,
		autoplayTimeout: 2000,
		responsive: {
			0: {
				items: 1
			},
			600: {
				items: 1
			},
			1000: {
				items: 1
			}
		}
	});
}