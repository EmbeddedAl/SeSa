<?php


function sharedSqlWrapper_connect()
{
    /* config is needed for connection to db */
    include 'config.php';

    /* open sql connection */
    $sqlConnection = new mysqli ( $dbhost, $dbuser, $dbpass, $dbase );
    if ($sqlConnection->connect_error)
        return null;

    return $sqlConnection;
}


function sharedSqlWrapper_disconnect($sqlConnection)
{
    $sqlConnection->close();
}

function sharedSqlWrapper_dropMappingTable()
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    $sqlStatement = "DROP TABLE IF EXISTS mapping";

    if ($sqlConnection->query( $sqlStatement ) != TRUE)
        goto end;

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}

function sharedSqlWrapper_existsMappingTable()
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check table already there */
    $sqlStatement = "SHOW TABLES LIKE 'mapping'";
    if ($sqlConnection->query( $sqlStatement ) != TRUE)
        goto end;

    /* if a row returns, table is there */
    if ($sqlConnection->affected_rows != 0)
        $returnValue = 1;
    else
        $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}

function sharesSqlWrapper_getNumberOfUsers()
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* get number of users */
    $sqlStatement = "SELECT * FROM users";
    if ($sqlConnection->query( $sqlStatement ) != TRUE)
        goto end;

    /* extract the number of users */
    $returnValue = $sqlConnection->affected_rows;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}

function sharedSqlWrapper_randomizeUsersToMapping()
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check table already there */
    if (sharedSqlWrapper_existsMappingTable() == 0)
        goto end;

    $numberUsers = sharesSqlWrapper_getNumberOfUsers();

    /* randomize the users to the mapping table - randomized approach */
    $PoolOfCompleted = [];
    for ($x = 1; $x <= $numberUsers; $x++)
    {
        do
        {
            /* find out who is next (circular approach)
             * take a random number between 1 and number of users
             */
            $next = rand(1, $numberUsers);

            /* if self is found, try again */
            if ($next == $x)
                continue;

        } while(in_array($next, $PoolOfCompleted));

        /* add 'next' to the pool of already used indexes */
        $PoolOfCompleted[] = $next;

        /* and insert it into database */
        $sqlStatement = "UPDATE mapping SET user_id ='" . $next . "' WHERE leaf_id = '". $x . "'";
        if ($sqlConnection->query( $sqlStatement ) != TRUE)
            goto end;
    }

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}
function sharedSqlWrapper_createMappingTable($numPresents)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    // TODO: Set in settings
    // TODO: Set in mapping table

    /* check table already there */
    if (sharedSqlWrapper_existsMappingTable() == 1)
        goto end;

    /* create table */
    $sqlStatement = "CREATE TABLE IF NOT EXISTS mapping (
                    leaf_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    receiveLeaf01 INT,
                    receiveLeaf02 INT
                    ) ENGINE=MyISAM DEFAULT CHARSET=latin1";

    if ($sqlConnection->query( $sqlStatement ) != TRUE)
        goto end;

    /* extract the number of users */
    $numberUsers = sharesSqlWrapper_getNumberOfUsers();

    /* the mapping table is a ram copy of what is in the sql database */
    $mappingTable;

    /* create as many mapping leafs as there are users */
    for ($thisMappingRow = 1; $thisMappingRow <= $numberUsers; $thisMappingRow++)
    {
        /* find out who is next (circular approach) */
        $nextRowNumber = ($thisMappingRow + 1);
        if ($nextRowNumber > $numberUsers)
            $nextRowNumber = 1;

        /* and insert it into database - hereby a new row will be added to mapping */
        $sqlStatement = "INSERT INTO mapping (receiveLeaf01) VALUES (" . $nextRowNumber . ")";
        if ($sqlConnection->query( $sqlStatement ) != TRUE)
            goto end;

        /* also store this information in the ram copy */
        $mappingTable[$thisMappingRow]['receiveLeaf01'] = $nextRowNumber;
    }

    /* fill the second receive leaf with a randomized approach */
    $PoolOfCompleted = [];
    for ($thisMappingRow = 1; $thisMappingRow <= $numberUsers; $thisMappingRow++)
    {
        $giveUpCounter = $numberUsers * 10;
        while(($giveUpCounter--) > 0)
        {
            /* ranodmize someone */
            $randomRow = rand(1, $numberUsers);

            /* if self is found, try again */
            if ($randomRow == $thisMappingRow)
                continue;

            /* if the random one is already connected, try again */
            if ($mappingTable[$thisMappingRow]['receiveLeaf01'] == $thisMappingRow)
                continue;

            /* if already in pool of completed, do it again */
            if (in_array($randomRow, $PoolOfCompleted))
                continue;

            break;
        }

        if ($giveUpCounter <= 0)
        {
            sharedSqlWrapper_dropMappingTable();
            $returnValue = -2;
            goto end;
        }

        /* add 'next' to the pool of already used indexes */
        $PoolOfCompleted[] = $randomRow;

        /* and insert it into database */
        $sqlStatement = "UPDATE mapping SET receiveLeaf02 ='" . $randomRow . "' WHERE leaf_id = '". $thisMappingRow . "'";
        if ($sqlConnection->query( $sqlStatement ) != TRUE)
            goto end;

        /* also store this information in the ram copy (even it is not used in the current implementation) */
        $mappingTable[$thisMappingRow]['receiveLeaf02'] = $randomRow;
    }

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}

function sharedSqlWrapper_getSettingRegistrationActive()
{
    $sqlValue = sharedSqlWrapper_getSetting("registrationActive");

    if ($sqlValue < 0)
        return $sqlValue;

    /* only values 0 and 1 are valid */
    if ($sqlValue == 0)
        return $sqlValue;
    if ($sqlValue == 1)
        return $sqlValue;

    return -1;
}
function sharedSqlWrapper_setSettingRegistrationActive($value)
{
    $dbValue = 0;

    if ($value == 1)
        $dbValue = 1;

    return sharedSqlWrapper_setSetting("registrationActive", $dbValue);
}

function sharedSqlWrapper_getSettingMappingReleasedToUsers()
{
    $sqlValue = sharedSqlWrapper_getSetting("mappingReleasedToUsers");

    if ($sqlValue < 0)
        return $sqlValue;

    /* only values 0 and 1 are valid */
    if ($sqlValue == 0)
        return $sqlValue;
    if ($sqlValue == 1)
        return $sqlValue;

    return -1;
}
function sharedSqlWrapper_setSettingMappingReleasedToUsers($value)
{
    $dbValue = 0;

    if ($value == 1)
        $dbValue = 1;

    return sharedSqlWrapper_setSetting("mappingReleasedToUsers", $dbValue);
}
function sharedSqlWrapper_getSetting($settingName)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check username/password with database */
    $sqlStatement = "SELECT * from `settings` WHERE `setting` = '" . $settingName . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    /* this is only exactly one line valid */
    if ($sqlResult->num_rows != 1)
        goto end;

    /* get the actual data base row */
    $sqlRow = $sqlResult->fetch_assoc();

    /* extract the value */
    $returnValue = $sqlRow['value'];

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}

function sharedSqlWrapper_setSetting($settingName, $settingValue)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* update into users */
        $sqlStatement = "UPDATE settings SET VALUE ='" . $settingValue . "' where setting = '". $settingName . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}


function sharedSqlWrapper_userExists($username)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check username/password with database */
    $sqlStatement = "SELECT * from `users` WHERE `username` = '"  . $username . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    /* if there is a result, the user exists */
    if ($sqlResult->num_rows != 0)
        /* user exists */
        $returnValue = 1;
    else
        /* user does not exits */
        $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}



function sharedSqlWrapper_getFirstName($userid)
{
    $returnValue = "undefined";

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check username/password with database */
    $sqlStatement = "SELECT * from `users` WHERE `userid` = '"  . $userid . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    /* there should be exactly one users */
    if ($sqlResult->num_rows == 1)
    {
        /* get the actual data base row */
        $sqlRow = $sqlResult->fetch_assoc();
        $returnValue = $sqlRow['firstname'];
    }

    end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}



function sharedSqlWrapper_getLastName($userid)
{
    $returnValue = "undefined";

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check username/password with database */
    $sqlStatement = "SELECT * from `users` WHERE `userid` = '"  . $userid . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    /* there should be exactly one users */
    if ($sqlResult->num_rows == 1)
    {
        /* get the actual data base row */
        $sqlRow = $sqlResult->fetch_assoc();
        $returnValue = $sqlRow['lastname'];
    }

    end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}


function sharedSqlWrapper_getUseridOfReceiveLeaf($userid, $leafNo)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    $ColumnName = "receiveLeaf0" . $leafNo;

    // get the leaf_id
    $sqlStatement = "SELECT $ColumnName from `mapping` WHERE `user_id` = '"  . $userid . "'";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    /* there should be exactly one entry */
    if ($sqlResult->num_rows == 1)
    {
        /* get the actual data base row */
        $sqlRow = $sqlResult->fetch_assoc();
        $leafid = $sqlRow[$ColumnName];

        // get the userid to that leafid
        $sqlStatement = "SELECT user_id from `mapping` WHERE `leaf_id` = '"  . $leafid . "'";

        /* query the database */
        $sqlResult = $sqlConnection->query ($sqlStatement);
        if ($sqlResult != TRUE)
            goto end;

        /* there should be exactly one entry */
        if ($sqlResult->num_rows == 1)
        {
            /* get the actual data base row */
            $sqlRow = $sqlResult->fetch_assoc();
            $returnValue = $sqlRow['user_id'];
        }
    }

    end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}




function shareSqlWrapper_userCreate($username, $firstname, $lastname, $email, $city, $passwordMD5)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* insert into users */
    $sqlStatement = "INSERT INTO users (username, firstname, lastname, email, city, password, isadmin) VALUES ("
            . "'" . $username . "', '" . $firstname . "', '" . $lastname . "', '" . $email . "', '" . $city ."', '" . $passwordMD5 . "', 0)";

    /* query the database */
    $sqlResult = $sqlConnection->query($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}


function shareSqlWrapper_updateUserPassword($userid, $passwordMD5)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* update into users */
    $sqlStatement = "UPDATE users SET password ='" . $passwordMD5 . "' where userid = " . $userid;

    /* query the database */
    $sqlResult = $sqlConnection->query($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}


?>