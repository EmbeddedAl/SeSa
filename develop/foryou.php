<?php session_start(); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">

<?php
    /* require user to be logged in */
    if (!isset($_SESSION["userid"]))
        echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\">";

    include "config.php";
    include 'sharedphp/sharedHelpers.php';
    include 'sharedphp/sharedSqlWrapper.php';

    $SettingMappingReleasedToUsers = sharedSqlWrapper_getSettingMappingReleasedToUsers();
    if ($SettingMappingReleasedToUsers != 0)
    {
        if (!isset($_SESSION["partner1Userid"]))
        {
            $_SESSION["partner1Userid"] = sharedSqlWrapper_getUseridOfReceiveLeaf($_SESSION["userid"], 1);
            $_SESSION["partner1FirstName"] = sharedSqlWrapper_getFirstName($_SESSION["partner1Userid"]);
            $_SESSION["partner1LastName"] = sharedSqlWrapper_getLastName($_SESSION["partner1Userid"]);

        }
        if (!isset($_SESSION["partner2Userid"]))
        {
            $_SESSION["partner2Userid"] = sharedSqlWrapper_getUseridOfReceiveLeaf($_SESSION["userid"], 2);
            $_SESSION["partner2FirstName"] = sharedSqlWrapper_getFirstName($_SESSION["partner2Userid"]);
            $_SESSION["partner2LastName"] = sharedSqlWrapper_getLastName($_SESSION["partner2Userid"]);
        }

        if ($_SESSION["partner1Userid"] <= 0 || $_SESSION["partner1Userid"] <= 0)
        {
            /* destroy session */
            session_destroy();
            $_SESSION = array();
            return;
        }

        /* read the list of relevant presents from the database */
        $partner1FreePresentsList = sharedSqlWrapper_getListOfFreePresentsOfUser($_SESSION["partner1Userid"]);
        $partner1LockedPresentsList = sharedSqlWrapper_getListOfPresentsOfUserALockedByUserB($_SESSION["partner1Userid"], $_SESSION["userid"]);
        $partner2FreePresentsList = sharedSqlWrapper_getListOfFreePresentsOfUser($_SESSION["partner2Userid"]);
        $partner2LockedPresentsList = sharedSqlWrapper_getListOfPresentsOfUserALockedByUserB($_SESSION["partner2Userid"], $_SESSION["userid"]);

        /* user hit the update button */
        if (isset($_POST["update"]))
        {
            /* get all 'free' presents (ids) that are relevant for this user (currently in database) */
            $AllFreePresentIdsInDatabase = array_merge($partner1FreePresentsList,  $partner2FreePresentsList);

            /* get all presents (ids) that are currently 'locked' by this user (currently in database) */
            $AllLockedPresentIdsInDatabase = array_merge($partner1LockedPresentsList,  $partner2LockedPresentsList);

            /* Create an array of all presents that are currently locked by the user on the web site (_POST) */
            $AllLockedPresentIdsInPOST = Array();
            foreach($_POST as $PostKey => $PostValue)
            {
                /* build an easy to iterate Array of all Present Ids checked */
                if (startsWith($PostKey, "CheckedId"))
                {
                    $AllLockedPresentIdsInPOST[] = $PostValue;
                }
            }

            /* check if we need to lock a present that was 'free' before (user ticked a new present) */
            foreach($AllLockedPresentIdsInPOST as $ThisLockedItemInPost)
            {
                /* if the present to be locked is in the list of free entries, lock it now! */
                if (in_array($ThisLockedItemInPost, $AllFreePresentIdsInDatabase))
                {
                    sharedSqlWrapper_lockPresentToUser($ThisLockedItemInPost, $_SESSION["userid"]);
                }
            }

            /* check if we need to unlock (=to free) a present that was locked before (user unticked a present that was ticked) */
            foreach($AllLockedPresentIdsInDatabase as $ThisLockedItemInDatabase)
            {
                /* if this present that is locked in the database is no longer part of the post, unlock it! */
                if (!in_array($ThisLockedItemInDatabase, $AllLockedPresentIdsInPOST))
                {
                    sharedSqlWrapper_lockPresentToUser($ThisLockedItemInDatabase, "NULL");
                }
            }

            /* since database updates might have been taken place, load the data again */
            $partner1FreePresentsList = sharedSqlWrapper_getListOfFreePresentsOfUser($_SESSION["partner1Userid"]);
            $partner1LockedPresentsList = sharedSqlWrapper_getListOfPresentsOfUserALockedByUserB($_SESSION["partner1Userid"], $_SESSION["userid"]);
            $partner2FreePresentsList = sharedSqlWrapper_getListOfFreePresentsOfUser($_SESSION["partner2Userid"]);
            $partner2LockedPresentsList = sharedSqlWrapper_getListOfPresentsOfUserALockedByUserB($_SESSION["partner2Userid"], $_SESSION["userid"]);
        }
    }

    function printPresentTableForPartner($PartnerFirstName, $PartnerLastName, $PartnerUserId, $partnerFreePresentsList, $partnerLockedPresentsList)
{
    echo "<table border=\"1\">";
    echo "<tr> <td width=\"490\" align=\"center\" colspan=\"4\"> <b>" . $PartnerFirstName . " " . $PartnerLastName . "</b> </td> </tr>";
    echo "<tr> <td width=\"490\" align=\"center\" colspan=\"4\"> <img src=" . sharedHelpers_getUserImageFile($PartnerUserId) . " border=\"4\"> </td> </tr>";
    echo "<tr>";
    echo "<th width=\"10\">#</th>";
    echo "<th width=\"150\">Name</th>";
    echo "<th width=\"280\">Description</th>";
    echo "<th width=\"50\">I buy it</th>";
    echo "</tr>";
    foreach ($partnerFreePresentsList as $presentId)
    {
        $PresentInformation = sharedSqlWrapper_getPresentInformation($presentId);
        echo "<tr>";
        echo "<td>" . $presentId . "</td>";
        echo "<td>" . $PresentInformation['pname'] . "</td>";
        echo "<td>" . $PresentInformation['pdesc'] . "</td>";
        echo "<td align=\"center\">" . "<input type=\"checkbox\" name=\"CheckedId".$presentId."\" value=\"".$presentId."\">" . "</td>";
        echo "</tr>";
    }
    foreach ($partnerLockedPresentsList as $presentId)
    {
        $PresentInformation = sharedSqlWrapper_getPresentInformation($presentId);
        echo "<tr>";
        echo "<td>" . $presentId . "</td>";
        echo "<td>" . $PresentInformation['pname'] . "</td>";
        echo "<td>" . $PresentInformation['pdesc'] . "</td>";
        echo "<td align=\"center\">" . "<input type=\"checkbox\" checked=\"yes\" name=\"CheckedId".$presentId."\" value=\"".$presentId."\">" . "</td>";
        echo "</tr>";
    }
    echo "</table>";
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
                   <h2>Wishlist of others</h2>
                    <?php
                        if ($SettingMappingReleasedToUsers != 0)
                        {
                            echo "<form action=\"foryou.php\" method=\"post\">";

                            printPresentTableForPartner($_SESSION["partner1FirstName"], $_SESSION["partner1LastName"], $_SESSION['partner1Userid'], $partner1FreePresentsList, $partner1LockedPresentsList);

                            echo "<p> </p>";
                            echo "<hr />";
                            echo "<p> </p>";

                            printPresentTableForPartner($_SESSION["partner2FirstName"], $_SESSION["partner2LastName"], $_SESSION['partner2Userid'], $partner2FreePresentsList, $partner2LockedPresentsList);

                            echo "<p align=\"right\">";
                            echo "<input  type=\"submit\" name=\"update\" value=\"update\" />";
                            echo "</p>";

                            echo "</form>";
                        }
                        else
                        {
                            echo "<p> Not yet released...</p>";
                        }
                    ?>
            </div>
            <?php include ("layout/footer.html"); ?>
        </div>

    </body>
</html>