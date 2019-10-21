<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php

    include "config.php";
    include 'sharedphp/sharedSqlWrapper.php';

    /* require user to be logged in */
    if (!isset($_SESSION["userid"]))
        echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";

    /* require user to be admin */
    if (!isset($_SESSION["isadmin"]))
        echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";

    /* ToggleRegistration */
    $RegistrationActive = sharedSqlWrapper_isRegistrationActive();
    if (isset($_POST['toggleRegistration']))
    {
        if ($RegistrationActive == 0)
        {
            sharedSqlWrapper_dropMappingTable();
            sharedSqlWrapper_setRegistrationActive(1);
        } else if ($RegistrationActive == 1)
        {
            sharedSqlWrapper_setRegistrationActive(0);
        }

        $RegistrationActive = sharedSqlWrapper_isRegistrationActive();

    }

    /* Create mapping table */
    if (isset($_POST['createTable']))
    {
        sharedSqlWrapper_createMappingTable(2);
    }

    /* Randomize users in */
    if (isset($_POST['randomize']))
    {
        sharedSqlWrapper_randomizeUsersToMapping();
    }

    $MappingTableExists = sharedSqlWrapper_existsMappingTable();
    $_POST = array();
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

            <!--  Toggle the registration switch -->
            <form action="admin.php" method="post">
                <table border="1" >
                	<tr>

                		<td> Current RegistrationActiveSetting: <br> <?php echo $RegistrationActive; ?> </td>
                		<td> <input type="submit" name="toggleRegistration" value="Toggle" /></td>

                		<?php if ($RegistrationActive == 0) { ?>
                			<td> WARNING: Reenabling registration will drop the complete matching table </td>
                		<?php } else { ?>
                			<td>  </td>
                		<?php } ?>
                	</tr>
        	    </table>
            </form>

            <!--  If registration is off, show table create button -->
            <?php if ($RegistrationActive == 0) { ?>
            <form action="admin.php" method="post">
                <table border="1" >
                	<tr>
                		<td> Mapping table exists: <br> <?php echo $MappingTableExists; ?></td>
                		<td> <input type="submit" name="createTable" value="Create Mapping Table" /></td>
                		<td> </td>
                	</tr>
        	    </table>
      	  	</form>
				<?php if ($MappingTableExists == 1) { ?>
					<!--  Randomize users into mapping table -->
    	            <form action="admin.php" method="post">
                        <table border="1" >
                        	<tr>
                        		<td> </td>
                        		<td> <input type="submit" name="randomize" value="Randomize Users" /></td>
                        		<td> </td>
                        	</tr>
                	    </table>
              	  	</form>


					Show mapping table here
				<?php } ?>
            <?php } ?>





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