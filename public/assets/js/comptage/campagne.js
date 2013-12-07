if (typeof Dtno.models === 'undefined') {
    Dtno.models = {};
}

(function($) {
	var messageDlg = '#messageZone';
	
	var campagne = {
		save: function() {
			Dtno.utils.post(
				"comptage:campagne:save",
				{},
				function(data) {
					//alert("Campagne sauvegardée");
					$(messageDlg).text("La campagne a été correctement sauvegardée");
					$(messageDlg).dialog("open");
					if (data.reload) {
						document.location.reload();
					}
				},
				null,
				function(error) {
					//alert("Erreur lors de l'enregistrement. Campagne non sauvegardée");
					$(messageDlg).html("Erreur lors de l'enregistrement.<br />Campagne non sauvegardée");
					$(messageDlg).dialog("open");
				}
			);
		},
		
		saveLibelle: function(libelle) {
			Dtno.utils.post(
				"comptage:campagne:saveLibelle",
				{"libelle": libelle},
				function(data) {
					//alert("Campagne sauvegardée");
					$(messageDlg).text("La campagne a été correctement sauvegardée");
					$(messageDlg).dialog("open");
					if (data.reload) {
						document.location.reload();
					}
				},
				null,
				function(error) {
					//alert("Erreur lors de l'enregistrement. Campagne non sauvegardée");
					$(messageDlg).html("Erreur lors de l'enregistrement.<br />Campagne non sauvegardée");
					$(messageDlg).dialog("open");
				}
			);
		}
	};
	
	var  critere = {
		remove: function(id) {
			Dtno.utils.post(
				"comptage:critere:remove",
				{"critere": id},
				function(data) {
					//document.location.reload();
				    //Dtno.controllers.cible.refreshCible();
				    Dtno.controllers.cible.isChanged = true;
				    Dtno.controllers.cible.onChange();
				}
			);
		},
		
		removeValeur: function(idCritere, idVal) {
			Dtno.utils.post(
				"comptage:critere:removevaleur",
				{"critere": idCritere, "valeur": idVal},
				function(data) {
				    parent.Dtno.controllers.cible.isChanged = true;
					document.location.reload();
				}
			);
		}
	};
	
	var operation = {
		comptage: function() {
			var jqXhr;

			jqXhr = Dtno.utils.post(
				"comptage:operation:comptage",
				{},
				function(data) {
					$.colorbox.close();
					//$("#resultat-comptage").html("<em>" + Dtno.utils.formatNumber(data.comptage) + "</em> adresses dans votre sélection");
					$("#ma-campagne-resultats-comptage").html('<span class="badge">' + Dtno.utils.formatNumber(data.comptage) + '</span><br />adresses sélectionnées');
					if (data.comptage > 0) {
						$("#btnSuivant").show();
					}
					//$("#resultat-comptage").fadeIn(1000);
					$("#ma-campagne-resultats-comptage").fadeIn(1000);
				},
				null,
				function(error) {
					$.colorbox.close();
					//alert("Une erreur s'est produite lors du comptage");
					$(messageDlg).html("Une erreur s'est produite lors du comptage");
					$(messageDlg).dialog("open");
					$("#btnComptage").show();
				}
			);
		}
	};
	
	Dtno.models.campagne = campagne;
	Dtno.models.critere = critere;
	Dtno.models.operation = operation;
})(jQuery);