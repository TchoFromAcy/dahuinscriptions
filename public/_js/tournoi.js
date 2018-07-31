$('#accompagne').bind('change',function(){

	if($(this).is(':checked')) $('#companionDiv').show();
	else $('#companionDiv').hide();
	$('#companionDiv input').valid();

	});


$('#type').bind('change',function(){

	if($(this).val()=='1'){
		 $('#compFs').show();
		 $('#club').parent('div').next('.help-block').html('&nbsp');

	}
	else $('#compFs').hide();



	});


$('#companionDiv input').bind('change keyup',function(){
	$('#companionDiv input').valid();

	
});
$('#noclub').bind('change',function(){

	
	
	if($(this).is(':checked')){
		 $('#club').val('').attr('disabled','disabled');
		 $('#club').parent('div').next('.help-block').html('&nbsp');

	}
	else $('#club').removeAttr('disabled');

	$('#club').valid();
	
	});


if($('#inscription').length>0)$('form').validate({

	onkeyup:function(elt){
			$(elt).valid();
		},

errorPlacement:function(error, element){


	var type=$(element).prop('type');

	if(type!=="checkbox")return error.insertAfter(element);
	$(error).addClass('checkboxError').removeClass('input-group-addon').appendTo($(element).parents('.form-group').find('label:first-child'));
	
	
},
		
		debug:true,

		success: function(label, input) {

		forName=$(input).attr('name').replace(/[\[\]']+/g,'');

			 label.html('<em class="fa fa-check"></em>');
		 $('.help-block[data-for='+forName+']').html('&nbsp;');

		 },
	rules:{
		nom:{required:true},
		prenom:{required:true},
		'poste[]':{
	required:true

			},
		email:{
			required:true,
			email:true
			},
			dob:{
			required:true,
			frenchDate:true,
			check_date_of_birth:16
				},
				 "captcha[input]":{

		                required:true,
		                minlength:3},
			adulte:{
				
				companion:true
				},
				enfant:{
				
					companion:true
					//depends: checkCompanion
					},
					club:{required:{
						depends:function(){
							return $('#type').val()=='1'&&!$('#noclub').is(':checked');
							}

						}}
		
		},
		messages:{
		'poste[]':function(){return 'Tu dois choisir au moins un poste';}
			},
			
		submitHandler:function(){

			var options = { 
			        target:        '#output2',   // target element(s) to be updated with server response 
			        beforeSubmit:  beforeSubmit,  // pre-submit callback 
			        success:       showResponse,
			        url:'/index/ajax?format=json',
			        type:'post',
			        dataType:'json',
			      //  resetForm:true ,
			        data:{apiCall:'subscribeTournament', 'ln':currLang}
			 
			
			    }; 
			 

			        // inside event callbacks 'this' is the DOM element so we first 
			        // wrap it in a jQuery object and then invoke ajaxSubmit 
			        $('form').ajaxSubmit(options); 


			}

	
});

//$('<p class="help-block">&nbsp;</p>').insertAfter($('.form-control').parent('div'));

$.each($('.form-control').not('.slider'), function(index,input){

if($(input).prop('type')=='checkbox') return;

forName=$(input).attr('name').replace(/[\[\]']+/g,'');

$('<p class="help-block" data-for="'+forName+'">&nbsp;</p>').insertAfter($(input).parent('div'));

		
});

$.validator.addMethod("companion", function(value, element) {

	if(!$('#accompagne').is(':checked')) return true;
	   return $('#accompagne').is(':checked')&&(parseInt($('#enfant').val())>0||parseInt($('#adulte').val())>0);
	}, "Tu dois &ecirc;tre accompagn&eacute; d'un adulte ou d'un p'tio");

function beforeSubmit(data, $form, options){
var dob = data[3];
dob.value=moment($('#dob').val(),$('body').hasClass('mobile')?'YYYY-MM-DD':'DD-MM-YYYY').format('DD/MM/YYYY');
	$('body').addClass('wait');
	$('.alert').alert('close');
submited=false;
$('#wait').show();
$('form').css("min-height",$('form').height()+"px");
$('form fieldset, form button, form .form-group').hide();

if(!$('body').hasClass('mobile'))tweenCircle();


return true;
	
}

$('#captcha-container').bind('click', '#refreshCaptcha', refreshCaptcha);



function refreshCaptcha(e) {
	
	$.get('/updater', {
		tpl: 'captcha',
	}).done(function(data) {
		$('#captcha-container').html(data);
		$('#captchaInput').val('').valid();

	});

	return false;
}



$('#closeWait').bind('click', function(e){
	e.preventDefault();

	resetForm();
	
	});

var currLang="sav";

$('#langTab li').bind('click',function(e){
	if($(this).hasClass('selected')) return;
	$('#langTab li.selected').removeClass('selected');
	$(this).addClass('selected');

$('.'+currLang).hide();
	
	currLang=$(this).find('a').data('lang');

	$('.'+currLang).show();
	
	tradForm();
	
});


$.validator.addMethod("check_date_of_birth", function(value,element,argument){
	
	  var age = moment(value,$('body').hasClass('mobile')?"YYYY-MM-DD":"DD-MM-YYYY");
	  var now = moment('2018-08-18');
	  
	  return this.optional(element) || now.diff(age,'years') >= argument;
	}, $.validator.format("Tu dois avoir plus de {0} ans le jour du tournoi.")); 


$.validator.addMethod("frenchDate", function(value,element){
        
   


var reg=$('body').hasClass('mobile')?/([0-9]{4})(.)([0-9]{2})(.)([0-9]{2})$/:/([0-9]{2})(.)([0-9]{2})(.)([0-9]{4})$/, test=reg.test(value);


if(test){	
	var date = moment(value,"DD-MM-YYYY");
	test=date.isValid();
};
		
	  return this.optional(element) || test;
	}, "La date est incorrecte"); 


var objTrad={"sav":
	{
	"accompagne":"Je serai accompagn&eacute;",
	"nom":"Ton nom",
	"noclub":"Je ne joue pas en club",
	"prenom":"Ton petit nom",
	"email":"Ton email",
	"dob":"Date de naissance",
	"sexe":"Sexe",
	"type":"Je souhaite participer :",
	"adulte":"Nb d'adulte(s) :",
	"enfant":"Nb de croet(s) :",
	"club":"Ton club",
	"poste1":"O&ucirc; que tu joues ?",
	"captchaInput":"Copie z'y le code",
	"technique":"Ton niveau technique <span class=\"help-text\">(technique pure et tactique)</span>","physique":"Ton niveau physique",
	"select":{
		sexe:["Je suis une femme","Je suis un homme"],
		type:["n'en tant que joueur","n'en tant qu'invit&eacute;"],
		confirm:["Vi","Nan"],
		party:["Vi","Nan","Sais po"]
		},
		sliders:{
			technique:["Chuis une vraie gniauque","&Ccedil;a qu'est des dieux du frisbee !"],
			physique:["chuis un baban","Je peux pataler toute la journ&eacute;e sans &ecirc;tre vann&eacute;"],

			},

			other:{
				waitText:'<span class="initial">Merci</span> <span class="initial">de</span> <span class="initial">patienter</span>',
				

				
				}
			
	},
	fr:{
		"accompagne":"Je serai accompagn&eacute;",	
		"nom":"Ton nom",
		"prenom":"Ton pr&eacute;nom",
		"email":"Ton email",
		"dob":"Date de naissance",
		"sexe":"Sexe",
		"type":"Je souhaite participer :",
		"adulte":"Nb d'adulte(s) :",
		"enfant":"Nb d'enfant(s) :",
		"club":"Ton club",
		"noclub":"Je ne joue pas en club",
		"poste1":"Ton poste",
		"captchaInput":"Copie le code",
		"technique":"Ton niveau technique <span class=\"help-text\">(technique pure et tactique)</span>","physique":"Ton niveau physique",
		"select":{
			sexe:["Je suis une femme","Je suis un homme"],
			type:["en tant que joueur","en tant qu'invit&eacute;"],
			confirm:["Oui","Non"],
			party:["Oui","Non","Je ne sais pas"]
			},
			sliders:{
				technique:["Je suis d&eacute;butant","Brodie est mon disciple"],
				physique:["Je suis une catastrophe","Je suis le meilleur"],

				},
				other:{
					waitText:'<span class="initial">Merci</span> <span class="initial">de</span> <span class="initial">patienter</span>',
					

					
					}
					
	
		},
		uk:{
			"accompagne":"I will be accompanied",
			"nom":"Lastname",
			"prenom":"Firstname",
			"email":"Email address",
			"dob":"Birth date",
			"sexe":"Gender",
			"type":"I am a ",
			"adulte":"Nb of adults :",
			"enfant":"Nb of children",
			"club":"Your club",
			"poste1":"Your position",
			"captchaInput":"Copy the code",
			"technique":"Ultimate skill<span class=\"help-text\">(technical and tactical)</span>",
			"physique":"Physical skill",
			"noclub":"I don't play in any club",
				"select":{
					sexe:["I am a woman","I am a man"],
					type:["a player","a guest"],
					confirm:["Yes","No"],
					party:["Yes","No","I don't know"]
					},
					sliders:{
						technique:["I don't know how to play","Brodie owes me everything "],
						physique:["I am a crap","I am the best"],

						},

			other:{
				waitText:'<span class="initial">Please</span> <span class="initial">wait</span>',
				

				
				}
						
		}
};

function tradForm(){
$.validator.messages=validatorMessages[currLang];

try{
	$('form input.error, form checkbox.error').valid();
}catch(e){};
	var trad = objTrad[currLang];


	$.each(trad, function(key, val){
if(typeof val!=="string") return;
		
	var input = $('#'+key), label = $('[for='+key+']').not('.error');
	if(label.length>0&&input.length>0) label.html(val);
	

		});
$.each(trad.select, function(key, val){

	$select = $('#'+key);
	$.each($select.children(), function(index){

		$(this).text(val[index]);
		});
	

	
});


$.each(trad.sliders, function(key, val){

	$select = $('#'+key);
	
	$.each($select.parents('.input-group').children('span'), function(index){

		$(this).html(val[index]);
		});
	

	
});

	
}


validatorMessages={
uk:{
	required: "This field is required.",
	remote: "Please fix this field.",
	email: "Please enter a valid email address.",
	url: "Please enter a valid URL.",
	date: "Please enter a valid date.",
	dateISO: "Please enter a valid date ( ISO ).",
	number: "Please enter a valid number.",
	digits: "Please enter only digits.",
	creditcard: "Please enter a valid credit card number.",
	equalTo: "Please enter the same value again.",
	maxlength: $.validator.format( "Please enter no more than {0} characters." ),
	minlength: $.validator.format( "Please enter at least {0} characters." ),
	rangelength: $.validator.format( "Please enter a value between {0} and {1} characters long." ),
	range: $.validator.format( "Please enter a value between {0} and {1}." ),
	max: $.validator.format( "Please enter a value less than or equal to {0}." ),
	min: $.validator.format( "Please enter a value greater than or equal to {0}." ),
	companion:"You must bring at least one adult or one child !",
	check_date_of_birth:$.validator.format("You must be over {0} when the tournament begins"),
	frenchDate:"The date is invalid (french format dd/mm/yyy)"
},
fr: {
	required: "Ce champ est obligatoire.",
	remote: "Veuillez corriger ce champ.",
	email: "Veuillez fournir une adresse électronique valide.",
	url: "Veuillez fournir une adresse URL valide.",
	date: "Veuillez fournir une date valide.",
	dateISO: "Veuillez fournir une date valide (ISO).",
	number: "Veuillez fournir un numéro valide.",
	digits: "Veuillez fournir seulement des chiffres.",
	creditcard: "Veuillez fournir un numéro de carte de crédit valide.",
	equalTo: "Veuillez fournir encore la même valeur.",
	extension: "Veuillez fournir une valeur avec une extension valide.",
	maxlength: $.validator.format("Veuillez fournir au plus {0} caractères."),
	minlength: $.validator.format("Veuillez fournir au moins {0} caractères."),
	rangelength: $.validator.format("Veuillez fournir une valeur qui contient entre {0} et {1} caractères."),
	range: $.validator.format("Veuillez fournir une valeur entre {0} et {1}."),
	max: $.validator.format("Veuillez fournir une valeur inférieure ou égale à {0}."),
	min: $.validator.format("Veuillez fournir une valeur supérieure ou égale à {0}."),
	maxWords: $.validator.format("Veuillez fournir au plus {0} mots."),
	minWords: $.validator.format("Veuillez fournir au moins {0} mots."),
	rangeWords: $.validator.format("Veuillez fournir entre {0} et {1} mots."),
	letterswithbasicpunc: "Veuillez fournir seulement des lettres et des signes de ponctuation.",
	alphanumeric: "Veuillez fournir seulement des lettres, nombres, espaces et soulignages.",
	lettersonly: "Veuillez fournir seulement des lettres.",
	nowhitespace: "Veuillez ne pas inscrire d'espaces blancs.",
	ziprange: "Veuillez fournir un code postal entre 902xx-xxxx et 905-xx-xxxx.",
	integer: "Veuillez fournir un nombre non décimal qui est positif ou négatif.",
	vinUS: "Veuillez fournir un numéro d'identification du véhicule (VIN).",
	dateITA: "Veuillez fournir une date valide.",
	time: "Veuillez fournir une heure valide entre 00:00 et 23:59.",
	phoneUS: "Veuillez fournir un numéro de téléphone valide.",
	phoneUK: "Veuillez fournir un numéro de téléphone valide.",
	mobileUK: "Veuillez fournir un numéro de téléphone mobile valide.",
	strippedminlength: $.validator.format("Veuillez fournir au moins {0} caractères."),
	email2: "Veuillez fournir une adresse électronique valide.",
	url2: "Veuillez fournir une adresse URL valide.",
	creditcardtypes: "Veuillez fournir un numéro de carte de crédit valide.",
	ipv4: "Veuillez fournir une adresse IP v4 valide.",
	ipv6: "Veuillez fournir une adresse IP v6 valide.",
	require_from_group: "Veuillez fournir au moins {0} de ces champs.",
	nifES: "Veuillez fournir un numéro NIF valide.",
	nieES: "Veuillez fournir un numéro NIE valide.",
	cifES: "Veuillez fournir un numéro CIF valide.",
	postalCodeCA: "Veuillez fournir un code postal valide.",
	companion:"Tu dois &ecirc;tre accompagn&eacute; d'un adulte ou d'un enfant",
	check_date_of_birth:$.validator.format("Tu dois avoir plus de {0} ans le jour du tournoi!"),
	frenchDate:"La date est incorrecte"
},
sav: {
	required: "Ce champ est obligatoire.",
	remote: "Veuillez corriger ce champ.",
	email: "Veuillez fournir une adresse électronique valide.",
	url: "Veuillez fournir une adresse URL valide.",
	date: "Veuillez fournir une date valide.",
	dateISO: "Veuillez fournir une date valide (ISO).",
	number: "Veuillez fournir un numéro valide.",
	digits: "Veuillez fournir seulement des chiffres.",
	creditcard: "Veuillez fournir un numéro de carte de crédit valide.",
	equalTo: "Veuillez fournir encore la même valeur.",
	extension: "Veuillez fournir une valeur avec une extension valide.",
	maxlength: $.validator.format("Veuillez fournir au plus {0} caractères."),
	minlength: $.validator.format("Veuillez fournir au moins {0} caractères."),
	rangelength: $.validator.format("Veuillez fournir une valeur qui contient entre {0} et {1} caractères."),
	range: $.validator.format("Veuillez fournir une valeur entre {0} et {1}."),
	max: $.validator.format("Veuillez fournir une valeur inférieure ou égale à {0}."),
	min: $.validator.format("Veuillez fournir une valeur supérieure ou égale à {0}."),
	maxWords: $.validator.format("Veuillez fournir au plus {0} mots."),
	minWords: $.validator.format("Veuillez fournir au moins {0} mots."),
	rangeWords: $.validator.format("Veuillez fournir entre {0} et {1} mots."),
	letterswithbasicpunc: "Veuillez fournir seulement des lettres et des signes de ponctuation.",
	alphanumeric: "Veuillez fournir seulement des lettres, nombres, espaces et soulignages.",
	lettersonly: "Veuillez fournir seulement des lettres.",
	nowhitespace: "Veuillez ne pas inscrire d'espaces blancs.",
	ziprange: "Veuillez fournir un code postal entre 902xx-xxxx et 905-xx-xxxx.",
	integer: "Veuillez fournir un nombre non décimal qui est positif ou négatif.",
	vinUS: "Veuillez fournir un numéro d'identification du véhicule (VIN).",
	dateITA: "Veuillez fournir une date valide.",
	time: "Veuillez fournir une heure valide entre 00:00 et 23:59.",
	phoneUS: "Veuillez fournir un numéro de téléphone valide.",
	phoneUK: "Veuillez fournir un numéro de téléphone valide.",
	mobileUK: "Veuillez fournir un numéro de téléphone mobile valide.",
	strippedminlength: $.validator.format("Veuillez fournir au moins {0} caractères."),
	email2: "Veuillez fournir une adresse électronique valide.",
	url2: "Veuillez fournir une adresse URL valide.",
	creditcardtypes: "Veuillez fournir un numéro de carte de crédit valide.",
	ipv4: "Veuillez fournir une adresse IP v4 valide.",
	ipv6: "Veuillez fournir une adresse IP v6 valide.",
	require_from_group: "Veuillez fournir au moins {0} de ces champs.",
	nifES: "Veuillez fournir un numéro NIF valide.",
	nieES: "Veuillez fournir un numéro NIE valide.",
	cifES: "Veuillez fournir un numéro CIF valide.",
	postalCodeCA: "Veuillez fournir un code postal valide.",
	companion:"Tu dois &ecirc;tre accompagn&eacute; d'un adulte ou d'un p'tio",
	check_date_of_birth:$.validator.format("Tu dois avoir plus de {0} ans le jour du tournoi"),
	frenchDate:"La date est incorrecte"
}

		
}