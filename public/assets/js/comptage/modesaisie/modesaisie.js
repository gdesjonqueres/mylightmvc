if (typeof Dtno.controllers == 'undefined') {
    Dtno.controllers = {};
}

(function($) {
    var controller = function(options) {
        var defaults = {
            critere: null,
            mode: null,
            validCallback: null
        };
        
        this.init = function() {
            this.options = $.extend({}, defaults, options);
        };

        this.validate = function() {
            var values = {};
            
            if (this.options.mode == 'intervalle') {
                values = this._intervalleValues();
            }
            else if (this.options.mode == 'autocomplete') {
                values = this._autocompleteValues();
            }
            else if (this.options.mode == 'checkbox') {
                values = this._checkboxValues();
            }
            else if (this.options.mode == 'radio') {
                values = this._radioValues();
            }
            else if (this.options.mode == 'tree') {
                values = this._treeValues();
            }
            
            Dtno.utils.post(
                "comptage:modesaisie:" + this.options.mode,
                {critere: this.options.critere, valeurs: values},
                this.options.validCallback
            );
        };
        
        this._intervalleValues = function() {
            var values = {},
                vals = $("#intervalle").intervalle("toArray");
            
            if (vals.lower != null) {
                var min = {};
                min[vals.lower.value] = vals.lower.label;
                values["min"] = min;
            }
            if (vals.upper != null) {
                var max = {};
                max[vals.upper.value] = vals.upper.label;
                values["max"] = max;
            }
            
            return values;
        };
        
        this._autocompleteValues = function() {
            return $("#autocom").data("dtnoAutoComplete").values();
        };
        
        this._checkboxValues = function() {
            var values = {};
            
            $("#formMode input[type=checkbox]:checked").each(function() {
                var key = $(this).attr("name");
                var val = $(this).val();
                values[key] = val;
            });
            
            return values;
        };
        
        this._radioValues = function() {
            var values = {},
                $radio = $("#formMode input[name='valeurs']:checked");
            if ($radio.val()) {
                values[$radio.val()] = $radio.data("label");
            }
            
            return values;
        };
        
        this._treeValues = function() {
            return $("#myTree").data("dtnoTree").values();
        };
        
        this.init();
    };
    Dtno.controllers.modesaisie = controller;
})(jQuery);