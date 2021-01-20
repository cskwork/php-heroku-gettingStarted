<?php

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog LOGGING service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register VIEW rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Register PDO Connection
$dbopts = parse_url(getenv('C:\Users\사용자\Documents\Github\php-heroku-gettingStarted\web\db'));
$app->register(new Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider('pdo'),
               array(
                'pdo.server' => array(
                   'driver'   => 'pgsql',
                   'user' => $dbopts["user"],
                   'password' => $dbopts["pass"],
                   'host' => $dbopts["host"],
                   'port' => $dbopts["port"],
                   'dbname' => ltrim($dbopts["path"],'/')
                   )
               )
);

//WEB HANDLERS
$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  //return $app['twig']->render('index.twig');
  return str_repeat('Hello', getenv('TIMES'));
});


$app->get('/cowsay', function() use($app) {
  $app['monolog']->addDebug('cowsay.');
  return "<pre>".\Cowsayphp\Cow::say("Cool beans")."</pre>";
});

//DB Query
$app->get('/db/', function() use($app) {
  $st = $app['pdo']->prepare('SELECT name FROM test_table');
  $st->execute();
  
  $names = array();
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $app['monolog']->addDebug('Row ' . $row['name']);
    $names[] = $row;
  }

  return $app['twig']->render('database.twig', array(
    'names' => $names
  ));
});

$app->run();
