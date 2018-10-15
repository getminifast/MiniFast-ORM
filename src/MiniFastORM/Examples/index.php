<?php

include_once __DIR__.'/src/Base.php';
include_once __DIR__.'/src/BaseQuery.php';
include_once __DIR__.'/src/User.php';
include_once __DIR__.'/src/UserQuery.php';

$test = new Base('table');
$test->set('col1', 'pseudo');
$test->set('col2', 'email');
$test->save();

echo '<br>';

$user = new User();
$user->setPseudo('iTechCydia');
$user->setEmail('email@server.com');
$user->save();

echo '<br>';

$user2 = BaseQuery::create('user');
$user2->set('col1', 'val1');
$user2->set('col2', 'otherval');
$user2->save();

echo '<br>';

$user3 = UserQuery::create()
    ->filterByPseudo('iTechCydia')
    ->filterByEmail('vincent.bathelier@epsi.fr')
    ->limit(10)
    ->offset(5)
    ->find();
echo $user3;

echo '<br>';

$test = UserQuery::create()->findPKs([1, 2, 3, 4]);
echo $test;
