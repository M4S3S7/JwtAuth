<?php

use Slim\App;
use Tuupola\Middleware\HttpBasicAuthentication;

return function (App $app) {
  // e.g: $app->add(new \Slim\Csrf\Guard);
  $container = $app->getContainer();
  $container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
  };

  $container["jwt"] = function ($container) {
    return new StdClass;
  };

  $app->add(new \Slim\Middleware\JwtAuthentication([
    "path" => "/api/v1",
    "secret" => "123456789helo_secret",
    "rules" => [
      new \Slim\Middleware\JwtAuthentication\RequestPathRule([
        "path" => "/",
        "passthrough" => ["/token", "/not-secure", "/home"]
      ]),
      new \Slim\Middleware\JwtAuthentication\RequestMethodRule([
        "passthrough" => ["OPTIONS"]
      ]),
    ],
    "callback" => function ($request, $response, $arguments) use ($container) {
      $container["jwt"] = $arguments["decoded"];
    },
    "error" => function ($request, $response, $arguments) {
      $data["Durum"] = "Hata";
      $data["Mesaj"] = $arguments["message"];
      return $response
      ->withHeader("Content-Type", "application/json")
      ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
  ]));

  $app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => "/public/token",
    "users" => [
      "user" => "password"
    ]
  ]));
  $app->add(new \Tuupola\Middleware\Cors([
    "logger" => $container["logger"],
    "origin" => ["*"],
    "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
    "headers.allow" => ["Authorization", "If-Match", "If-Unmodified-Since"],
    "headers.expose" => ["Authorization", "Etag"],
    "credentials" => true,
    "cache" => 60,
    "error" => function ($request, $response, $arguments) {
      return new UnauthorizedResponse($arguments["message"], 401);
    }
  ]));
};
