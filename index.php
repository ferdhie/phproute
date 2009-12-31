<?php

include('Controller.php');

class HelloController
{
    function hello($controller)
    {
        echo 'Hello World';
        echo '<p><a href="'.$controller->root.'">BACK</a></p>';
    }
}

class TestController
{
    function test($controller)
    {
        echo 'test';
        echo '<p><a href="'.$controller->root.'">BACK</a></p>';
    }
}

function index($controller)
{
    echo '
    <html>
    <head>
        <title>SimpleController</title>
    </head>
    <body>
        <h1>This is a simple controller which based on url maps</h1>
        <ul>
            <li><a href="'.$controller->root.'/">a function as controller</a></li>
            <li><a href="'.$controller->root.'/hello">define class as a string</a></li>
            <li><a href="'.$controller->root.'/test">also accept object</a></li>
            <li><a href="'.$controller->root.'/simple">and a file</a></li>
            <li><a href="'.$controller->root.'/archives/2009/12/31/hello-world">its accept matched pattern as arguments</a></li>
        </ul>
        <h2>Sample usage</h2>
        <pre>'.htmlentities(file_get_contents('index.php')).'</pre>
    </body>
    </html>';
}

function testparam($controller)
{
    $match = func_get_args();
    echo 'testparam: <pre>',print_r( $match ),'</pre>';
    echo '<p><a href="'.$controller->root.'">BACK</a></p>';
}

$urls = array( 
    '!^$!' => 'index', // function
    '!^test$!' => array( 'TestController', 'test' ), // class as string
    '!^hello$!' => array( new HelloController(), 'hello' ), // object
    '!^archives/(\d+)/(\d+)/(\d+)/(.+)$!' => 'testparam', // matched array as params
    '!^simple$!' => 'simple_include.php', // include file
);

$controller = new Controller();
$controller->routes($urls);

