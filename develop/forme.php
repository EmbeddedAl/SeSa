<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php
   /* always start session */
   session_start();

   /* require user to be logged in */
   if (!isset($_SESSION["userid"]))
      echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";

   /* config is needed for connection to db */
   include "config.php";

   /* open sql connection */
   $sqlConnection = new mysqli ( $dbhost, $dbuser, $dbpass, $dbase );
   if ($sqlConnection->connect_error)
       return;

   /* add item to database */
   if (isset($_POST['add']))
   {
      if ($_POST['newname'] != "" &&
          $_POST['newdesc'] != "")
      {
         $sqlStatement = "INSERT INTO presents (pname, pdesc, puserid) VALUES (" .
                  "'" . $_POST['newname'] . "', " .
                  "'" . $_POST['newdesc'] . "', " .
                  "'" . $_SESSION['userid'] . "')";

         /* query the database */
         $result = $sqlConnection->query ( $sqlStatement );
         if ($result != TRUE)
             return;

      }
      $_POST = array();
   }

   /* delete on request */
   foreach($_POST as $k => $v)
   {
      if (substr($k,0,3) == 'del')
      {
         $delid = substr($k, 3);
         $sqlStatement = "DELETE FROM presents where pid = " . $delid . " AND puserid = " . $_SESSION['userid'];

         /* query the database */
         $sqlConnection->query ( $sqlStatement );

         $_POST = array();
      }
   }

   /* fetch all data from database */
   $sqlStatement = "SELECT * from `presents` where puserid =" . $_SESSION["userid"];

   /* query the database */
   $result = $sqlConnection->query ( $sqlStatement );
   if ($result != TRUE)
       return;

   $databuf = array();
   while(($DatabaseRow = $result->fetch_assoc()) != false)
       $databuf[] = $DatabaseRow;

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
            <h2>My wishlist</h2>

            <p>
            Add all presents on to your wishlist.
            At any time you can add new itmes to the list.
            In SeSa phase 1 you can also remove items from the list again.
            In SeSa phase 2 this list will be seen by your secret santas.
            The santas will then go out and get presents for you.
            Starting from phase 2 deletion of items is not possible anymore. </p>
            <p> <b>Example:</b> </p>
            <p> <u>Name: </u>Foo Fighters - Everlong </p>
            <p> <u>Description: </u>The new CD from the Foo fighters</p>

            <form action="forme.php" method="post">

               <table border="1" >
                  <tr>
                     <th width="10">#</th>
                     <th width="150">Name</th>
                     <th width="280">Description</th>
                     <th width="50"></th>
                  </tr>
                  <tr>
                     <td></td>
                     <td><input name="newname" /></td>
                     <td><input name="newdesc" /></td>
                     <td><input type="submit" name="add" value="add" /></td>
                  </tr>
                  <?php
                     for ($i = 0; $i < count($databuf); $i++)
                     {
                        echo "<tr>";
                        echo "<td>" . ($i+1) . "</td>";
                        echo "<td>" . $databuf[$i]['pname'] . "</td>";
                        echo "<td>" . $databuf[$i]['pdesc'] . "</td>";
                        if ($ConfigPhase2Active != 0)
                           echo "<td></td>";
                        else
                           echo "<td>" . "<input type=\"submit\" value=\"delete\" name=\"del" . $databuf[$i]['pid'] ."\"" . "</td>";
                        echo "</td>";

                        echo "</tr>";
                     }
                  ?>
               </table>
            </form>
		  </div>

		<?php include ("layout/footer.html"); ?>
	</div>

	</body>
</html>