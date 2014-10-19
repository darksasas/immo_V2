<?php
global $base_url;



?>
<!DOCTYPE html>
<html  lang="en" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>

	<link rel="shortcut icon" href="<?= $base_url ?>/sites/default/files/1398135398_office-building.png" type="image/png" />
    <link rel="stylesheet" type="text/css" href="http://necolas.github.io/normalize.css/3.0.1/normalize.css">
	<script src="<?= $base_url ?>/sites/all/libraries/jquery-1.11.0.min.js"></script>
    <script src="<?= $base_url ?>/sites/all/libraries/jquery-ui-1.10.4.custom.min.js"></script>

	<script type="text/javascript">
		
		var CP = "";
		var street = "";
		var street_nbr = "";

		<?php // print javascript data_CP variable
			echo 'var data_CP = [';
			foreach ($CP as $key => $value) {
				echo '"'.$value.'",';
			}
			echo "];\n";
		?>
		var data_streets = "";
		var data_street_nbrs = "";
        var data_habitants = "";
        var data_habitants_change = 0 ;
		var ajax_result="";

		function get_screen_size(){
			if (document.body){
				var larg = (document.getElementById('contents').clientWidth);
				var haut = (document.getElementById('contents').clientHeight);
			} 
			else{
				var larg = (window.innerWidth);
				var haut = (window.innerHeight);
			}
			screen['larg'] = larg;
			screen['haut'] = haut;
			return screen
		}
		
		function ajax(type, data){
            var xmlhttp;   

            if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
              	xmlhttp=new XMLHttpRequest();
            } 
            else{// code for IE6, IE5
              	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange=function(){
            	if (xmlhttp.readyState==1) {
              		//$('#ajax-loading').show();
              	}
              	else if (xmlhttp.readyState==4 && xmlhttp.status==200){
                	ajax_result = xmlhttp.responseText;
                    if (type=="get_street"){
                		data_streets = JSON.parse(ajax_result);
                		display_choose_street(data_streets);
                	} 
                    else if (type=="get_buildings") {
                        data_street_nbrs = JSON.parse(ajax_result);
                        display_choose_buildings(data_street_nbrs);
                    } 
                    else if (type=='save_building'){
                        if (ajax_result !== 'ok') {
                            alert(ajax_result);
                        }
                        else { // On enregistre les donénes en locales pour l'affichage
                            save_building_data('store_local', data);
                            $('#building_message').data("flag", "0");
                            $('input[name=save_building]').remove();
                        };
                    } 
                    else if (type=='get_habitants') {
                        data_habitants = JSON.parse(ajax_result);
                        display_habitants_data('display_habitants', data_habitants);
                    }
                    else if (type=='save_habitants_data'){
                        console.debug(ajax_result);
                        data_habitants = JSON.parse(ajax_result);
                        save_habitants_data('habitants_success');
                    };
              	}
            }
            if (type=='get_CP') {
            	xmlhttp.open("GET","<?= $base_url ?>/ajax/get_CP",true);
				xmlhttp.send();
            } else if (type=='get_street'){
            	xmlhttp.open("POST","<?= $base_url ?>/ajax/get_street",true);
				xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
				xmlhttp.send("CP="+CP);
            } else if (type=='get_buildings') {
                xmlhttp.open("POST","<?= $base_url ?>/ajax/get_buildings",true);
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send("street="+street);
            } else if (type=='save_building') {
                xmlhttp.open("POST","<?= $base_url ?>/ajax/save_building",true);
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send("im_id="+data.im_id+"&gardiens="+data.gardiens+"&bal="+data.bal+"&code="+data.code+"&infos="+data.infos);
            } else if (type=='get_habitants') {
                xmlhttp.open("POST","<?= $base_url ?>/ajax/get_habitants",true);
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send("im_id="+data.im_id);
            } else if (type=='save_habitants_data') {
                xmlhttp.open("POST","<?= $base_url ?>/ajax/save_habitants",true);
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send("habitants="+data);
            };
        }

        function display_choose_CP(){
        	if (CP == '') {
                var display = "Choose a postal code:";
                display += '<select id="choose_CP">';
                display += '<option value=""></option>';
                for (key in data_CP){
                    display += '<option value="'+data_CP[key]+'">'+data_CP[key]+'</option>';
                }
                display += "</select>";
                $('#search_bar').append(display);
            }
            $('#choose_CP').change(function(){
                if ($('#choose_street_bloc')) {
                    $('#choose_street_bloc').remove();
                };

                CP = $(this).val();
                if (CP!=='') {
                    ajax('get_street');
                };
            });
        }

        function display_choose_street(streets){
        	var display = '<div id="choose_street_bloc">Choose a street:';
        	display += '<select id="choose_street">';
            display += '<option value=""></option>';
        	for (key in streets){
        		display += '<option value="'+streets[key]['nom']+'">'+streets[key]['nom']+'('+streets[key]['type_voie']+')'+'</option>';
        	}
        	display += "</select></div>";
        	$('#search_bar').append(display);

            $('#choose_street').change(function(){
                street = $(this).val();
                if (street!=='') {
                    ajax('get_buildings');
                };

            });
        }

        function display_choose_buildings(buildings){

            console.debug(buildings);
            var display = '<ul class="immeuble">';
            var display2 = '<ul class="immeuble">';
            for (var i = 0; i < buildings.length; i++) {
                if (buildings[i].num%2 == 0){
                    display2 += '<li class="buildings_num" data-buildings_num="'+i+'">'+buildings[i].num+' '+buildings[i].num2+'</li>';
                }
                else{
                    display += '<li class="buildings_num" data-buildings_num="'+i+'">'+buildings[i].num+' '+buildings[i].num2+'</li>';
                }
            };
            display += '</ul>';
            display2 += '</ul>';
            $('#buildings_line_1').append(display);
            $('#buildings_line_3').append(display2);

            $('.buildings_num').on('click', function(){
                // Par sécurité avant d'afficher un autre immeuble, on vérifie qu'il n'y a pas eu de modifs des data de l'immeuble déjà affiché
                if ($('#building_message').data("flag") == 1) {
                    if (confirm('Attention !\nDes modifications d\'infos sur l\'immeuble ont été détectées.\nVoulez-vous les enregistrer ?')) { 
                        save_building_data('store_server');
                    }
                };
                // On vérifie aussi qu'il n'y a pas eu de changements sur les habitants
                if (data_habitants_change == 1) {
                    if (confirm('Attention !\nDes modifications sur les habitants de l\'immeuble ont été détectées.\nVoulez-vous les enregistrer ?')) { 
                        save_habitants_data('habitants_save');
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
            });
        }

        function display_building_data(selected_building){
            $('#display_building_data').empty();

            building = data_street_nbrs[selected_building];
            street_nbr = building.im_id;

            display = '';
            display += '<div id="building_message" data-flag="0"></div>';
            display += '<br><span>Info immeuble :</span><br>';
            display += '<input style="display:none" class="building_form" name="building_id" type="text" value="'+building.im_id+'">';
            display += 'Gardien: <input class="building_form" name="building_gardien" type="text" value="'+building.gardiens+'"><br>';
            display += 'BAL: <input class="building_form" name="building_bal" type="text" value="'+building.bal+'"><br>';
            display += 'Code imm.: <input class="building_form" name="building_code" type="text" value="'+building.code+'"><br>';
            display += 'Infos: <textarea class="building_form" name="building_infos" rows=4 COLS=40>'+building.infos+'</textarea><br>';
            
            //display += '<hr><div id="display_pistes"><span>Piste(s) active(s): ???</span></div>';
            //display += '<hr><div id="display_habitants"><span>Habitants: (voir)</span></div>';

            $('#display_building_data').append(display);
            $('#display_building_data').data('selected_building',selected_building);
            //console.debug(building);
            $('.building_form').on('change', function(){
                if ($('input[name=save_building]').length == 0) {
                    button = '<input name="save_building" type="button" value="Enregistrer les modifications">';
                    $('#building_message').append(button);
                    $('input[name=save_building]').on('click', function(){
                        save_building_data('store_server');
                    });
                    $('#building_message').data("flag", "1");
                    $('#building_message').data("array_key", selected_building); // The key of data_street_nbrs to store changes after ajax success
                };
            });

            // Appel les fonctions habitants et Pistes

            display_habitants_data('is_habitants' ,building);
            //display_pistes_data();
        }

        function save_building_data(type, data){
            if (type=="store_server") {
                record = new Array();
                record['array_key'] = $('#building_message').data("array_key");
                record['im_id'] = $('input[name=building_id]').val();
                record['gardiens'] = $('input[name=building_gardien]').val();
                record['bal'] = $('input[name=building_bal]').val();
                record['code'] = $('input[name=building_code]').val();
                record['infos'] = $('textarea[name=building_infos]').val();
                //console.debug(record);
                ajax('save_building', record);
            }
            else if (type=="store_local") {
                //console.debug(data);
                data_street_nbrs[data.array_key] = data;
            };;
        }

        function display_habitants_data(type, data){
            
            if (type=="is_habitants") { // Informe sur le nbr d'habitants associé à l'immeuble
                $('#display_habitants').empty();   

                /*if (data_habitants){
                    if (data_habitants[0].im_id == street_nbr) {
                        display_habitants_data('display_habitants', data_habitants);
                        return;
                    };
                };*/

                display='';

                if (data.nbr_habitants > 0) {
                    display +='Il y a '+data.nbr_habitants+' habitants enregistrés ici.<br>';
                    display +='<input name="habitants_display" type="button" value="Voir">';
                    $('#display_habitants').append(display);
                    $('input[name=habitants_display]').click(function(){
                        ajax('get_habitants', data);
                    })
                }
                else{
                    display +='Aucun habitant enregistré ici.<br>';
                    display +='<input name="habitants_create" type="button" value="Créer un habitant">';
                    $('#display_habitants').append(display);
                };
            }
            else if(type == 'display_habitants'){
                $('#display_habitants').empty();
                data_habitants_change = 0 ;
                //console.debug(data);
                display = '<div id="habitants_message"></div>';
                display += '<div id="habitants_add"><input name="habitants_add" type="button" value="&nbsp;&nbsp;+&nbsp;&nbsp;"></div>';
                display += '<ul class="habitants" id="liste_habitants">';
                for( key in data){
                    display += '<li id="'+data[key].poids+'" class="habitants">'+data[key].nom+' '+data[key].prenom+'</li>';
                }
                display += '</ul>';
                $('#display_habitants').append(display);


                // Ajout d'un habitant
                $('input[name=habitants_add]').on('click', function(){
                    display_habitants_add();
                });

                // Drag n' Drop: modification du poids des habitants
                $(document).ready( function(){ // quand la page a fini de se charger
                    $("#liste_habitants").sortable({ 
                        placeholder: 'highlight', // classe à ajouter à l'élément fantome
                        update: function() {  // callback quand l'ordre de la liste est changé
                            /*var order = $('#liste_habitants').sortable('toArray'); // récupération des données à envoyer
                            sortable = [];
                            for( key in order){
                                sortable[key] = data_habitants[order[key]];
                                sortable[key].poids = key;
                            }
                            data_habitants = sortable;*/
                            save_habitants_data('habitants_change');
                            
                        }
                    });
                    $("#liste_habitants").disableSelection(); // on désactive la possibilité au navigateur de faire des sélections
                });


            };
        }

        function display_habitants_add(){
            
            display = '';
            display +='Nom: <input name="habitants_add_nom" type="text" value=""><br>';
            display +='Prénom: <input name="habitants_add_prenom" type="text" value=""><br>';
            display +='Genre: <select id="habitants_add_genre"><option value="0">M.</option><option value="1">Mme</option><option value="2">Famille</option></select><br>';
            display +='Tel: <input name="habitants_add_tel" type="text" value=""><br>';
            display +='Email: <input name="habitants_add_email" type="text" value=""><br>';
            display +='<input name="habitants_add_save" type="button" value="Enregistrer"><br>';

            // Affiche
            $('#lightbox_bg, #lightbox').show();
            $('#lightbox').empty().append(display);
            // Masque - Ferme
            $('#lightbox_bg').on('click', function(){
                $('#lightbox_bg, #lightbox').hide();
            })
            // Enregistre et ferme
            $('input[name=habitants_add_save]').on('click', function(){
                habitant_added = {};
                habitant_added.nom = $('input[name=habitants_add_nom]').val();
                habitant_added.prenom = $('input[name=habitants_add_prenom]').val();
                habitant_added.genre = $('#habitants_add_genre').val();
                habitant_added.tel = $('input[name=habitants_add_tel]').val();
                habitant_added.email = $('input[name=habitants_add_email]').val();
                habitant_added.im_id = street_nbr ;

                // On calcule le poids à donner en comptant le nbre d'éléments existant
                order = $('#liste_habitants').sortable('toArray');
                habitant_added.poids = order.length ;

                data_habitants.push(habitant_added);

                $('#lightbox_bg, #lightbox').hide();
                $('#lightbox').empty();
                console.debug(data_habitants);
                display_habitants_data('display_habitants', data_habitants);
                save_habitants_data('habitants_change');
            });
        }

        function save_habitants_data(type){
            if (type=='habitants_change') {
                data_habitants_change = 1 ;
                $('#habitants_message').append('<input name="habitants_save" type="button" value="Save">');
                $('input[name=habitants_save]').on('click', function(){
                    save_habitants_data('habitants_save');
                })
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
                //save_habitants_data('habitants_change');

                var serializedArr = JSON.stringify( data_habitants );
                console.debug(serializedArr);
                ajax('save_habitants_data', serializedArr);
                ajax('get_habitants', data_habitants[0].im_id);
            }
            else if(type=="habitants_success"){
                 $('#habitants_message').empty();
                 data_habitants_change = 0 ;
                 display_habitants_data('display_habitants', data_habitants);
                 alert('ok');
            };
        }

        function controller(){

            display_choose_CP();        	        	

        }

	</script>

</head>
<body>
	<style>
		#lightbox_bg{
            display: none;
            position: absolute;
            z-index: 10;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            background-color: grey;
            moz-opacity:0.8;
            opacity: 0.8;
            filter:alpha(opacity=80);
        }
        #lightbox{
            display: none;
            position: absolute;
            z-index: 20;
            width: 50%;
            height: 65%;
            left: 25%;
            top: 25%;
            background-color: whitesmoke;
            border: solid 16px rgba(255, 0, 0, .5);
        }
        #contents{
			width: 100%;
			height: 100%;
			background-color: blue;
			position: absolute;
			top: 0px;
			left: 0px;
		}
        #search_bar{
            height: 75px;
        }
        #buildings_nav{
            width: 100%;
            height: 150px;
            background-color: grey;
            overflow: hidden;
        }
        #buildings_line_1{
            border-bottom: solid 1px black;
            height: 50px;
            position: relative;
        }
        #buildings_line_2{
            height: 50px;
        }
        #buildings_line_3{
            border-top: solid 1px black;
            height: 50px;
            position: relative;
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
            background-color: red;
        }
        ul.immeuble { 
            display: inline-flex;
            padding-left: 0px;
            margin-top: 0px;
            
        }
        li.buildings_num {
            float:left;
            width: 50px;
            height: 50px;
            border: solid 1px black;
            text-align: center;
            list-style-type: none;
            line-height: 50px;
        }
        ul.habitants{

        }
        li.habitants {
            /*float:left;*/
            width: 500px;
            height: 30px;
            border: solid 1px black;
            text-align: center;
            list-style-type: none;
            line-height: 30px;
        }

	</style>
	 
	<div id="lightbox_bg"></div>
    <div id="lightbox"></div>

    <div id="contents">

		<div id="search_bar">
		</div>
        
        <div id="buildings_nav">
            <div id="buildings_line_1">
            </div>
            <div id="buildings_line_2">
                <div id="buildings_nav_left">Left</div>
                <div id="buildings_nav_right">Right</div>
            </div>
            <div id="buildings_line_3">
            </div>
        </div>
        
        <div id="display_building_data">
            ici s'affiche les infos !
        </div>

        <div id="display_habitants">
        
        </div>

	</div>


<script type="text/javascript">
	$(document).ready(function(){

		var screen = get_screen_size();
		controller();

	});
</script>

</body>
</html>

