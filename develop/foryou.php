<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php
   /* require user to be logged in */
   if (!isset($_SESSION["userid"]))
      echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";
?>
<html>
	<head>
		<?php include ("layout/title.html"); ?>
		<link rel="stylesheet" href="layout/style.css">
	</head>

	<body>
		<div id="page">
			<?php include ("layout/header.html"); ?>
			<?php include ("layout/nav.html"); ?>

             <div id="content">
                <h2>Wishlist of others</h2>
                <p> Not yet released...</p>
             </div>

			<?php include ("layout/footer.html"); ?>
		</div>
	</body>
</html>