<?php
global $base_url;

?>


<!DOCTYPE html>
<html  lang="en" dir="ltr">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<link rel="shortcut icon" href="<?= $base_url ?>/sites/default/files/1398135398_office-building.png" type="image/png" />
    <link rel="stylesheet" type="text/css" href="<?= $base_url ?>/sites/all/modules/immomemo/CSS/reset.css">
	<script src="<?= $base_url ?>/sites/all/libraries/jquery-1.11.0.min.js"></script>
    <script src="<?= $base_url ?>/sites/all/libraries/jquery-ui-1.10.4.custom.min.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>/sites/all/libraries/jquery.ui.touch-punch.min.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>/sites/all/libraries/hammer.js"></script>

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
        var data_habitants_change = 0 ;
		var ajax_result="";

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
			screen['larg'] = larg;
			screen['haut'] = haut;
			return screen
		}

		function loading(state){
			if (state=='on')
				$('#contents').prepend(loading_display);
			else $('#loading').remove();
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
              		loading('on');
              	}
              	else if (xmlhttp.readyState==4 && xmlhttp.status==200){
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
			if (type=='get_street'){
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
                var display = '<div id="header_choice_CP" class="select-style">';
                display += '<select id="choose_CP">';
                display += '<option value="" disabled selected>Choose a postal code</option>';
                for (key in data_CP){
                    display += '<option value="'+data_CP[key]+'">'+data_CP[key]+'</option>';
                }
                display += "</select></div><br><br>";
                $('#header_choice').append(display);
            }
            $('#choose_CP').change(function(){
            	if ($('#header_choice_street')) {
                    $('#header_choice_street').remove();
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
        		display += '<option value="'+streets[key]['nom']+'">'+streets[key]['nom']+'('+streets[key]['type_voie']+')'+'</option>';
        	}
        	display += "</select></div><br><br>";
        	$('#header_choice').append(display);

            $('#choose_street').change(function(){
                street = $(this).val();
                if (street!=='') {
                    ajax('get_buildings');
                };

            });
        }

        function display_choose_buildings(buildings){

            var display = '<ul id="immeuble" class="immeuble">';
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

            $('#data').css('height', screen['haut']-150).text('yopyop');
        	var buildings_nav = '<div id="buildings_nav"><div id="buildings_line_1"></div><div id="buildings_line_2"><div id="buildings_nav_left">Left</div><div id="buildings_nav_right">Right</div></div><div id="buildings_line_3"></div></div>';
        	$('#contents').append(buildings_nav);
        	$('#buildings_line_1').append(display);
            $('#buildings_line_3').append(display2);
            $('html, body').animate({
		        scrollTop: $("#data").offset().top
		    }, 2000);
		    
            var width= $('#buildings_line_1').width();
            var height = $('#buildings_line_1').height();

            /*$('.immeuble').draggable({
                axis : 'x',
                distance: 10,
                containement: [-width,0, width,50],
            });*/

			var element = document.getElementById('immeuble');
            //var element = $('#immeuble');

            Hammer(element).on("dragright", function(event) {
                $('#buildings_line_1, #buildings_line_3 ').css({
                        '-webkit-transform': 'translateX('+event.gesture.deltaX+'px)',
                    }, 0);
                //alert('yes');
                console.debug('drag');
            });

            /*$('.buildings_num').on('click', function(){
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
            });*/
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
		#data{
			position: relative;
			background-color: red;
		}
		


		/*Buildings*/
		 #buildings_nav{
            position: relative;
			bottom: 0px;
			left: 0px;
			width: 100%;
			height: 150px;
			background-color: grey;
			overflow: hidden;
        }
        #buildings_line_1{
            border-bottom: solid 1px black;
            height: 50px;
            position: relative;
            left: 0px;
            /*overflow: hidden;*/
            /*transform(translateX(150px,0,0));*/
        }
        #buildings_line_2{
            height: 50px;
        }
        #buildings_line_3{
            border-top: solid 1px black;
            height: 50px;
            position: relative;
            /*overflow: hidden;*/
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
            /*position: relative;
            display: inline-block;*/
            float:left;
            width: 50px;
            height: 50px;
            border: solid 1px black;
            text-align: center;
            list-style-type: none;
            line-height: 50px;
        }





		/*Loading animation*/
		
		*,
		*:before,
		*:after {
		    box-sizing: border-box;
		}
		#loading{
			position: absolute;
            z-index: 10;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
		}
		#loading_bg{
			position: absolute;
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



	<div id="contents">
		
		<div id="header_logo">	
			<img src="<?= $base_url ?>/sites/default/files/1398135398_office-building.png" style="width:80%;max-width: 100px;" >
			<br>
			<span style="font-size: xx-large;">Immomemo<br><br></span>
		</div>
		

		<div id="header_choice">

		</div>

		<div id="data">

		</div>

	</div>


	<script type="text/javascript">
		$(document).ready(function(){

			
			display_choose_CP();
			var screen = get_screen_size();
			//alert(screen['haut']+'X'+screen['larg']);

		});
	</script>

</body>
</html>