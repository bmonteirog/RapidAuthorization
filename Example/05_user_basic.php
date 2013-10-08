<?php

/**
 * Working with Users - Basic
 * @author Maykonn Welington Candido<maykonn@outlook.com>
 */
$sep = DIRECTORY_SEPARATOR;
require_once '..' . $sep . 'RapidAuthorization.php';

use Rapid\Authorization\RapidAuthorization;

$configuration = Array(
    'mysqlHost' => 'localhost',
    'mysqlPort' => 3306,
    'mysqlUser' => 'root',
    'mysqlPass' => '',
    'dbName' => 'rapid_authorization',
    'userTable' => 'user_table',
    'userTablePK' => 'user_pk',
    'useRapidAuthorizationAutoload' => true,
);


// Attach Role
$authorization = new RapidAuthorization($configuration);
$roleId = 1;
$userId = 1;
echo 'ADD ROLE #' . $roleId . ' TO USER : #' . $userId;
var_dump($authorization->user()->attachRole($roleId, $userId)) . '<br>';

$roleId = 3;
$userId = 1;
echo 'ADD ROLE #' . $roleId . ' TO USER : #' . $userId;
var_dump($authorization->user()->attachRole($roleId, $userId)) . '<br>';

$roleId = 3;
$userId = 2;
echo 'ADD ROLE #' . $roleId . ' TO USER : #' . $userId;
var_dump($authorization->user()->attachRole($roleId, $userId)) . '<br>';


// List all Roles that an User has permission
$userId = 1;
echo 'LISTING ALL ROLES FROM USER #' . $userId . '<pre>';
$userRoles = $authorization->user()->getRoles($userId);
print_r($userRoles);
echo '</pre>';

$userId = 2;
echo 'LISTING ALL ROLES FROM USER #' . $userId . '<pre>';
$userRoles = $authorization->user()->getRoles($userId);
print_r($userRoles);
echo '</pre>';
?>