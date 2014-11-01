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



    // Control Data
    var hostname = '<?= $base_url ?>';
    var screen_size;
    var online;


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
            // Pas besoin de vérification du timestamp
        }
        else{
            check_timestamp('start_webapp');
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

    </script>


</head>
<body>

    <style>
        
        /*********/
        /*Screens*/
        /*********/
        #popup_screen{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            z-index: 90;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
        }
        #select_screen{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            z-index: 90;
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
            z-index: 90;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
        }
        #habitants_screen{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            z-index: 90;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
        }
        #edit_screen{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            z-index: 90;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
        }


    </style>

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
