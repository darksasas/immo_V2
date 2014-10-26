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


    var streets = <?php echo json_encode($user_datas['streets']) ?>;

    var immeubles = <?php echo json_encode($user_datas['immeubles']) ?>;

    var habitants = <?php echo json_encode($user_datas['habitants']) ?>;

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


    </script>


</head>
<body>

    <style>
    </style>

    <div id="loader" style="display:none">
        <div id="loading_bg">&nbsp;</div><p id="loading_img"><img src="<?= $base_url ?>/sites/all/modules/symrise/img/processing.png" width="200px"></p>
    </div>

    <div id="popup_screens">
    </div>

    <div id="select_univers">
    </div>

    <div id="summary">
    </div>

    <div id="contents">
    	yop play
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
