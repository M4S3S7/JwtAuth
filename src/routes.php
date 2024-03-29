<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;
use Tuupola\Base62;

return function (App $app) {
  $container = $app->getContainer();

  $app->get('/index/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
    // Sample log message
    $container->get('logger')->info("Slim-Skeleton '/' route");

    // Render index view
    return $container->get('renderer')->render($response, 'index.phtml', $args);
  });

  $app->get('/home', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', ["name" => "Welcome to Trinity Tuts demo Api"]);
  });



  $app->get("/not-secure",  function ($request, $response, $args) {

    $data = ["status" => 1, 'msg' => "No need of token to access me"];

    return $response->withStatus(200)
    ->withHeader("Content-Type", "application/json")
    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
  });


  $app->post("/formData",  function ($request, $response, $args) {
    $data = $request->getParsedBody();

    $result = ["status" => 1, 'msg' => $data];

    // Request with status response
    return $this->response->withJson($result, 200);
  });


  $app->post("/token",  function ($request, $response, $args) use ($container){
    /* Here generate and return JWT to the client. */
    //$valid_scopes = ["read", "write", "delete"]

    $requested_scopes = $request->getParsedBody() ?: [];

    $now = new DateTime();
    $future = new DateTime("+10 minutes");
    $server = $request->getServerParams();
    $jti = (new Base62)->encode(random_bytes(16));
    $payload = [
      "iat" => $now->getTimeStamp(),
      "exp" => $future->getTimeStamp(),
      "jti" => $jti,
      "sub" => $server["PHP_AUTH_USER"]
    ];
    $secret = "123456789helo_secret";
    $token = JWT::encode($payload, $secret, "HS256");
    $data["token"] = $token;
    $data["expires"] = $future->getTimeStamp();
    return $response->withStatus(201)
    ->withHeader("Content-Type", "application/json")
    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
  });

//TokenAlan Taraf
$app->group('/api/v1', function(\Slim\App $app) {

    $app->get('/user',function(Request $request, Response $response, array $args) {
        print_r($request->getAttribute('decoded_token_data'));
    });

    $app->get("/secure",  function ($request, $response, $args) {

      $data = ["status" => 1, 'msg' => "This route is secure!"];

      return $response->withStatus(200)
      ->withHeader("Content-Type", "application/json")
      ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

});


};
