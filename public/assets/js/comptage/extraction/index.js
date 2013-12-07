/**
 * 
 */
(function($) {
	
	var messageDlg = '#messageZone';
	
	var EXCEL_LINES_LIMIT  = 1048576;
	var EXCEL5_LINES_LIMIT = 65535;

	$(document).ready(function(){
		// Méthodes exécutées au chargement du DOM
		initLightBoxes();
		initSortable();
		initQuantite();
		initFichierType();
		initForm();
		initDialog();
	});
	
	function initLightBoxes() {
		$(".iframe").colorbox({
			iframe: true,
			speed: "200",
			width: "400px",
			height: "200px",
			fastIframe: false,
			onComplete: resizeColorbox
		});
	}
	
	function initQuantite() {
		$("#quantite").number(true, 0, ",", " ")
			.blur(function() {
				if ($(this).val() > EXCEL_LINES_LIMIT) {
					$("#fichiertype option[value='excel']").prop("disabled", true);
					$("#fichiertype").val("txt").prop("selected", true);
					$("#fichiertype").trigger("change");
				}
				else {
					$("#fichiertype option[value='excel']").prop("disabled", false);
					
					if ($(this).val() > EXCEL5_LINES_LIMIT) {
						$("#opt-excel [name='options[format]'] option[value='excel5']").prop("disabled", true);
						$("#opt-excel [name='options[format]']").val("excel2007").prop("selected", true);
					}
					else {
						$("#opt-excel [name='options[format]'] option[value='excel5']").prop("disabled", false);
					}
				}
			});
	}
	
	function initSortable() {
		$(".mySortable").sortable({
			connectWith: ".connectedSortable"
		}).disableSelection();
		
		$("#champs-tous").click(function(event) {
			event.preventDefault();
			$("#champs-dispo li").appendTo("#champs-ajoutes");
		});
		
		$("#formExtraction").submit(function(event) {
			//event.preventDefault();
			var tabIds = $("#champs-ajoutes").sortable("toArray");
			$.each(tabIds, function(key, val) {
				$("#champs-ajoutes-ids").append('<input type="text" name="champs[' + key + ']" value="' + val + '" />');
			});
			return true;
		});
	}
	
	function initFichierType() {
		$(".fichiertype-options").hide();
		$(".fichiertype-options input, .fichiertype-options select").prop("disabled", true);
		if ($("#fichiertype").val()) {
			var optname = "#opt-" + $("#fichiertype").val();
			$(optname + " input, " + optname + " select").prop("disabled", false);
			$(optname).show();
		}
		
		$("#fichiertype").change(function(event) {
			$(".fichiertype-options").hide();
			$(".fichiertype-options input, .fichiertype-options select").prop("disabled", true);
			var optname = "#opt-" + $(this).val();
			$(optname + " input, " + optname + " select").prop("disabled", false);
			$(optname).show();
;		});
		
		$("#quantite").trigger("blur");
	}
	
	function resizeColorbox() {
		var myForm = $(".cboxIframe").contents().find("form");
		var myContent = myForm.find(".content");
		Dtno.utils.resizeColorBox($.colorbox, myForm, myContent);
	}
	
	function initForm() {
		$("form").submit(function(event) {
			//event.preventDefault();
			
			var errors = 0,
				$elt,
				$label;
			
			$elt = $("#quantite");
			$label = getLabelFor($elt);
			if ($elt.val() == 0 || $.trim($elt.val()) == '') {
				errors++;
				$label.addClass("error");
			}
			else {
				$label.removeClass("error");
			}
			
			$elt = $("#contact");
			$label = getLabelFor($elt);
			if ($elt.val() == 0 || $.trim($elt.val()) == '') {
				errors++;
				$label.addClass("error");
			}
			else {
				$label.removeClass("error");
			}
			
			$elt = $("input[name='envoi']");
			$label = getLabelFor($elt);
			if ($elt.filter(":checked").length <= 0) {
				errors++;
				$label.addClass("error");
			}
			else {
				$label.removeClass("error");
			}
			
			if (errors > 0) {
				$(messageDlg).text("Merci de renseigner tous les champs obligatoires");
				$(messageDlg).dialog("open");
				return false;
			}

			var tabIds = $("#champs-ajoutes").sortable("toArray");
			if (tabIds.length <= 0) {
				$(messageDlg).text("Merci de définir un dessin d'enregistrement");
				$(messageDlg).dialog("open");
				return false;
			}
			$.each(tabIds, function(key, val) {
				$("#champs-ajoutes-ids").append('<input type="text" name="champs[' + key + ']" value="' + val + '" />');
			});
			
			document.getElementById("quantite").value = $("#quantite").val();
			
			return true;
		});
	}
	
	function getLabelFor($elt) {
		return $elt.parent().prev(".label");
	}
	
	function initDialog() {
		$(messageDlg).dialog({
			draggable: false,
			resizable: false,
			autoOpen: false,
			modal: true,
			title: 'Extraction',
			height: 100,
			width: 250
		});
	}

})(jQuery);