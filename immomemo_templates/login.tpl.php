<?php
global $base_url;

?>


<!DOCTYPE html>
<html  lang="en" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
	<link rel="shortcut icon" href="<?= $base_url ?>/sites/default/files/1398135398_office-building.png" type="image/png" />
    <link rel="stylesheet" type="text/css" href="<?= $base_url ?>/sites/all/modules/immomemo/CSS/normalize.css">
    <link href='http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz' rel='stylesheet' type='text/css'>
	<script src="<?= $base_url ?>/sites/all/libraries/jquery-1.11.0.min.js"></script>
    <script src="<?= $base_url ?>/sites/all/libraries/jquery-ui-1.10.4.custom.min.js"></script>
</head>
<body>

	<div id="contents">
		<style>
			body{
				background-image: url('<?= $base_url ?>/sites/all/modules/immomemo/immomemo_templates/img/rayures_claires.png');
				background-repeat: repeat;
				background-color: #4A66E5;
				font-family: 'Yanone Kaffeesatz', sans-serif;
				text-align: center;
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
			#contents{
				padding: 20px;
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
		<div id="header_logo">	
			<img src="<?= $base_url ?>/sites/default/files/1398135398_office-building.png" style="width:80%;max-width: 100px;" >
			<br>
			<span style="font-size: xx-large;">Immomemo<br><br></span>
		</div>
	</div>

	<?php
		echo drupal_get_form('user_login');
	?>

	<script type="text/javascript">

		//Pour afficher le formulaire de login comme désiré, le jquery suivant nettoie la page puis rajoute et modifie le formulaire.
		$(document).ready(function(){

			$('.item-list, #edit-name--2, #edit-pass--2, .form-item').remove();

			var login_form = $('#user-login-form');
			var contents = $('#contents');
			var messages = $('.messages');
			var input_username = '<input type="text" id="edit-name--2" name="name" value="" placeholder="Username" size="15" maxlength="60" class="enjoy-css">';
			var input_password = '<input type="password" id="edit-pass--2" name="pass" size="15" maxlength="128" class="enjoy-css" placeholder="Password">';

			$('#skip-link').remove();
			$('#page').remove();
			$('body').append(contents);
			$('#contents').append(login_form);
			$('#user-login-form').children('div').prepend(input_username+'<br><br>'+input_password+'<br><br>');
			$('#edit-submit--2').removeClass('form-submit').addClass('button_submit').attr('value', 'Go !');

			$('#button_submit').on('click', function(){
				$('#user-login-form').submit();
			})

			if (messages) {
				$('#contents').prepend(messages);
			};
		});

	</script>
</body>
</html>