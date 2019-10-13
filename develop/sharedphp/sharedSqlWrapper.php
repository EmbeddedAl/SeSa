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


function shareSqlWrapper_userCreate($username, $firstname, $lastname, $email, $passwordMD5)
{
    $returnValue = -1;

    /* open connection */
    $sqlConnection = sharedSqlWrapper_connect();
    if ($sqlConnection == null)
        return $returnValue;

    /* insert into users */
    $sqlStatement = "INSERT INTO users (username, firstname, lastname, email, password, isadmin) VALUES ("
            . "'" . $username . "', '" . $firstname . "', '" . $lastname . "', '" . $email . "', '" . $passwordMD5 . "', 0)";

    /* query the database */
    $sqlResult = $sqlConnection->query ($sqlStatement);
    if ($sqlResult != TRUE)
        goto end;

    $returnValue = 0;

end:
    sharedSqlWrapper_disconnect($sqlConnection);
    return $returnValue;
}

?>