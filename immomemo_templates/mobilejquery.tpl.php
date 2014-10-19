<?php
global $base_url;

?>


<!DOCTYPE html>

<html >

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, user-scalable=no">
    <link rel="shortcut icon" href="<?= $base_url ?>/sites/default/files/1398135398_office-building.png" type="image/png" />
    <link rel="stylesheet" type="text/css" href="<?= $base_url ?>/sites/all/modules/immomemo/CSS/reset.css">

    <!-- Apple app full screen -->    
    <meta name="apple-mobile-web-app-capable" content="yes">
    <!-- Page Title -->
    <title>Immomemo</title>
    <!-- Apple Icon -->
    <link rel="apple-touch-icon-precomposed" href="<?= $base_url ?>/sites/default/files/1398135398_office-building.png"/>

    <!-- Status Bar -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />

    <!-- iPhone (Retina) -->
    <link href="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/splash_640x1136.jpg" rel="apple-touch-startup-image">

	<script type="text/javascript">

		// User choice
		var CP = "";
		var street = "";
		var street_nbr = "";


		// data received
		<?php // print available CP for this user
			echo 'var data_CP = [';
			foreach ($CP as $key => $value) {
				echo '"'.$value.'",';
			}
			echo "];\n";
		?>
		var data_streets = "";
		var data_street_nbrs = "";
        var data_habitants = "";

        // controle data
        var loading_display = '<div id="loading"><div id="loading_bg"></div><ul class="bokeh"><li></li><li></li><li></li><li></li></ul></div>';
        var data_building_change = 0 ;
        var data_habitants_change = 0 ;
		var ajax_result="";
        var nav_pos = 0 ;
        var nav_width = 0 ;
        var nav_1 = 0 ;
        var nav_2 = 0 ;
        var speed = 1500 ;
        var easing = 'easeInOutExpo';
        var type_voie = ['','rue','boulevard','place','impasse','quai'];
        var timer;
        var timer2;
        var xmlhttp ;

		//
		// Functions
		//


		function get_screen_size(){
			if (document.body){
				var larg = (document.getElementById('contents').clientWidth);
				var haut = (document.getElementById('contents').clientHeight);
			} 
			else{
				var larg = (window.innerWidth);
				var haut = (window.innerHeight);
			}
            
            // Si on veut la taille de l'écran :
            //var haut = screen.height;
			
            screen['larg'] = larg;
			screen['haut'] = haut;
			return screen
		}

        function reset_nav(){
            nav_pos = 0 ;
            nav_width = 0 ;
            nav_1 = 0 ;
            nav_2 = 0 ;
            $('#buildings_nav').remove();
        }

		function loading(state){
			if (state=='on'){
                if ($('#loading').length == 0){
                    $('#contents').prepend(loading_display);
                }
            }
			else $('#loading').remove();
		}

		function ajax(type, data){
            
            if (!navigator.onLine) { // Ne marche pas en mode application
                alert("Vous n'êtes plus connecté !\rL'application ne peut fonctionner.");
                return;
            };

            timeout_delay = 15000 ;
            timeout_message = "Votre connexion est trop faible !\rL'application ne peut fonctionner.";

            //var xmlhttp;   

            if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
              	xmlhttp=new XMLHttpRequest();
            } 
            else{// code for IE6, IE5
              	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange=function(){
            	if (xmlhttp.readyState==1) {
              		loading('on');
              	}
              	else if (xmlhttp.readyState==4 && xmlhttp.status==200){
                    clearTimeout(timer);
                    clearTimeout(timer2);
                    ajax_result = xmlhttp.responseText;
                    if (type=="get_street"){
                		data_streets = JSON.parse(ajax_result);
                		loading('off');
                		display_choose_street(data_streets);
                	} 
                    else if (type=="get_buildings") {
                        data_street_nbrs = JSON.parse(ajax_result);
                        loading('off');
                        display_choose_buildings(data_street_nbrs);
                    } 
                    else if (type=='save_building'){
                        if (ajax_result !== 'ok') {
                            alert(ajax_result);
                            loading('off');
                        }
                        else { // On enregistre les données en locales pour l'affichage
                            save_data('building_store_local', data);
                            save_data('success');
                            loading('off');
                            data_building_change = 0 ;
                        };
                    } 
                    else if (type=='get_habitants') {
                        data_habitants = JSON.parse(ajax_result);
                        loading('off');
                        display_habitants_data('display_habitants', data_habitants);
                    }
                    else if (type=='save_habitants_data'){
                        loading('off');
                        data_habitants = JSON.parse(ajax_result);
                        save_data('success');
                    }
                    else if(type=='csv_print'){
                        loading('off');
                        alert('L\'email a bien été envoyé.');
                    };
              	}
            }
			if (type=='get_street'){
            	xmlhttp.open("POST","<?= $base_url ?>/ajax/get_street",true);
				xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
				xmlhttp.send("CP="+CP);
                timer = setTimeout(function(){xmlhttp.abort();alert(timeout_message);loading('off');}, timeout_delay);
            } else if (type=='get_buildings') {
                xmlhttp.open("POST","<?= $base_url ?>/ajax/get_buildings",true);
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send("street="+street);
                timer = setTimeout(function(){xmlhttp.abort();alert(timeout_message);loading('off');}, timeout_delay);
            } else if (type=='save_building') {
                xmlhttp.open("POST","<?= $base_url ?>/ajax/save_building",true);
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send("im_id="+data.im_id+"&gardiens="+data.gardiens+"&bal="+data.bal+"&code="+data.code+"&infos="+data.infos);
                timer = setTimeout(function(){xmlhttp.abort();alert(timeout_message);loading('off');}, timeout_delay);
            } else if (type=='get_habitants') {
                xmlhttp.open("POST","<?= $base_url ?>/ajax/get_habitants",true);
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send("im_id="+data.im_id);
                timer = setTimeout(function(){xmlhttp.abort();alert(timeout_message);loading('off');}, timeout_delay);
            } else if (type=='save_habitants_data') {
                xmlhttp.open("POST","<?= $base_url ?>/ajax/save_habitants",true);
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send("habitants="+data);
                timer2 = setTimeout(function(){xmlhttp.abort();alert(timeout_message);loading('off');}, timeout_delay);
            } else if(type =='csv_print') {
                xmlhttp.open("POST","<?= $base_url ?>/play/print",true);
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send("street="+data.street+'&mode='+data.mode);
            };
        }

		function display_choose_CP(){
        	$('#header').css('height',(screen['haut']-20)+'px'); //J'enlève 20px à cause du CSS padding-top de #content

            if (CP == '') {
                var display = '<div id="header_choice_CP" class="select-style">';
                display += '<select id="choose_CP">';
                display += '<option value="" disabled selected>Choose a postal code</option>';
                for (key in data_CP){
                    display += '<option value="'+data_CP[key]+'">'+data_CP[key]+'</option>';
                }
                display += "</select></div>";
                $('#header_choice').append(display);
            }
            $('#choose_CP').change(function(){
            	if ($('#header_choice_street')) {
                    $('#data').empty();
                    reset_nav();
                    $('#header_choice_street').remove();
                    $('#view_result').remove();
                };

            	CP = $(this).val();
                if (CP!=='') {
                    ajax('get_street');
                };
            });
        }

        function display_choose_street(streets){
        	var display = '<div id="header_choice_street" class="select-style">';
        	display += '<select id="choose_street">';
            display += '<option value="" disabled selected>Choose a street</option>';
        	for (key in streets){
        		display += '<option value="'+streets[key]['nom']+'">'+streets[key]['nom']+' ('+type_voie[streets[key]['type_voie']]+')'+'</option>';
        	}
        	display += "</select></div>";
        	$('#header_choice').append(display);

            $('#choose_street').change(function(){
                street = $(this).val();
                $('#data').empty();
                reset_nav();
                $('#view_result').remove();
                
                //display = '<span id="view_result">View results</span>';
                display = '<div id="view_result" class="buttonstyle white">View results</div>'
                $('#header_choice').append(display);
                $('#view_result').on('click', function(){
                    $('html, body').animate({
                        scrollTop: $("#data").offset().top
                    }, speed, easing);
                })

                if (street!=='') {
                    ajax('get_buildings');
                };

            });
        }

        function display_choose_buildings(buildings){

            display_tips = '<div id="tips" style="text-align:left;padding:5px;"><br><strong>Conseils d\'utilisation :</strong><br><br>- Enregistrez régulièrement, au fur et à mesure, vos modifications.<br><br>- Appuyez plusieurs fois rapidement sur les flèches pour naviguer plus rapidement parmis les n° d\'immeubles.<br><br>- Pour changer de rue ou d\'arondissement, appuyer sur le nom de la rue en bas.</div>';
            $('#data').css('height', screen['haut']+150).html(display_tips);


            var display = '<ul id="immeuble" class="immeuble">';
            var display2 = '<ul class="immeuble">';
            for (var i = 0; i < buildings.length; i++) {
                if (buildings[i].num%2 == 0){
                    display2 += '<li class="buildings_num" data-buildings_num="'+i+'">'+buildings[i].num+' '+buildings[i].num2+'</li>';
                    nav_2 += 1 ;
                }
                else{
                    display += '<li class="buildings_num" data-buildings_num="'+i+'">'+buildings[i].num+' '+buildings[i].num2+'</li>';
                    nav_1 += 1 ;
                }
            };
            display += '</ul>';
            display2 += '</ul>';         
        	var buildings_nav = '';
            buildings_nav += '<div id="buildings_nav"><div id="buildings_line_1" style="left:0px;"></div>';
            buildings_nav += '<div id="buildings_line_2"><div id="buildings_nav_left"><img width="24" height="15" title="" alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAPCAYAAAD+pA/bAAAABmJLR0QA/wD/AP+gvaeTAAAA3UlEQVQ4jb3UIUtDURiA4UdBsAgOoyizL64uGGU/wnajdf9A/AtWo0mwjJkN2sxWXVXR4Bh4DfPKZR53vjvQL7+c5x7OuYfYtHGJ62C/jQvc5sJVHOENJSaBxQ/x9NWXi8Iu7mphDtjFaK5PAus4wTQRp4AVFHhJ9D+Afdz/EqaAHQwX9N/ABk7xkYnrQIHnTF9CL/PV88AWzoN9CeMG8QTHDfr/2QGsYYD3BmdwgIcoUE0HN0EANs0uRxhg9vcWeA0A1fTxGAWq2cNVEIAWzpoA1W7+7C2qT9uSr+knWUnH5X/sO18AAAAASUVORK5CYII=" /></div>';
            buildings_nav += '<div id="buildings_nav_name"></div>';
            buildings_nav += '<div id="buildings_nav_right"><img width="24" height="15" title="" alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAPCAYAAAD+pA/bAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAGYktHRAD/AP8A/6C9p5MAAAD6SURBVDhPY2BgYDgFxBuAWBqIiQFHgYo2A7ECMYpBav5D8XsgHU+Epp9Q9V+AdB4QMxHSA7MARi8CahDEowlmAUz9bqBaRXyWoFsA4j8FYm8cmtAtAKn/DMRpuHyDzQKY2EygJgE0i7BZAFN/EqhWB91h+CwAyT0BYk8kTfgsAKn/AcTlQMwK00PIApj8KqAGYSAmZAFM/W2gWluQJcRaAFLXRoIFIPXPSLGAZj74AE0hsCAlFET/gApBiYOXmDjYAVQkS0IqAoW7IzGp6CPU1YzoinHEwW+geAcQc2BRjxHJu4CK5LAphIqhB9FFoLgJHvW0L4toWpoCALWox+UWixZPAAAAAElFTkSuQmCC" /></div></div>';
            buildings_nav += '<div id="buildings_line_3" style="left:0px;"></div></div>';
           
            $('#contents').append(buildings_nav);
            $('#buildings_nav_name').html('<img id="button_print" src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/print.png" style="width:80%;max-width: 32px;float: left;margin-left: 5px;margin-top: 9px;"><span id="street_name">'+street+'</span>');
            var width= $('#buildings_line_1').width();
            var height = $('#buildings_line_1').height();
            
            if (nav_1 > nav_2) {
                nav_width = (width/6)*(nav_1-6);
            } else{
                nav_width = (width/6)*(nav_2-6);
            };

            $('#buildings_line_1').append(display);
            $('#buildings_line_3').append(display2);
            $('.buildings_num, #buildings_nav_left, #buildings_nav_right').css('width',width/6);
            $('#buildings_nav_name').css('width',(width/6)*4);

            $('html, body').animate({
                scrollTop: $("#data").offset().top
            }, speed, easing);


            $('#button_print').on('click', function(){
                CSV_print();
            });
           

           // Building_nav Touch gestures
            $('#street_name').on('click', function(){
                $('html, body').animate({
                    scrollTop: $("#contents").offset().top
                }, speed, easing);
            });
            
            Hammer(document.getElementById('buildings_nav'))
            .on("dragstart", function(event) {
                nav_pos = parseInt($('#buildings_line_1').css("left"));
            })
            .on("dragleft", function(event) {
                event.gesture.preventDefault();
                pos = nav_pos + event.gesture.deltaX ;
                $('#buildings_line_1, #buildings_line_3 ').animate({
                    left: pos+'px'
                }, 0);
            })
            .on("dragright", function(event) {
                event.gesture.preventDefault();
                pos = nav_pos - event.gesture.deltaX ;
                $('#buildings_line_1, #buildings_line_3 ').animate({
                    left: (nav_pos+event.gesture.deltaX)+'px'
                }, 0);
            })
            .on("dragend", function(event) {
                nav_pos = parseInt($('#buildings_line_1').css("left"));
                if (nav_pos > 0) {
                    $('#buildings_line_1, #buildings_line_3 ').animate({
                        left: '0px'
                    }, 0);
                    return;
                }
                else if (nav_pos < (-nav_width) ) {
                    $('#buildings_line_1, #buildings_line_3 ').animate({
                        left: (-nav_width)+'px'
                    }, 0);
                };
            });

            Hammer(document.getElementById('buildings_nav_left'))
            .on("tap", function(event) {
                nav_pos = parseInt($('#buildings_line_1').css("left"));
                pos = nav_pos + width ;
                if (nav_pos == 0){
                    return;
                }
                else if (nav_pos < 0 && nav_pos > (-width)) {
                    $('#buildings_line_1, #buildings_line_3 ').animate({
                        left: '0px'
                    }, 0);
                }
                else{
                    $('#buildings_line_1, #buildings_line_3 ').animate({
                        left: pos+'px'
                    }, 0);
                };
            })
            .on("doubletap", function(event) {
                nav_pos = parseInt($('#buildings_line_1').css("left"));
                pos = nav_pos + width + width + width ;
                if (nav_pos == 0){
                    return;
                }
                else if (nav_pos < 0 && nav_pos > (-width*3)) {
                    $('#buildings_line_1, #buildings_line_3 ').animate({
                        left: '0px'
                    }, 0);
                }
                else{
                    $('#buildings_line_1, #buildings_line_3 ').animate({
                        left: pos+'px'
                    }, 0);
                };
            })

            Hammer(document.getElementById('buildings_nav_right'))
            .on("tap", function(event) {
                nav_pos = parseInt($('#buildings_line_1').css("left"));
                pos = nav_pos - width ;
                if (nav_pos == (-nav_width)){
                    return;
                }
                else if (nav_pos > (-nav_width) && nav_pos < (-nav_width+width)) {
                    $('#buildings_line_1, #buildings_line_3 ').animate({
                        left: (-nav_width)+'px'
                    }, 0);
                }
                else{
                    $('#buildings_line_1, #buildings_line_3 ').animate({
                        left: pos+'px'
                    }, 0);
                };
            })
            .on("doubletap", function(event) {
                nav_pos = parseInt($('#buildings_line_1').css("left"));
                pos = nav_pos - width - width - width;
                if (nav_pos == (-nav_width)){
                    return;
                }
                else if (nav_pos > (-nav_width) && nav_pos < (-nav_width+(width*3))) {
                    $('#buildings_line_1, #buildings_line_3 ').animate({
                        left: (-nav_width)+'px'
                    }, 0);
                }
                else{
                    $('#buildings_line_1, #buildings_line_3 ').animate({
                        left: pos+'px'
                    }, 0);
                };
            })
            

            $('.buildings_num').on('click', function(){
                // Par sécurité avant d'afficher un autre immeuble, on vérifie qu'il n'y a pas eu de modifs des datas de l'immeuble ou des habitants déjà effectuées
                if (data_building_change == 1 || data_habitants_change == 1 ) {
                    if (confirm('Attention !\nDes modifications d\'infos sur l\'immeuble ont été détectées.\nVoulez-vous les enregistrer ?')) { 
                        save_data('save');
                    }
                    else{
                        save_data('reset');
                    }
                };
                if ($('.building_colored').length == 1) {
                    $('.building_colored').removeClass("building_colored");
                    $(this).addClass("building_colored");
                } else {
                    $(this).addClass("building_colored");
                };
                selected_building = $(this).data("buildings_num");
                
                display_building_data(selected_building);
                $('html, body').animate({
                    scrollTop: $("#data_header_building").offset().top
                }, speed, easing);
            });
        }
		
        function display_building_data(selected_building){
            $('#display_building_data, #display_habitants, #tips').empty();

            building = data_street_nbrs[selected_building];
            street_nbr = building.im_id;

            display = '';
            display += '<div id="data_header_building"><span>Info immeuble :</span></div><br><br>';
            display += '<input style="display:none" class="building_form" name="building_id" type="text" value="'+building.im_id+'">';
            display += '<label>Gardien: </label><input class="building_form" name="building_gardien" type="text" value="'+building.gardiens+'">';
            if (building.bal == 0) {
                display += '<label>BAL: </label><select class="building_form" name="building_bal"><option value="0" selected>Non</option><option value="1">Oui</option></select>';
            }
            else{
                display += '<label>BAL: </label><select class="building_form" name="building_bal"><option value="0">Non</option><option value="1" selected>Oui</option></select>';
            };

            display += '<label>Code imm.: </label><input class="building_form" name="building_code" type="text" value="'+building.code+'">';
            display += '<label>Infos: </label><br><br><textarea class="building_form building_textarea" name="building_infos" rows=4 COLS=40>'+building.infos+'</textarea>';

            if ($('#display_building_data').length == 0) {
                $('#data').append('<div id="display_building_data"></div><div id="display_habitants"></div>');
            };

            $('#display_building_data').append(display);
            $('#display_building_data').data('selected_building',selected_building);
            
            // Detect change in buildings data
            $('.building_form').on('change', function(){
                //data_building_change = 1 ;
                save_data('building_change');
            });

            // Ajoute la possibilité de "Voir les habitants"
            //display ='<input name="habitants_display" type="button" value="Voir les habitants">';
            display ='<div id="habitants_display" class="buttonstyle">Voir les habitants</div>';
            $('#display_habitants').append(display);
            $('#habitants_display').click(function(){
                ajax('get_habitants', building);
            })
        }

        function display_habitants_data(type, data){

            if(type == 'display_habitants'){
                $('#display_habitants').empty();
                data_habitants_change = 0 ;
                display = '';
                display += '<div id="data_header_habitants"><span>Info habitants :</span></div><br><br>';
                display += '<div id="habitants_add"><div class="habitant_poignee"><img src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/plus.png" style="width:70%;max-width: 20px;margin-top: 4px;margin-left: 2px;" ></div><div class="habitant_name"><span>Ajouter un habitant</span></div></div>';
                display += '<ul class="habitants" id="liste_habitants">';
                for( key in data){
                    display += '<li id="'+data[key].poids+'" class="habitants"><div class="habitant_poignee handler"><img src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/drag.png" style="width:80%;max-width: 22px;margin-top: 3px;margin-left: 2px;" ></div><div class="habitant_name handler">'+data[key].nom+' '+data[key].prenom+'</div><div class="habitant_edit"><img src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/edit.png" style="width:80%;max-width: 20px;margin-top: 5px;margin-left: 3px;" ></div></li>';
                }
                display += '</ul><br><br>';
                display += '<div id="habitants_add_form"></div>';
                $('#display_habitants').append(display);
                hauteur = $('#display_building_data').height() + $('#display_habitants').height();
                hauteur_data = $('#data').height();
                if (hauteur > hauteur_data) {
                    $('#data').css('height', hauteur+150);
                }
                else{
                    $('#display_habitants').css('height', $('#display_habitants').height()+150);
                };
                
                $('html, body').animate({
                    scrollTop: $("#data_header_habitants").offset().top
                }, speed, easing);


                // Drag n' Drop: modification du poids des habitants
                $(document).ready( function(){ // quand la page a fini de se charger
                    $("#liste_habitants").sortable({ 
                        placeholder: 'highlight', // classe à ajouter à l'élément fantome
                        scrollSpeed: 10,
                        handle: ".handler",
                        update: function() {  // callback quand l'ordre de la liste est changé
                            save_data('habitants_change');      
                        }
                    });
                    $("#liste_habitants").disableSelection(); // on désactive la possibilité au navigateur de faire des sélections
                });


                // Edition d'un habitant
                $('.habitant_edit').on('click', function(){
                    habitant_id = $(this).parent().attr('id');
                    display_habitants_add(habitant_id);
                    $('html, body').animate({
                        scrollTop: $("#data_header_habitants_add").offset().top
                    }, speed, easing);
                });                


                // Ajout d'un habitant
                $('#habitants_add').on('click', function(){
                    display_habitants_add();
                    $('html, body').animate({
                        scrollTop: $("#data_header_habitants_add").offset().top
                    }, speed, easing);
                });

            };
        }

        function display_habitants_add(habitant_id){ 
            $('#habitants_add_form').css('height',screen['haut']);

            if(typeof(habitant_id) == 'undefined'){ // Création d'un habitant
                display = '';
                display += '<div id="data_header_habitants_add"><span>Ajouter un habitant :</span></div><br><br><br>';
                display +='<label>Nom: </label><input name="habitants_add_nom" type="text" value=""><br>';
                display +='<label>Prénom: </label><input name="habitants_add_prenom" type="text" value=""><br>';
                display +='<label>Genre: </label><select id="habitants_add_genre"><option value="0">Aucun</option><option value="1">M. & Mme</option><option value="2">M.</option><option value="3">Mme</option><option value="4">Famille</option><option value="5">Sté</option><option value="6">SCI</option><option value="7">Docteur</option><option value="8">Maître</option></select><br>';
                display +='<label>Tel: </label><input name="habitants_add_tel" type="tel" value=""><br>';
                display +='<label>Email: </label><input name="habitants_add_email" type="email" value=""><br>';
                //display +='<input name="habitants_add_cancel" type="button" value="Annuler">';
                //display +='<input name="habitants_add_save" type="button" value="Enregistrer"><br>';
                //display +='<input name="habitants_add_delete" type="button" value="Supprimer"><br>';
                display += '<br><div id="habitants_add_cancel" class="buttonstyle">Annuler</div><br>';
                display += '<div id="habitants_add_save" class="buttonstyle">Enregistrer</div><br>';
                display += '<div id="habitants_add_delete" class="buttonstyle">Supprimer</div><br>';
                
                // Affiche le formulaire
                $('#habitants_add_form').empty().append(display);
            }
            else{ // Edition d'un habitant
                habitant = data_habitants[habitant_id];

                display = '';
                display += '<div id="data_header_habitants_add"><span>Modifier un habitant :</span></div><br><br><br>';
                display += '<input name="habitants_add_id" type="hidden" value="'+habitant.id+'"> ';
                display += '<input name="habitants_add_poids" type="hidden" value="'+habitant.poids+'"> ';
                display += '<label>Nom: </label><input name="habitants_add_nom" type="text" value="'+habitant.nom+'"><br>';
                display += '<label>Prénom: </label><input name="habitants_add_prenom" type="text" value="'+habitant.prenom+'"><br>';
                display +='<label>Genre: </label><select id="habitants_add_genre"><option value="0">Aucun</option><option value="1">M. & Mme</option><option value="2">M.</option><option value="3">Mme</option><option value="4">Famille</option><option value="5">Sté</option><option value="6">SCI</option><option value="7">Docteur</option><option value="8">Maître</option></select><br>';
                display += '<label>Tel: </label><input name="habitants_add_tel" type="tel" value="'+habitant.tel+'"><br>';
                display += '<label>Email: </label><input name="habitants_add_email" type="email" value="'+habitant.email+'"><br>';
                //display += '<input name="habitants_add_cancel" type="button" value="Annuler">';
                //display += '<input name="habitants_add_edit" type="button" value="Modifier"><br>';
                //display += '<input name="habitants_add_delete" type="button" value="Supprimer"><br>';
                display += '<br><div id="habitants_add_cancel" class="buttonstyle">Annuler</div><br>';
                display += '<div id="habitants_add_edit" class="buttonstyle">Modifier</div><br>';
                display += '<div id="habitants_add_delete" class="buttonstyle">Supprimer</div><br>';

                // Affiche le formulaire
                $('#habitants_add_form').empty().append(display);
                $("#habitants_add_genre").val(habitant.genre);
            }

            // Annuler
            $('#habitants_add_cancel').on('click', function(){
                $('#habitants_add_form').empty();
                $('html, body').animate({
                    scrollTop: $("#display_habitants").offset().top
                }, speed, easing);
            });

            // Supprimer
            $('#habitants_add_delete').on('click', function(){
                if (confirm('Supprimer cet habitant ?')) { 
                    delete data_habitants[habitant_id];
                    $('#habitants_add_form').empty();
                    display_habitants_data('display_habitants', data_habitants);
                    //data_habitants_change = 1 ;
                    save_data('habitants_change');
                }
            });

            // Ajoute, enregistre et ferme
            $('#habitants_add_save').on('click', function(){
                
                // On recalcule l'ordre et le poids de chaque habitant
                var order = $('#liste_habitants').sortable('toArray');
                sortable = [];
                for( key in order){
                    sortable[key] = data_habitants[order[key]];
                    sortable[key].poids = key;
                }
                data_habitants = sortable;

                habitant_added = {};
                habitant_added.nom = $('input[name=habitants_add_nom]').val();
                habitant_added.nom = habitant_added.nom.replace(/\;/g, '-');
                habitant_added.prenom = $('input[name=habitants_add_prenom]').val();
                habitant_added.prenom = habitant_added.prenom.replace(/\;/g, '-');
                habitant_added.genre = $('#habitants_add_genre').val();
                habitant_added.tel = $('input[name=habitants_add_tel]').val();
                habitant_added.tel = habitant_added.tel.replace(/\;/g, '-');
                habitant_added.email = $('input[name=habitants_add_email]').val();
                habitant_added.email = habitant_added.email.replace(/\;/g, '-');
                habitant_added.im_id = street_nbr ;

                // On calcule le poids à donner en comptant le nbre d'éléments existant
                var last_key = '';
                if (sortable.length == 0 ) { // Dans le cas où il n'y avait pas encore d'habitant
                    last_key = 0 ;
                }
                else{
                    for (key in data_habitants){
                        last_key = key ;
                    }
                    last_key = parseInt(last_key) + 1 ;
                }
                console.log ('last key = '+last_key);
                habitant_added.poids = last_key ;
                data_habitants.push(habitant_added);
                //console.debug(data_habitants);
                display_habitants_data('display_habitants', data_habitants);
                //save_habitants_data('habitants_change');
                save_data('habitants_change');
            });

            // Modifie, enregistre et ferme
            $('#habitants_add_edit').on('click', function(){
                habitant_added = {};
                habitant_added.nom = $('input[name=habitants_add_nom]').val();
                habitant_added.nom = habitant_added.nom.replace(/\;/g, '-');
                habitant_added.prenom = $('input[name=habitants_add_prenom]').val();
                habitant_added.prenom = habitant_added.prenom.replace(/\;/g, '-');
                habitant_added.genre = $('#habitants_add_genre').val();
                habitant_added.tel = String($('input[name=habitants_add_tel]').val());
                habitant_added.tel = habitant_added.tel.replace(/\;/g, '-');
                habitant_added.email = $('input[name=habitants_add_email]').val();
                habitant_added.email = habitant_added.email.replace(/\;/g, '-');
                habitant_added.im_id = street_nbr ;
                habitant_added.id = $('input[name=habitants_add_id]').val();
                habitant_added.poids = $('input[name=habitants_add_poids]').val();

                data_habitants[habitant_id] =  habitant_added;
                // On recalcule l'ordre et le poids de chaque habitant
                var order = $('#liste_habitants').sortable('toArray');
                sortable = [];
                for( key in order){
                    sortable[key] = data_habitants[order[key]];
                    sortable[key].poids = key;
                }
                data_habitants = sortable;
                display_habitants_data('display_habitants', data_habitants);
                //save_habitants_data('habitants_change');
                save_data('habitants_change');
            });
        }

        function save_data(type, data){
            if (type == 'reset') {
                data_habitants_change = 0 ;
                data_building_change = 0 ;
                if ($('#button_rec').length) {
                    $('#button_rec').remove();
                };
            }
            else if (type=="button_rec"){
                if ($('#button_rec').length) {
                    // do nothing
                }
                else{
                    $('#buildings_nav_name').append('<img id="button_rec" src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/save.png" style="width:80%;max-width: 32px;float: right;margin-right: 5px;margin-top: 9px;">');
                    $('#button_rec').on('click', function(){
                        save_data('save');
                    });
                }
            }
            else if (type=='success') {
                data_habitants_change = 0 ;
                data_building_change = 0 ;
                if ($('#button_rec').length) {
                    $('#button_rec').replaceWith('<img id="button_rec" src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/save_ok.png" style="width:80%;max-width: 32px;float: right;margin-right: 5px;margin-top: 9px;">');
                    $('#button_rec').fadeOut( 5000, function() {
                        $(this).remove();
                    });
                }
            }
            else if (type=='habitants_change') {
                data_habitants_change = 1 ;
                save_data('button_rec');
            }
            else if (type=='building_change') {
                data_building_change = 1 ;
                save_data('button_rec');
            }
            else if (type=="save") {
                if (data_building_change == 1 && data_habitants_change == 1) {
                    save_data('habitants_save');
                    save_data('building_store_server');
                }
                else if (data_building_change == 1) {
                    save_data('building_store_server');
                }
                else if (data_habitants_change == 1) {
                    save_data('habitants_save');
                }
            }
            else if (type =='habitants_save') {
                var order = $('#liste_habitants').sortable('toArray'); // récupération des données à envoyer
                console.debug(order);
                sortable = [];
                for( key in order){
                    sortable[key] = data_habitants[order[key]];
                    sortable[key].poids = key;
                }
                data_habitants = sortable;

                var serializedArr = JSON.stringify( data_habitants );
                //console.debug(serializedArr);
                ajax('save_habitants_data', serializedArr);
            }
            else if (type=="building_store_server") {
                record = new Array();
                record['array_key'] = selected_building;
                record['im_id'] = $('input[name=building_id]').val();
                record['gardiens'] = $('input[name=building_gardien]').val();
                record['bal'] = $('select[name=building_bal]').val();
                record['code'] = $('input[name=building_code]').val();
                record['infos'] = $('textarea[name=building_infos]').val();
                ajax('save_building', record);
            }
            else if (type=="building_store_local") {
                data_street_nbrs[data.array_key] = data;
            }
            else if (type =='inline_1') {

            };
        }

        function CSV_print(){

            var display = '';
            var csv_data ={};
            display += '<div id="csv_print">';
            display += '<br><span>Vous allez recevoir, par email, votre fichier d\'adresses pour :<br>'+street+'<br><br>Merci de valider le sens de distribution désiré :</span><br><br>';
            display += '<div id="print_build_line1"><div class="csv_print_line_1_3">1</div><div class="csv_print_line_1_3">3</div><div class="csv_print_line_1_3">5</div><div class="csv_print_line_1_3">7</div><div class="csv_print_line_1_3">9</div></div>';
            display += '<div id="print_build_line2"><div data-mode="1" class="csv_print_line_2 mode_colored" ><img src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/Mode_1.png"></div><div data-mode="2" class="csv_print_line_2"><img src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/Mode_2.png"></div><div data-mode="3" class="csv_print_line_2"><img src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/Mode_3.png"></div><div data-mode="4" class="csv_print_line_2"><img src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/Mode_4.png"></div><div data-mode="5" class="csv_print_line_2"><img src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/Mode_5.png"></div></div>';
            display += '<div id="print_build_line3"><div class="csv_print_line_1_3">2</div><div class="csv_print_line_1_3">4</div><div class="csv_print_line_1_3">6</div><div class="csv_print_line_1_3">8</div><div class="csv_print_line_1_3">10</div></div>';

            display += '<br><br><div style="display: inline-block;position:relative;margin-top: 10px;"><div id="csv_cancel" class="buttonstyle">Annuler</div><br><div id="csv_valid" class="buttonstyle">Valider</div></div>';
            display += '</div>';

            $('#csvprint_box').append(display);

            $('.csv_print_line_2').on('click', function(){
                $('.mode_colored').removeClass('mode_colored');
                $(this).addClass('mode_colored');
            });

            $('#csv_cancel').on('click', function(){
                $('#csvprint_box').empty();
            });

            $('#csv_valid').on('click', function(){
                csv_data.mode = $('.mode_colored').data('mode');
                csv_data.street = street;
                ajax('csv_print', csv_data);
                $('#csvprint_box').empty();

            });
        }

	</script>

</head>
<body>

	<style>

		/*Zones et block*/
		body{
			background-image: url('<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/rayures_claires.png');
			background-repeat: repeat;
			background-color: #4A66E5;
			font-family: 'Yanone Kaffeesatz', sans-serif;
			text-align: center;
			width: 100%;
			height: 100%
		}
		#contents{
			position: absolute;
			top: 0px;
			left: 0px;
			width: 100%;
			height: 100%;
			padding-top: 20px;
		}
        .buttonstyle{
            display: block;
            position: relative;
            width: 150px;
            height: 25px;
            line-height: 25px;
            margin: auto;
            padding-left: 5px;
            padding-right: 5px;
            color: #2C3E50;
            border: solid 2px #2C3E50;
            font-weight: bold;
            cursor: pointer;
        }
        .white{
            color: white;
            border: solid 2px white;
        }
		#data, #habitants_add_form{
			position: relative;
			/*background-color: rgb(87, 87, 87);
            color: whitesmoke;*/
            background-color: rgb(202, 202, 202);
            color: #2C3E50;
		}
        #header_choice_street{
           margin-top: 15px;
           margin-bottom: 15px;
        }

        /*  #data_header{
            display: inline-block;
            width: 100%;
        }*/
        #data_header_building, #data_header_habitants, #data_header_habitants_add{
            float: left;
            width: 100%;
            border: solid 1px white;
            height: 30px;
            line-height: 30px;
            color: white;
            background-color: #2C3E50;
        }
        /*#data_header_habitants, #data_header_habitants{
            float: left;
            width: 50%;
        }*/
        
        label{
            display: block;
            width: 110px;
            float: left;
            text-align: right;
            padding-right: 10px;
        }
        .building_form{
            display: block;
        }
        .building_textarea{
            width: 90%;
            margin: auto;
        }
		


		/*Buildings*/
		 #buildings_nav{
            position: fixed;
			bottom: 0px;
			left: 0px;
			width: 100%;
			height: 150px;
			/*background-color: #293133;*/
            background-color: #2C3E50;
            overflow: hidden;
            color: whitesmoke;
        }
        #buildings_line_1{
            position: relative;
            left: 0px;

        }
        #buildings_line_2{
            height: 50px;
            background-color: whitesmoke;
            color: black;
        }
        #buildings_line_3{
            position: relative;
            left: 0px;
        }
        #buildings_nav_left{
            width: 50px;
            height: 50px;
            border-right: solid 1px black;
            position: relative;
            float: left;
            text-align: center;
            line-height: 50px;
        }
        #buildings_nav_name{
            height: 50px;
            position: relative;
            float: left;
            text-align: center;
            line-height: 50px;
        }
        #street_name{
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        #buildings_nav_rec{
            width: 20px;
            height: 50px;
            position: relative;
            float: right;
            background-color: red;
        }
        #buildings_nav_right{
            width: 50px;
            height: 50px;
            border-left: solid 1px black;
            position: relative;
            float: right;
            text-align: center;
            line-height: 50px;
        }
        .building_colored{
            /*background-color: red;*/
            background-color: #3498DB;
        }
        ul.immeuble { 
            white-space: nowrap;
            height: 50px;
            width: 100%;
            
        }
        li.buildings_num {
            display:inline-block; 
            width: 50px;
            height: 50px;
            border: solid 1px black;
            text-align: center;
            list-style-type: none;
            line-height: 50px;
        }

        /*Habitants*/
        #display_habitants{
            margin-top: 10px;
        }

        li.habitants, #habitants_add{
            display: inline-block;
            margin: auto;
            width: 90%;
            height: 30px;
            line-height: 30px;
            color: black;
            background-color: whitesmoke;
            border-radius: 4px;
            border: solid 1px grey;
        }

        .habitant_poignee{
            display: block;
            width: 10%;
            border-right: dashed 1px grey;
            float: left;
        }
        .habitant_name{
            display: block;
            width: 80%;
            float: left;
        }
        .habitant_edit{
            display: block;
            width: 10%;
            float: right;
            padding-right: 5px;
            border-left: dashed 1px grey;
        }

        #csv_print{
            position: fixed;
            z-index: 10;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            background-color: white;
        }

        .csv_print_line_1_3{
            width:20%;
            height:32px;
            line-height:32px;
            float:left;
            background-color:grey;
        }
        .csv_print_line_2{
            width:20%;
            height:32px;
            line-height:32px;
            float:left;
            background-color:white;
        }
        .mode_colored{
            /*background-color: red;*/
            background-color: #3498DB;
        }



		/*Loading animation*/
		
		*,
		*:before,
		*:after {
		    box-sizing: border-box;
		}
		#loading{
			position: fixed;
            z-index: 10;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
		}
		#loading_bg{
			position: fixed;
            /*z-index: 10;*/
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            background-color: black;
            moz-opacity:0.8;
            opacity: 0.8;
            filter:alpha(opacity=80);
		}

		body:after {
		    content: "";
		    z-index: -1;
		    position: absolute;
		    top: 0; right: 0; bottom: 0; left: 0;
		    background: -webkit-radial-gradient(center center, circle cover, rgba(0,0,0,0), rgba(0,0,0,0.75));
		    background: -moz-radial-gradient(center center, circle cover, rgba(0,0,0,0), rgba(0,0,0,0.75));
		    background: -ms-radial-gradient(center center, circle cover, rgba(0,0,0,0), rgba(0,0,0,0.75));
		    background: -o-radial-gradient(center center, circle cover, rgba(0,0,0,0), rgba(0,0,0,0.75));
		    background: radial-gradient(center center, circle cover, rgba(0,0,0,0), rgba(0,0,0,0.75));
		}

		.bokeh {
			font-size: 100px;
		    width: 1em;
		    height: 1em;
		    position: relative;
		    margin: 100px auto;
		    border-radius: 50%;
		    border: .01em solid rgba(150,150,150,0.1);
		    list-style: none;
		    z-index: 11;
		    }

		.bokeh li {
		    position: absolute;
		    width: .2em;
		    height: .2em;
		    border-radius: 50%;
		}

		.bokeh li:nth-child(1) {
		    left: 50%;
		    top: 0;
		    margin: 0 0 0 -.1em;
		    background: #00C176;
		    -webkit-transform-origin: 50% 250%;
		    -moz-transform-origin: 50% 250%;
		    -ms-transform-origin: 50% 250%;
		    -o-transform-origin: 50% 250%;
		    transform-origin: 50% 250%;
		    -webkit-animation: 
		        rota 1.13s linear infinite,
		        opa 3.67s ease-in-out infinite alternate;
		    -moz-animation: 
		        rota 1.13s linear infinite,
		        opa 3.67s ease-in-out infinite alternate;
		    -ms-animation: 
		        rota 1.13s linear infinite,
		        opa 3.67s ease-in-out infinite alternate;
		    -o-animation: 
		        rota 1.13s linear infinite,
		        opa 3.67s ease-in-out infinite alternate;
		    animation: 
		        rota 1.13s linear infinite,
		        opa 3.67s ease-in-out infinite alternate;
		}

		.bokeh li:nth-child(2) {
		    top: 50%; 
		    right: 0;
		    margin: -.1em 0 0 0;
		    background: #FF003C;
		    -webkit-transform-origin: -150% 50%;
		    -moz-transform-origin: -150% 50%;
		    -ms-transform-origin: -150% 50%;
		    -o-transform-origin: -150% 50%;
		    transform-origin: -150% 50%;
		    -webkit-animation: 
		        rota 1.86s linear infinite,
		        opa 4.29s ease-in-out infinite alternate;
		    -moz-animation: 
		        rota 1.86s linear infinite,
		        opa 4.29s ease-in-out infinite alternate;
		    -ms-animation: 
		        rota 1.86s linear infinite,
		        opa 4.29s ease-in-out infinite alternate;
		    -o-animation: 
		        rota 1.86s linear infinite,
		        opa 4.29s ease-in-out infinite alternate;
		    animation: 
		        rota 1.86s linear infinite,
		        opa 4.29s ease-in-out infinite alternate;
		}

		.bokeh li:nth-child(3) {
		    left: 50%; 
		    bottom: 0;
		    margin: 0 0 0 -.1em;
		    background: #FABE28;
		    -webkit-transform-origin: 50% -150%;
		    -moz-transform-origin: 50% -150%;
		    -ms-transform-origin: 50% -150%;
		    -o-transform-origin: 50% -150%;
		    transform-origin: 50% -150%;
		    -webkit-animation: 
		        rota 1.45s linear infinite,
		        opa 5.12s ease-in-out infinite alternate;
		    -moz-animation: 
		        rota 1.45s linear infinite,
		        opa 5.12s ease-in-out infinite alternate;
		    -ms-animation: 
		        rota 1.45s linear infinite,
		        opa 5.12s ease-in-out infinite alternate;
		    -o-animation: 
		        rota 1.45s linear infinite,
		        opa 5.12s ease-in-out infinite alternate;
		    animation: 
		        rota 1.45s linear infinite,
		        opa 5.12s ease-in-out infinite alternate;
		}

		.bokeh li:nth-child(4) {
		    top: 50%; 
		    left 0;
		    margin: -.1em 0 0 0;
		    background: #88C100;
		    -webkit-transform-origin: 250% 50%;
		    -moz-transform-origin: 250% 50%;
		    -ms-transform-origin: 250% 50%;
		    -o-transform-origin: 250% 50%;
		    transform-origin: 250% 50%;
		    -webkit-animation: 
		        rota 1.72s linear infinite,
		        opa 5.25s ease-in-out infinite alternate;
		    -moz-animation: 
		        rota 1.72s linear infinite,
		        opa 5.25s ease-in-out infinite alternate;
		    -ms-animation: 
		        rota 1.72s linear infinite,
		        opa 5.25s ease-in-out infinite alternate;
		    -o-animation: 
		        rota 1.72s linear infinite,
		        opa 5.25s ease-in-out infinite alternate;
		    animation: 
		        rota 1.72s linear infinite,
		        opa 5.25s ease-in-out infinite alternate;
		}

		@-webkit-keyframes rota {
		    to { -webkit-transform: rotate(360deg); }
		}

		@-moz-keyframes rota {
		    to { -moz-transform: rotate(360deg); }
		}

		@-ms-keyframes rota {
		    to { -ms-transform: rotate(360deg); }
		}

		@-o-keyframes rota {
		    to { -o-transform: rotate(360deg); }
		}

		@keyframes rota {
		    to { transform: rotate(360deg); }
		}

		@-webkit-keyframes opa {
		    12.0% { opacity: 0.80; }
		    19.5% { opacity: 0.88; }
		    37.2% { opacity: 0.64; }
		    40.5% { opacity: 0.52; }
		    52.7% { opacity: 0.69; }
		    60.2% { opacity: 0.60; }
		    66.6% { opacity: 0.52; }
		    70.0% { opacity: 0.63; }
		    79.9% { opacity: 0.60; }
		    84.2% { opacity: 0.75; }
		    91.0% { opacity: 0.87; }
		}

		@-moz-keyframes opa {
		    12.0% { opacity: 0.80; }
		    19.5% { opacity: 0.88; }
		    37.2% { opacity: 0.64; }
		    40.5% { opacity: 0.52; }
		    52.7% { opacity: 0.69; }
		    60.2% { opacity: 0.60; }
		    66.6% { opacity: 0.52; }
		    70.0% { opacity: 0.63; }
		    79.9% { opacity: 0.60; }
		    84.2% { opacity: 0.75; }
		    91.0% { opacity: 0.87; }
		}

		@-ms-keyframes opa {
		    12.0% { opacity: 0.80; }
		    19.5% { opacity: 0.88; }
		    37.2% { opacity: 0.64; }
		    40.5% { opacity: 0.52; }
		    52.7% { opacity: 0.69; }
		    60.2% { opacity: 0.60; }
		    66.6% { opacity: 0.52; }
		    70.0% { opacity: 0.63; }
		    79.9% { opacity: 0.60; }
		    84.2% { opacity: 0.75; }
		    91.0% { opacity: 0.87; }
		}

		@-o-keyframes opa {
		    12.0% { opacity: 0.80; }
		    19.5% { opacity: 0.88; }
		    37.2% { opacity: 0.64; }
		    40.5% { opacity: 0.52; }
		    52.7% { opacity: 0.69; }
		    60.2% { opacity: 0.60; }
		    66.6% { opacity: 0.52; }
		    70.0% { opacity: 0.63; }
		    79.9% { opacity: 0.60; }
		    84.2% { opacity: 0.75; }
		    91.0% { opacity: 0.87; }
		}

		@keyframes opa {
		    12.0% { opacity: 0.80; }
		    19.5% { opacity: 0.88; }
		    37.2% { opacity: 0.64; }
		    40.5% { opacity: 0.52; }
		    52.7% { opacity: 0.69; }
		    60.2% { opacity: 0.60; }
		    66.6% { opacity: 0.52; }
		    70.0% { opacity: 0.63; }
		    79.9% { opacity: 0.60; }
		    84.2% { opacity: 0.75; }
		    91.0% { opacity: 0.87; }
		}

		/*Select & inputs*/
		.select-style {
		    border: 1px solid #ccc;
		    width: 230px;
		    border-radius: 3px;
		    overflow: hidden;
		    background: #fafafa url("data:image/png;base64,R0lGODlhDwAUAIABAAAAAP///yH5BAEAAAEALAAAAAAPABQAAAIXjI+py+0Po5wH2HsXzmw//lHiSJZmUAAAOw==") no-repeat 90% 50%;
		    margin: auto;
		}
		.select-style select {
		    padding: 5px 8px;
		    width: 130%;
		    border: none;
		    box-shadow: none;
		    background: transparent;
		    background-image: none;
		    -webkit-appearance: none;
		}
		.select-style select:focus {
		    outline: none;
		}
		.button_submit{
		  	background:#bfd70e;
		  	border-radius:50%;
		  	width:55px;
		  	height:55px;
		  	border:2px solid #679403;
		  	line-height: 55px;
		  	text-align: center;
		  	display: block;
			margin: auto;
			font-weight: bold;
			padding: 0px;
		}
		.enjoy-css {
			display: inline-block;
			-webkit-box-sizing: content-box;
			-moz-box-sizing: content-box;
			box-sizing: content-box;
			padding: 10px 20px;
			border: 1px solid #b7b7b7;
			-webkit-border-radius: 3px;
			border-radius: 3px;
			font: normal normal normal 16px/normal "Times New Roman", Times, serif;
			color: rgba(0,142,198,1);
			-o-text-overflow: clip;
			text-overflow: clip;
			background: rgba(252,252,252,1);
			text-shadow: 1px 1px 0 rgba(255,255,255,0.66) ;
			-webkit-transition: all 200ms cubic-bezier(0.42, 0, 0.58, 1);
			-moz-transition: all 200ms cubic-bezier(0.42, 0, 0.58, 1);
			-o-transition: all 200ms cubic-bezier(0.42, 0, 0.58, 1);
			transition: all 200ms cubic-bezier(0.42, 0, 0.58, 1);
		}
		#user-login-form {
			text-align: center; 
		}

	</style>

    <div id="lightbox_bg"></div>
    <div id="lightbox"></div>
    <div id="csvprint_box"></div>

	<div id="contents">
		
        <div id="header" style="height:500px">
    		<div id="header_logo">	
    			<img src="<?= $base_url ?>/sites/default/files/1398135398_office-building.png" style="width:80%;max-width: 100px;" >
    			<br>
    			<span style="font-size: xx-large;">Immomemo<br><br></span>
    		</div>
    		<div id="header_choice">
    		</div>
        </div>

		<div id="data">
            <div id="display_building_data">
            </div>

            <div id="display_habitants">
            </div>
		</div>

	</div>

    <script src="<?= $base_url ?>/sites/all/libraries/jquery-1.11.0.min.js"></script>
    <script src="<?= $base_url ?>/sites/all/libraries/jquery-ui-1.10.4.custom.min.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>/sites/all/libraries/hammer.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>/sites/all/libraries/jquery.ui.touch-punch.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){

            var screen = get_screen_size();
            //alert(screen['haut']+' '+screen['larg']);
            display_choose_CP();
            
            /*Building_nav display controler*/
            var $window = $(window);
            $window.scroll(function () {
                if ($window.scrollTop() == 0)
                    $('#buildings_nav').hide(350);
                else if ($window.scrollTop() > screen['haut']-20) {
                    $('#buildings_nav').show(350);
                }
            });

        });
    </script>
</body>
</html>