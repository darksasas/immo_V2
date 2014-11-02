<?php
	global $base_url;

?>


<!DOCTYPE html>

<html>
<!--<html manifest="/sites/all/modules/symrise/cache2.appcache">-->

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">

    <link rel="stylesheet" type="text/css" href="<?= $base_url ?>/sites/all/libraries/CSS/reset.css">

    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700|Open+Sans+Condensed:300' rel='stylesheet' type='text/css'>


    <script type="text/javascript"  charset="UTF-8">
    
    // User choices
    var user_CP;
    var user_street;


    // Control Data
    var hostname = '<?= $base_url ?>';
    var screen_size;
    var online;
    var animation_speed = 400; // milisecondes
    var nav_1 = 0 ;
    var nav_2 = 0 ;

    var type_voie = ['','rue','boulevard','place','impasse','quai'];


    var streets = <?php echo json_encode($user_datas['streets']) ?>;

    var immeubles = <?php echo json_encode($user_datas['immeubles']) ?>;

    var habitants = <?php echo json_encode($user_datas['habitants']) ?>;

    var user_timestamp = <?php echo ($user_datas['user_timestamp']) ?>;

    var changes = {};



    // Functions

    function get_screen_size(){
            
        screen_size = [];
        /*if (document.body){
            var larg = (document.getElementById('contents').clientWidth);
            var haut = (document.getElementById('contents').clientHeight);
        } 
        else{
            var larg = (window.innerWidth);
            var haut = (window.innerHeight);
        }*/
        var larg = $(window).width();
        var haut = $(window).height();
        // Si on veut la taille de l'écran : utiliser screen.height           
        screen_size['larg'] = larg;
        screen_size['haut'] = haut;
        return screen_size
    }

    function isonline(){
        if (!navigator.onLine)
            return false;
        return true;
    }

    function data_storage(type){

        if (type='store') {
            localStorage.setItem('streets',JSON.stringify(streets));
            localStorage.setItem('immeubles',JSON.stringify(immeubles));
            localStorage.setItem('habitants',JSON.stringify(habitants));
            //localStorage.setItem('user_timestamp', Math.round(new Date().getTime() / 1000));
            localStorage.setItem('user_timestamp', user_timestamp);
        }
        else if (type='load') {
            streets = JSON.parse(localStorage.getItem("streets"));
            immeubles = JSON.parse(localStorage.getItem("immeubles"));
            habitants = JSON.parse(localStorage.getItem("habitants"));
        }
        else if (type=="reset") {
            localStorage.clear();
            //$('body').empty();
            //location.reload();
        };
    }

    function set_webapp(){

        if (!online) { 
            popup('offline_stop');
            return;
        };

        // On check le timestamp
        if (localStorage.getItem('user_timestamp')=== null) { // 1er chargement de l'app
            data_storage('store');
            select_CP();
            // Pas besoin de vérification du timestamp
        }
        else{
            check_timestamp('start_webapp');
            select_CP();
        }
    }

    function ajax(type, data){
            
        if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        } 
        else{// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==1) {
            }
            else if (xmlhttp.readyState==4 && xmlhttp.status==200){
                ajax_result = xmlhttp.responseText;
                if (type=='request_access_timestamp') {
                    if (ajax_result!='DENIED') {
                        localStorage.setItem('access_timestamp', ajax_result);
                        set_webapp('check_products_timestamp');
                    }
                    else{
                        popup('user_denied');
                    }  
                }
                else if (type=="check_products_timestamp"){
                    if (ajax_result == 'OK') {
                        prod_storage('load');
                    }
                    else if (ajax_result=='DENIED') {
                        popup('user_denied');
                    }
                    else{
                        prod_storage('update', ajax_result);
                    }
                } 
            }
        }
        if(type=='request_access_timestamp'){
            xmlhttp.open("POST","<?= $base_url ?>/webapp/ajax/request_access_timestamp",true);
            xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            xmlhttp.send("user_id="+user_id);
        }
        else if (type=='check_products_timestamp'){
            xmlhttp.open("POST","<?= $base_url ?>/webapp/ajax/check_products_timestamp",true);
            xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            xmlhttp.send("user_id="+user_id+"&timestamp="+data);
        }
        else if (type=='sendFavs'){
            xmlhttp.open("POST","<?= $base_url ?>/webapp/ajax/sendFavs",true);
            xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            xmlhttp.send("user_id="+user_id+"&email="+data['email']+"&favs="+(data['type']=='all'? JSON.stringify(favs) : data['type']));
        }
    }

    function popup(type){
        
        ('#popup_screen').empty();

        if (type='offline_stop') {
            $('#popup_screen').append('Attention, vous devez être connecté pour lancer l\'application !');
        };
    }

    function check_timestamp(type){
        
        if (type='start_webapp') {
            $.ajax({
                url: hostname+"/immomemo/ajax/timestamp",
                type: "POST",
                async: false, // Mode synchrone
                data: ({
                    user_timestamp: localStorage.getItem("user_timestamp"),
                    //format: $("#format").val()
                }),
                dataType: "json",
                complete: function(data){
                    //console.log('check_timestamp: '+data.responseText);
                    console.log(data);

                    // En fonction de la réponse
                    if (data.responseText == 'SERVER_OLD')
                        check_timestamp('SERVER_OLD');
                    else if (data.responseText == 'NO_CHANGE') {}
                        // Do nothing
                    else { 
                        // On enregistre les données envoyées par le serveur
                        streets = data.responseJSON.streets;
                        immeubles = data.responseJSON.immeubles;
                        habitants = data.responseJSON.habitants;
                        user_timestamp = data.responseJSON.user_timestamp;
                        data_storage('store');
                    }
                }
            });
        }
        else if (type='SERVER_OLD') {
            // On upload les données (ou les changements ?) de la webapp vers le webserver
        };
    }

    function select_CP(){
        display = '<div id="div_choose_CP" class="select-style">';
        display += '<select id="choose_CP">';
        display += '<option value="" disabled selected>Choose a postal code</option>';
        // On parcour streets à la recherche des CP uniques
        var CP = new Array();
        for (key in streets){
            if (CP.indexOf(streets[key][2]) < 0 ) {
                CP.push(streets[key][2]);
            };
        }
        for (key in CP){
            display += '<option value="'+CP[key]+'">'+CP[key]+'</option>';
        }
        display += "</select></div>";
        $('#select_screen').append(display);

        $('#choose_CP').on('change', function(){
            if ($('#div_choose_street')) {
                $('#div_choose_street').remove();
            }
            if ($('#view_result')) {
                $('#view_result').remove();
            };
            user_CP = $(this).val();
            select_street(user_CP);
        });
    }

    function select_street(user_CP){
        display = '<div id="div_choose_street" class="select-style">';
        display += '<select id="choose_street">';
        display += '<option value="" disabled selected>Choose a street</option>';
        for (key in streets){
            if (streets[key][2] == user_CP) {
                display += '<option value="'+key+'">'+streets[key][1]+' ('+type_voie[streets[key][0]]+')'+'</option>';
            };
        }
        display += "</select></div>";
        $('#select_screen').append(display);

        $('#choose_street').change(function(){
            user_street = $(this).val();
            if ($('#view_result')) {
                $('#view_result').remove();
            };
            display = '<div id="view_result" class="buttonstyle white">View results</div>';
            $('#select_screen').append(display);

            $('#view_result').on('click', function(){
                $('#select_screen').animate({
                    top: 100+'%'
                }, animation_speed);
                select_building(user_street);
            });
        })
    }

    function select_building(user_street){

            display_tips = '<div id="tips" style="text-align:left;padding:5px;"><br><strong>Conseils d\'utilisation :</strong><br><br>- Enregistrez régulièrement, au fur et à mesure, vos modifications.<br><br>- Appuyez plusieurs fois rapidement sur les flèches pour naviguer plus rapidement parmis les n° d\'immeubles.<br><br>- Pour changer de rue ou d\'arondissement, appuyer sur le nom de la rue en bas.</div>';
            $('#street_screen').html(display_tips);

            var display = '<ul id="immeuble" class="immeuble">';
            var display2 = '<ul class="immeuble">';
            for (key in immeubles) {
                if (immeubles[key][0] == user_street) {
                    if (immeubles[key][1]%2 == 0){
                        display2 += '<li class="buildings_num" data-buildings_num="'+key+'">'+immeubles[key][1]+' '+immeubles[key][2]+'</li>';
                        nav_2 += 1 ;
                    }
                    else{
                        display += '<li class="buildings_num" data-buildings_num="'+key+'">'+immeubles[key][1]+' '+immeubles[key][2]+'</li>';
                        nav_1 += 1 ;
                    }
                };
            };

            display += '</ul>';
            display2 += '</ul>';         
            var buildings_nav = '';
            buildings_nav += '<div id="buildings_nav"><div id="buildings_line_1" style="left:0px;"></div>';
            buildings_nav += '<div id="buildings_line_2"><div id="buildings_nav_left"><img width="24" height="15" title="" alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAPCAYAAAD+pA/bAAAABmJLR0QA/wD/AP+gvaeTAAAA3UlEQVQ4jb3UIUtDURiA4UdBsAgOoyizL64uGGU/wnajdf9A/AtWo0mwjJkN2sxWXVXR4Bh4DfPKZR53vjvQL7+c5x7OuYfYtHGJ62C/jQvc5sJVHOENJSaBxQ/x9NWXi8Iu7mphDtjFaK5PAus4wTQRp4AVFHhJ9D+Afdz/EqaAHQwX9N/ABk7xkYnrQIHnTF9CL/PV88AWzoN9CeMG8QTHDfr/2QGsYYD3BmdwgIcoUE0HN0EANs0uRxhg9vcWeA0A1fTxGAWq2cNVEIAWzpoA1W7+7C2qT9uSr+knWUnH5X/sO18AAAAASUVORK5CYII=" /></div>';
            buildings_nav += '<div id="buildings_nav_name"></div>';
            buildings_nav += '<div id="buildings_nav_right"><img width="24" height="15" title="" alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAPCAYAAAD+pA/bAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAGYktHRAD/AP8A/6C9p5MAAAD6SURBVDhPY2BgYDgFxBuAWBqIiQFHgYo2A7ECMYpBav5D8XsgHU+Epp9Q9V+AdB4QMxHSA7MARi8CahDEowlmAUz9bqBaRXyWoFsA4j8FYm8cmtAtAKn/DMRpuHyDzQKY2EygJgE0i7BZAFN/EqhWB91h+CwAyT0BYk8kTfgsAKn/AcTlQMwK00PIApj8KqAGYSAmZAFM/W2gWluQJcRaAFLXRoIFIPXPSLGAZj74AE0hsCAlFET/gApBiYOXmDjYAVQkS0IqAoW7IzGp6CPU1YzoinHEwW+geAcQc2BRjxHJu4CK5LAphIqhB9FFoLgJHvW0L4toWpoCALWox+UWixZPAAAAAElFTkSuQmCC" /></div></div>';
            buildings_nav += '<div id="buildings_line_3" style="left:0px;"></div></div>';
           
            $('#street_screen').append(buildings_nav);
            //$('#buildings_nav_name').html('<img id="button_print" src="<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/print.png" style="width:80%;max-width: 32px;float: left;margin-left: 5px;margin-top: 9px;"><span id="street_name">'+street+'</span>');
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

    </script>


</head>
<body>

    <style>
        
    /*SCREENS*/
        #contents{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            /*z-index: 90;*/
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
        }
        #popup_screen{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            /*z-index: 90;*/
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
        }
        #select_screen{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            z-index: 10;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;

            background-color: blue;
        }
        #street_screen{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
        }
        #habitants_screen{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
        }
        #edit_screen{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
        }

    /*DIVERS*/

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

    /*BUILDINGS*/
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


    </style>

    <div id="contents">
        <div id="loader" style="display:none">
        </div>

        <div id="popup_screen">
        </div>

        <div id="select_screen">
        </div>

        <div id="street_screen">
        </div>

        <div id="habitants_screen">
        </div>

        <div id="edit_screen">
        </div>
    </div>

	<script type="text/javascript" src="<?= $base_url ?>/sites/all/libraries/JS/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>/sites/all/libraries/JS/jquery-ui-1.10.4.custom.min.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>/sites/all/libraries/JS/hammer.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>/sites/all/libraries/JS/jquery.ui.touch-punch.min.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>/sites/all/libraries/JS/bookmark_bubble.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>/sites/all/libraries/JS/bookmark_bubble_msg_fr.js"></script>

    <script type="text/javascript">
        $(document).ready(function(){

        	//localStorage.clear();

            screen_size = get_screen_size();
            online = isonline();

            set_webapp();

            //**************************
            // Buble "Add to Homescreen"
            //**************************
            google.bookmarkbubble.Bubble.prototype.NUMBER_OF_TIMES_TO_DISMISS=3;
            google.bookmarkbubble.Bubble.prototype.TIME_UNTIL_AUTO_DESTRUCT = 15000;

            window.addEventListener('load', function() {
                window.setTimeout(function() {
                    var bubble = new google.bookmarkbubble.Bubble();
                    var parameter = 'bmb=1';

                    bubble.hasHashParameter = function() {
                        return window.location.hash.indexOf(parameter) != -1;
                    };
                    bubble.setHashParameter = function() {
                        if (!this.hasHashParameter()) {
                            window.location.hash += parameter;
                        }
                    };
                    bubble.getViewportHeight = function() {
                        window.console.log('Example of how to override getViewportHeight.');
                        return window.innerHeight;
                    };
                    bubble.getViewportScrollY = function() {
                        window.console.log('Example of how to override getViewportScrollY.');
                        return window.pageYOffset;
                    };
                    bubble.registerScrollHandler = function(handler) {
                        window.console.log('Example of how to override registerScrollHandler.');
                        window.addEventListener('scroll', handler, false);
                    };
                    bubble.deregisterScrollHandler = function(handler) {
                        window.console.log('Example of how to override deregisterScrollHandler.');
                        window.removeEventListener('scroll', handler, false);
                    };
                    bubble.showIfAllowed();
                }, 1000);
            }, false);

        });
    </script>
    
</body>
</html>
