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
            /* find out who is next (circular approach) */
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
    // TODO: Set in mapping gable

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

    /* create as many mapping leafs as there are users */
    for ($x = 1; $x <= $numberUsers; $x++)
    {
        /* find out who is next (circular approach) */
        $next = ($x+1);
        if ($next > $numberUsers)
            $next = 1;

        /* and insert it into database */
        $sqlStatement = "INSERT INTO mapping (receiveLeaf01) VALUES (".$next.")";
        if ($sqlConnection->query( $sqlStatement ) != TRUE)
            goto end;
    }

    /* randomize the second receive leaf with an randomized approach */
    $PoolOfCompleted = [];
    for ($x = 1; $x <= $numberUsers; $x++)
    {
        while(1)
        {
            /* find out who is next (circular approach) */
            $next = rand(1, $numberUsers);

            /* if self is found, try again */
            if ($next == $x)
                continue;

            /* if already in pool of completed, do it again */
            if (in_array($next, $PoolOfCompleted))
                continue;

            break;
        }

        /* add 'next' to the pool of already used indexes */
        $PoolOfCompleted[] = $next;

        /* and insert it into database */
        $sqlStatement = "UPDATE mapping SET receiveLeaf02 ='" . $next . "' WHERE leaf_id = '". $x . "'";
        if ($sqlConnection->query( $sqlStatement ) != TRUE)
            goto end;
    }

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}

function sharedSqlWrapper_isRegistrationActive()
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* check username/password with database */
    $sqlStatement = "SELECT * from `settings` WHERE `setting` = 'registrationActive'";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    /* this is only exactly one line valid */
    if ($sqlResult->num_rows != 1)
        goto end;

    /* get the actual data base row */
    $sqlRow = $sqlResult->fetch_assoc();

    /* only values 0 and 1 are valid */
    if ($sqlRow['value'] == 0)
        $returnValue = 0;
    else if ($sqlRow['value'] == 1)
        $returnValue = 1;
    else
        $returnValue = -1;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}

function sharedSqlWrapper_setRegistrationActive($value)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* update into users */
    $sqlStatement = "UPDATE settings SET VALUE ='" . $value . "' where setting = 'registrationActive'";

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