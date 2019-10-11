<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php
   /* always start session */
   session_start();

   /* require user to be logged in */
   if (!isset($_SESSION["userid"]))
      echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";

   /* require user to be admin */
   if (!isset($_SESSION["isadmin"]))
      echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";

   /* config is needed for connection to db */
   include "config.php";

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
            <h2>Admin Area</h2>
            <form action="admin.php">
               <?php
                  echo "UNIX TIMESTAMP:" . $ConfigTimestampPhase2 . "<br>";
                  echo "Date:  <input style=\"width:40px\" name=\"yr\" value=\"".date("Y", $ConfigTimestampPhase2)."\"/> ";
                  echo "       <input style=\"width:20px\" name=\"mo\" value=\"".date("m", $ConfigTimestampPhase2)."\"/> ";
                  echo "       <input style=\"width:20px\" name=\"da\" value=\"".date("d", $ConfigTimestampPhase2)."\"/><br>";
                  echo "Time:  <input style=\"width:20px\" name=\"hr\" value=\"".date("H", $ConfigTimestampPhase2)."\"/>:";
                  echo "       <input style=\"width:20px\" name=\"mi\" value=\"".date("i", $ConfigTimestampPhase2)."\"/><br>";
               ?>
            </form>
            <?php
                if ($ConfigPhase2Active == 1)
                   echo "SYSTEM IN PHASE 2";
               else
                   echo "SYSTEM IN PHASE 1";
            ?>
		  </div>

		<?php include ("layout/footer.html"); ?>
	</div>

	</body>
</html>