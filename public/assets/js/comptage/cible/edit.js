if (typeof Dtno.controllers === 'undefined') {
	Dtno.controllers = {};
}

(function($) {
    $(document).ready(function(){
        Dtno.controllers.cible.init();
    });
    
    var controller = {
         messageDlg: '#messageZone',
         mode: null,
         isChanged: false,

         init: function() {
             this.initLightBoxes();
             this.initLibelleEdit();
             this.initSlideMenu();
             this.initDeleteBtn();
             this.initTooltip();
             this.initComptageBtn();
             this.initDialog();
         },
         
         initLightBoxes: function() {
             $(".lightbox").colorbox({
                 onComplete: function() {
                     controller.mode = new Dtno.controllers.modesaisie({
                         mode: $(this).data('mode'),
                         critere: $(this).data('critere'),
                         validCallback: function() {
                             controller.isChanged = true;
                             controller.onChange();
                         }
                     });
                     
                     $("#formMode").on("submit", function(event) {
                         event.preventDefault();
                         controller.mode.validate();
                         $.colorbox.close();
                     });
                 }
             });
             
             $(".iframe").colorbox({
                 iframe: true,
                 speed: "200",
                 width: "400px",
                 height: "200px",
                 fastIframe: false,
                 onComplete: controller.resizeColorbox,
                 onClosed: controller.onChange
             });
         },
         
         onChange: function() {
             if (controller.isChanged) {
                 controller.refreshCible();
                 controller.showComptage();
                 controller.isChanged = false;
             }
         },
         
         initLibelleEdit: function() {
             $("#ma-campagne-libelle").editable(function(value, settings) {
                 Dtno.models.campagne.saveLibelle(value);
                 return value;
             }, {
                 type : "text",
                 indicator : "Enregistrement en cours...",
                 tooltip : "Cliquez ici pour changer le nom de la campagne...",
                 cssclass: "edit",
                 submit: "OK",
                 select: true,
                 width: "430px"/*,
                 height: "20px"*/
             });
         },
         
         initSlideMenu: function() {
             $("#menu-site ul").hide();
             $(".menu h1").each(function() {
                 $(this).css("cursor", "pointer");
                 $(this).on("click", function(event) {
                     var myJqObj = $(this).parent();
                     myJqObj.children("ul").slideToggle();
                     $(".menu").not(myJqObj).children("ul").slideUp();
                 });
             });
         },
         
         initDeleteBtn: function() {
             $(".delete").on("mouseover", function(event) {
                 $(this).siblings("h1, h2, .title").addClass("todelete");
             });
             $(".delete").on("mouseout", function(event) {
                 $(this).siblings("h1, h2, .title").removeClass("todelete");
             });
         },
         
         initTooltip: function() {
             $(".tooltip").tooltip({
                 items: "[data-values]",
                 content: function() {
                     return Dtno.utils.formatTooltipValues($(this).data("values"));
                 }
             });
         },
         
         initComptageBtn: function() {
             $("#btnComptage").click(function(event) {
                 
                 if ($(this).data("iscontactset")) {
                     $("#btnComptage").hide();
                     
                     Dtno.models.operation.comptage();
                     
                     $.colorbox({
                         html:'<div id="comptage-en-cours">Comptage en cours,<br />Merci de bien vouloir patienter<div class="progressbar"></div></div>',
                         width: "300px",
                         height: "200px",
                         overlayClose: false,
                         escKey: false,
                         closeButton: false,
                         /*onCleanup: function() {
                             jqXhr.abort();
                         },*/
                         onComplete: function() {
                             $("#cboxClose").hide();
                             $("#comptage-en-cours .progressbar").progressbar({value: false});
                         }
                     });
                 }
                 else {
                     alert("Merci d'associer un compte avant d'effectuer le comptage");
                 }
             });
         },
         
         showComptage: function() {
             $("#btnComptage").show();
             $("#btnSuivant").hide();
             //$("#resultat-comptage").hide();
             $("#ma-campagne-resultats-comptage").hide();
         },

         initDialog: function() {
             $(this.messageDlg).dialog({
                 draggable: false,
                 resizable: false,
                 autoOpen: false,
                 modal: true,
                 title: 'Campagne en cours',
                 height: 100,
                 width: 250
             });
         },
         
         refreshCible: function() {
             $("#cible-en-cours").load(
                 Dtno.config.baseUrl,
                 Dtno.utils.parseRoute('comptage:cible:refreshcurrentcible'),
                 function() {
                     controller.initLightBoxes();
                     controller.initTooltip();
                     controller.initDeleteBtn();
                 }
             );
         },
         
         resizeColorbox: function() {
             var myForm = $(".cboxIframe").contents().find("form");
             var myContent = myForm.find(".content");
             Dtno.utils.resizeColorBox($.colorbox, myForm, myContent);
         }
    };
    Dtno.controllers.cible = controller;
})(jQuery);