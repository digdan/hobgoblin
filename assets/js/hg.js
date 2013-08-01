;(function( $ ) {
	$.fn.hgButler = function( options ) {
		var defaults = {
			reason : '#reason',
			onBefore : function () {
				$(this).children('div[data*="bound"]').hide(); //Hide field level reasons
			},
			onBeforeSubmit : function(arr,$form,options) {
				$(this).addClass('loading');
			},
			onAfter : function (json,target) {
				$(this).removeClass('loading');
			},
			onSuccess : function( json,target ) {

			},
			onProgress : function ( event, position, total, percentComplete ) {

			},
			onOk : function (json, target) {
				if (json.redirect) {
					window.location.href=json.redirect;
				}
				if (json.reason) {
					reasonContainer = $(options.reason);
					if (reasonContainer.length > 0) {
						options.onSuccess.call(json,target);
						reasonContainer.html('<div class="alert alert-success fade in">'+json.reason[0]+'<button type="button" class="close" data-dismiss="alert">&times;</a></div>');
						$(".alert").delay(3000).fadeOut("slow", function () { $(this).remove(); });

					} else {
						alert('Ok : '+json.reason[0]);
					}
				} else {
					alert('Ok : ' +json.reason[0]);
				}

				// $(':input',formId).not(':button, :submit, :reset, :hidden').val('').removeAttr('checked').removeAttr('selected');//
				target.find("input[type='text'], textarea").val(""); //Clear Form
			},
			onError : function(json, target) {
				if (json.redirect) {
					window.location.href=json.redirect;
				}
				if (json.reason) {
					reasonContainer = $(options.reason);
					if (reasonContainer.length > 0) {
						reasonContainer.html('<div class="alert alert-error fade in">'+json.reason[0]+'<button type="button" class="close" data-dismiss="alert">&times;</a></div>');
						$(".alert").delay(3000).fadeOut("slow", function () { $(this).remove(); });
					} else {
						alert('Error : '+json.reason);
					}
				} else {
					alert('Error');
				}
				//Field level reasons
/*
				json.fields.each( function (index, value) {
					if ($('div[data-bound='+index+']').length > 0) {
						$('div[data-bound='+index+']').html(value);
						$('div[data-bound='+index+']').show();
					}
				});
*/
			}
		};

		options = $.extend(true, defaults , options);

		return this.each( function () {
			obj = $(this);
			if ($.isFunction( options.onBefore ) ) {
				options.onBefore.call( this );
			}

			$(this).ajaxForm( {
				dataType : 'json',
				beforeSubmit: options.onBeforeSubmit,
				error : function (handler,txtError,errorThrown) { alert('Communication Error : ' + errorThrown + ' | ' + txtError ); },
			    uploadProgress: function(event, position, total, percentComplete) {
			    	options.onProgress.call( this, event, position, total, percentComplete);
    			},
				success : function ( json ) {
					if (json.ok == true) {
						if ($.isFunction( options.onOk ) ) {
							options.onOk.call(this,json,obj);
						}
					} else {
						options.onError.call(this,json,obj);
					}
				}
			});
			obj.removeClass('loading');
			return false;
		});
	}
})( jQuery );