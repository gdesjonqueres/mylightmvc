if (typeof Dtno.helpers === 'undefined') {
    Dtno.helpers = {};
}

(function($) {

	var autoComplete = function(elId, options) {

		var defaults = {
			searchUri: null,
			searchData: null,
			delay: 300,
			minLength: 3
		};

		this.id = elId;
		if (typeof options !== 'object') {
			options = JSON.parse(options);
		}
		this.options = $.extend({}, defaults, options);

		this.init = function() {
			var that = this;

			this.$el = $("#" + elId);
			this.searchValue = null;
			this.successCount = 0;
			
			this.$el.on("keydown", function(event) {
				if (that.timeOutCllBck) {
					clearTimeout(that.timeOutCllBck);
				}
				that.timeOutCllBck = setTimeout(function() {
					if (that.searchValue !== that.$el.val()) {
						if (that.$el.val().length >= that.options.minLength) {
							that.search(that.$el.val());
						}
						else if (that.searchValue !== null) {
							that.$container.find(".results").hide();
							that.$container.find(".elements").empty();

						}
					}
				}, that.options.delay);
			});
			
			this.createElement();

			// Attache l'ojet à l'élément du DOM
			this.$el.data("dtnoAutoComplete", this);
		};
		
		this.createElement = function() {
			this.$el.wrap('<div class="autocomplete" />');
			this.$container = $("<div />").insertAfter(this.$el);

			$('<div class="noresults">Aucun résultat</div>' + "\n" +
				'<div class="results">' + "\n" +
				'<div class="instructions">Cochez les éléments à sélectionner</div>' + "\n" +
				'<div class="elements"></div>' + "\n" +
				'</div>').appendTo(this.$container);

			this.$el.addClass("field");
		};
		
		this.search = function(term) {
			var that = this,
				data = $.extend({}, this.options.searchData, {lookup: term});

			Dtno.utils.post(
				this.options.searchUri,
				data,
				function(data) {
					that.searchValue = term;
					that.updateResults(data);
				}
			);
		};
		
		this.updateResults = function(data) {
			var that = this,
				isSuccessful = data.length >= 1 ? true : false,
				$elts;

			this.$container.find(".noresults").hide();
			this.$container.find(".results").hide();
			this.$container.find(".elements").empty();

			$elts = that.$container.find(".elements");
			$.each(data, function(index, item) {
				$elts.append('<span><input type="checkbox" id="cb_' + item.id + '" ' + 'name="' + item.id + '" value="' + item.label + '" />' + 
					'<label for="cb_' + item.id + '">' + item.id + ' - ' + item.label + '</label></span><br />');
			});
			
			if (isSuccessful) {
				this.$container.find(".results").show();
			}
			else {
				this.$container.find(".noresults").show();
			}

			if (parent.$.colorbox && isSuccessful && this.successCount == 0) {
				Dtno.utils.resizeColorBox(parent.$.colorbox, $("form"), $(".content"));
			}

			if (isSuccessful) {
				this.successCount++;
			}
		};

		this.values = function() {
			var values = {};

			this.$container.find("input:checked").each(function() {
				values[$(this).attr("name")] = $(this).val();
			});

			return values;
		};

		this.init();
	};
	
	Dtno.helpers.autoComplete = autoComplete;
	
})(jQuery);
