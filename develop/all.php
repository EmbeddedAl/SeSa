<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php
   /* require user to be logged in */
   if (!isset($_SESSION["userid"]))
      echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";

   /* config is needed for connection to db */
   include "config.php";
   include "sharedphp/sharedHelpers.php";

   /* open sql connection */
   $sqlConnection = new mysqli ( $dbhost, $dbuser, $dbpass, $dbase );
   if ($sqlConnection->connect_error)
       return;

   /* check username/password with database */
   $sqlStatement = "SELECT userid, username, firstname, lastname FROM `users`";

   /* query the database */
   $result = $sqlConnection->query ( $sqlStatement );
   if ($result != TRUE)
       return;

   /* fetch the result from database */
   while(($userFromDatabase = $result->fetch_assoc()) != false)
   {
       $users[$userFromDatabase['userid']] = $userFromDatabase;
       $imgFileName = sharedHelpers_getUserImageFile($userFromDatabase['userid']);
       $imgs[$userFromDatabase['userid']] = $link[$userFromDatabase['userid']]  = "<img src=\"" . $imgFileName . "\" border=\"4\" alt=\"" . $userFromDatabase['firstname'] . "\">  ";
   }
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
            <h2>Who is participating?</h2>
            <p>Here is a list of all users currently registered in SeSa</p>

            <table border="0" >
               <?php
                  $counter = 2;
                  foreach ($users as $row)
                  {
                     if ($counter % 2 == 0)
                        echo "<tr>";

                     echo "<td align=\"center\">" . $imgs[$row['userid']] . "</br>";
                     echo          $row['username'] . " (" . $row['firstname'] . " " . $row['lastname'] . ")</p></td>";

                     if ($counter % 2 == 1)
                        echo "</tr>";

                     $counter++;
                  }
               ?>
            </table>
		  </div>

		<?php include ("layout/footer.html"); ?>
	</div>

	</body>
</html>