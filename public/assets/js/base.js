var phpjs = {
	number_format: function (number, decimals, dec_point, thousands_sep) {
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function (n, prec) {
			var k = Math.pow(10, prec);
			return '' + Math.round(n * k) / k;
		};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}
};

if (typeof Dtno === 'undefined') {
	window.Dtno = {};
}

(function($) {
	var config = {
		baseUrl: URL_BASE + 'index.php'
	};
	Dtno.config = config;

	var utils = {
		REGEX_MCA_URL: /^\w*\:\w*\:\w*$/,
		
		exceptions: {
			"sessionEnded": "Access_NoSessionException"
		},
		
		isset: function(v) {
			return typeof v !== 'undefined' && v !== null;
		},

		parseRoute: function(route) {
			var myRoute = route.split(':');
			return {
				"m": myRoute[0],
				"c": myRoute[1],
				"a": myRoute[2]
			};
		},
		
		handleError: function(xhr) {
			var text = 'statut : ' + xhr.status;
			if (xhr.status == 500) {
				text = xhr.responseText;
			}
			else if (xhr.status == 200) {
				// alert(text);
				// window.location.reload(true);
			}
			else {
				text += '\nréponse : ' + xhr.responseText;
			}
			//return text;
			//alert(text);
			
			var exception = JSON.parse(xhr.responseText),
				str = '';
			
			if (exception.type == utils.exceptions.sessionEnded) {
				if (parent) {
					parent.window.location = Dtno.config.baseUrl;
				}
				else {
					window.location = Dtno.config.baseUrl;
				}
			}
			
			if (window.console && window.console.log && window.console.error && window.console.info) {
				if (typeof exception.type !== 'undedefined' && exception.type != null) {
					str += exception.type + ': ';
				}
				str += exception.message;
				console.error(str);
				if (typeof exception.debug !== 'undefined' && exception.debug != null) {
					console.info(exception.debug);
				}
			}
		},
			
		post: function(route, data, success, complete, error) {
			var jqXhr,
				url;
			
			if (typeof data === 'undefined' || data == null) {
				data = {};
			}
			
			if (utils.REGEX_MCA_URL.test(route)) {
				url = Dtno.config.baseUrl;
				$.extend(data, utils.parseRoute(route));
			}
			else {
				url = route;
			}

			jqXhr = $.ajax({
				type: "POST",
				url: url,
				data: data,
				dataType: "json",
				/*contentType: "application/json; charset=utf-8",*/
				success: function(data) {
					if (typeof success !== 'undefined' && success != null) {
						//success.call(this, data == null ? null : data.d);
						success.call(this, data);
					}
				},
				error: function(xhr) {
					if (typeof error !== 'undefined' && error != null) {
						var exception = JSON.parse(xhr.responseText);
						if (exception.type == utils.exceptions.sessionEnded) {
							window.location = Dtno.config.baseUrl;
						}
						else {
							if (!error.call(this, exception)) {
								utils.handleError(xhr);
							}
						}
					}
					else {
						utils.handleError(xhr);
					}
				},
				complete: function() {
					if (typeof complete !== 'undefined' && complete != null) {
						complete.call(this);
					}
				}
			});
			
			return jqXhr;
		},
		
		formatNumber: function(number, decimals) {
			if (typeof decimals == 'undefined' || decimals == null) {
				decimals = 0;
			}
			return phpjs.number_format(number, decimals, ',', ' ');
		},
		
		formatTooltipValues: function(values, limitChar) {
			if (typeof limitChar === 'undefined' || limitChar == null) {
				limitChar = 30;
			}
			var str = '';
			$.each(values, function(k, v) {
				if (v.length > limitChar) {
					str += v.substr(0, limitChar) + ' (...)';
				}
				else {
					str += v;
				}
				str += ' <br />';
			});
			return str;
		},
		
		resizeColorBox: function(cb, $elt1, $elt2) {
			var myWidth = $elt1.width() > $elt2.width() ? $elt1.width() : $elt2.width();
			var myHeight = $elt1.height() > $elt2.height() ? $elt1.height() : $elt2.height();
			myWidth += 100;
			myHeight += 70;
			cb.resize({innerWidth: myWidth, innerHeight: myHeight});
		}
	};
	Dtno.utils = utils;
})(jQuery);