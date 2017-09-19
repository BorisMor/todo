<?php

require_once('vendor/autoload.php');

use \app\AppTodo;
use \app\component\UsersComponent;
use \app\component\TodoComponent;

$app = new AppTodo(['debug' => false]);

/** @var TodoComponent $comTodo Компонент отвечающий за работы со списком todo */
$comTodo = TodoComponent::component($app);

$app->post('/login', function () use ($app){
    UsersComponent::actionLogin($app);
});

$app->post('/logout', function () use ($app){
    UsersComponent::actionLogout($app);
});

$app->put('/item', function () use ($app, $comTodo){
    $app->answer(
        $comTodo->actionInsertItems()
    );
});

$app->get('/item', function () use ($app, $comTodo){
    $app->answer(
        $comTodo->actionListItems()
    );
});

$app->post('/item/:id', function ($id) use ($app, $comTodo){
    $app->answer(
        $comTodo->actionUpdateItems($id)->attributes
    );
});

$app->delete('/item/:id', function ($id) use ($app, $comTodo){
    $app->answer(
        $comTodo->actionDeleteItems($id)
    );
});

$app->run();