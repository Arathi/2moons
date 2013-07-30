$(function() {
	$('.flags').on('click', function(e) {
		e.preventDefault();
		var langKey = $(this).attr('class').replace(/flags(.*)/, "$1").trim();
		Login.setLanguage(langKey);
		return false;
	});
	
	$('.fancybox').fancybox({
		'type' : 'iframe',
		'padding' : 1,
	});
	
	if(LoginConfig.isMultiUniverse)
	{
		$('.changeAction').each(function() {
			var $this	= $(this);
			$this.parents('form').attr('action', function(i, old) {
				return LoginConfig.basePath+'uni'+$this.val()+'/'+$(this).data('action');
			});
			$('.fb_login').attr('href', function(i, old) {
				return LoginConfig.basePath+'uni'+$this.val()+'/'+$(this).data('href');
			});
		}).on('change', function() {
			var $this	= $(this);
			$this.parents('form').attr('action', function(i, old) {
				return LoginConfig.basePath+'uni'+$this.val()+'/'+$(this).data('action');
			});
			$('.fb_login').attr('href', function(i, old) {
				return LoginConfig.basePath+'uni'+$this.val()+'/'+$(this).data('href');
			});
		});
		
		$('.changeUni').on('change', function() {
			document.location.href = LoginConfig.basePath+'uni'+$(this).val()+'/index.php'+document.location.search;
		});
	}
	else
	{
		$('.fb_login').attr('href', function(i, old) {
			return LoginConfig.basePath+$(this).data('href');
		});
	}
});

var Login = {
	setLanguage : function (LNG, Query) {
		$.cookie('lang', LNG);
		if(typeof Query === "undefined")
			document.location.href = document.location.href
		else
			document.location.href = document.location.href+Query;
	}
};