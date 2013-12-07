if (typeof Dtno.helpers === 'undefined') {
    Dtno.helpers = {};
}

(function($) {

	var tree = function(elId, nodes, selected) {

		this.id = elId;
		this.$el = $("#" + this.id);

		if (typeof nodes !== 'object') {
			this.nodes = JSON.parse(nodes);
		}
		else {
			this.nodes = nodes;
		}
		if (typeof selected !== 'object') {
			this.selected = JSON.parse(selected);
		}
		else {
			this.selected = selected;
		}

		this.init = function() {
			var that = this,
				root;
			
			// patch...
			// "selected" doit contenir un tableau de string,
			// parfois les index numériques sont castés en int par php quand récupérés en POST
			// ce qui pose problème avec le $.inArray plus loin car les ids de la liste des nodes
			// sont uniquement des chaînes, eux
			$.each(that.selected, function(index, item) {
				if (typeof item == "number") {
					that.selected[index] = item.toString();
				}
			});

			this.recursive(this.nodes, this.selected);

			root = $.extend({}, this.treeDefaults, {
				"id" : "root",
				"text" : "Tout sélectionner",
				"isexpand" : true,
				"ChildNodes" : this.nodes
			});
			this.$el.treeview({
				showcheck : true,
				data : [root]
			});

			// Attache l'ojet à l'élément du DOM
			this.$el.data("dtnoTree", this);
		};

		this.init();
	};

	tree.prototype = {
		treeDefaults: {
			"showcheck" : true,
			complete : true,
			"isexpand" : false,
			"checkstate" : 0,
			"hasChildren" : true
		},

		recursive: function(nodes, selected, parent, prefix) {
			if (prefix == null) {
				prefix = '';
			}

			for (var i = 0; i < nodes.length; i++) {

				nodes[i] = $.extend({}, this.treeDefaults, nodes[i]);

				if (!nodes[i].id) {
					nodes[i].id = (prefix != '' ? prefix + '_n' + i : 'n' + i);
				} else {
					nodes[i].value = nodes[i].id;
				}
				if ($.inArray(nodes[i].id, selected) != -1) {
					nodes[i].checkstate = 1;
					if (parent) {
						parent.checkstate = 2;
						parent.isexpand = true;
					}
				}

				if (nodes[i].ChildNodes == null) {
					nodes[i].hasChildren = false;
				} else {
					this.recursive(nodes[i].ChildNodes, selected, nodes[i], prefix + 'n' + i);
					if (nodes[i].checkstate == 2 && parent) {
						parent.checkstate = 2;
						parent.isexpand = true;
					}
				}
			}
		},

		getCheckedNodes: function() {
			var checkedItems = this.$el.getTSNs();
			var values = [];
			$.each(checkedItems, function(index, item) {
				if (item.value) {
					values.push({id: item.value, label: item.text});
				}
			});
			return values;
		},

		values: function() {
			var checkedItems = this.$el.getTSNs();
			var values = {};
			$.each(checkedItems, function(index, item) {
				if (item.value) {
					values[item.value] = item.text;
				}
			});
			return values;
		}
	};
	tree.prototype.toArray = tree.prototype.getCheckedNodes;

	Dtno.helpers.tree = tree;

})(jQuery);
