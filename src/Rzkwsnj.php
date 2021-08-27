<?php

/**
 * This file may be redistributed in whole or significant part.
 * ---------------- THIS IS FREE SOFTWARE ----------------
 *
 *
 * @file        Rzkwsnj.php
 * @package     Satu API
 * @company     rzkwsnj <rizkiwisnuaji.id@gmail.com>
 * @programmer  Rizki Wisnuaji, drg., M.Kom. <drg.rizkiwisnuaji@gmail.com>
 * @copyright   2021 rzkwsnj. All Rights Reserved.
 * @license     MIT
 * @version     Release: @1.0@
 * @framework   http://slimframework.com
 *
 *
 * ---------------- THIS IS FREE SOFTWARE ----------------
 * This file may be redistributed in whole or significant part.
 **/

declare(strict_types=1);

namespace Rzkwsnj;

use DateTime;
use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use PDO;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use ReflectionClass;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;
use stdClass;
use Throwable;
use Tuupola\Middleware\JwtAuthentication;
use Slim\App;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Slim\Factory\AppFactory;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;


# =========================================
# S A T U - A P I  V E N D O R
# =========================================
require __DIR__ . '/../vendor/autoload.php';


# =========================================
# S A T U - A P I  D O T E N V
# =========================================
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
if (file_exists(__DIR__ . '/../' . '.env')) {
    $dotenv->load();
}
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT', 'DB_DRIVER', 'DB_CHARSET', 'DB_COLLATION', 'DB_PREFIX']);


# ===================================
# S A T U - A P I  C O N T A I N E R
# ===================================
$container = new Container();
$satu_api = AppFactory::create(null, new Psr11Container($container));


# =========================================
# S A T U - A P I  E R R O R  H A N D L E R
# =========================================
$customErrorHandler = function (
    Request   $request,
    Throwable $exception,
    bool      $displayErrorDetails,
    bool      $logErrors,
    bool      $logErrorDetails
) use ($satu_api): Response {
    $statusCode = 500;
    if (is_int($exception->getCode()) &&
        $exception->getCode() >= 400 &&
        $exception->getCode() <= 500
    ) {
        $statusCode = $exception->getCode();
    }
    $className = new ReflectionClass(get_class($exception));
    $data = [
        'message' => $exception->getMessage(),
        'class' => $className->getShortName(),
        'status' => 'error',
        'code' => $statusCode,
    ];
    $body = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    $response = $satu_api->getResponseFactory()->createResponse();
    $response->getBody()->write($body);

    return $response
        ->withStatus($statusCode)
        ->withHeader('Content-type', 'application/problem+json');
};

$path = $_SERVER['APP_BASE_PATH'] ?? '';
$satu_api->setBasePath($path);
$satu_api->addRoutingMiddleware();
$satu_api->addBodyParsingMiddleware();

$displayError = filter_var(
    $_SERVER['DISPLAY_ERROR_DETAILS'] ?? false,
    FILTER_VALIDATE_BOOLEAN
);
$errorMiddleware = $satu_api->addErrorMiddleware($displayError, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);


# =========================================
# S A T U - A P I  L O G G E R
# =========================================
$logger = new Logger($_SERVER['APP_NAME']);
$rotating = new RotatingFileHandler(__DIR__ . "/../tmp/logs/" . date('Y-m-d') . '.log', 0, Logger::DEBUG);
$logger->pushHandler($rotating);


# =========================================
# S A T U - A P I  J W T
# =========================================
$satu_api->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$satu_api->add(function (Request $request, $handler): Response {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
        ->withHeader('Who-Am-I', 'RZKWSNJ')
        ->withHeader('Encoded-Style', '[PASTE HERE]')
        ->withHeader(
            'Access-Control-Allow-Headers',
            'X-Requested-With, Content-Type, Accept, Origin, Authorization'
        )
        ->withHeader(
            'Access-Control-Allow-Methods',
            'GET, POST, PUT, DELETE, PATCH, OPTIONS'
        );
});

$satu_api->add(new JwtAuthentication([
    "path" => "/api",
    "ignore" => ["/api/v1/login", "/api/v1/register"],
    "logger" => $logger,
    "secure" => true,
    "relaxed" => ["localhost", "satu-api.kodekoo"],
    "attribute" => $_SERVER['JWT_ATTRIBUTE'],
    "secret" => $_SERVER["JWT_SECRET"],
    "algorithm" => ["HS512"], //["HS256", "HS384", "HS512"]
    "error" => function ($response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return responseWithJson($response, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), 200);
    }
]));


# =========================================
# S A T U - A P I  C L A S S
# =========================================

abstract class Rzkwsnj
    extends Eloquent
{

    const DB_USERS = "users";
    const DB_PROFILE = "user_profile";
    const DB_ROLE = "roles";

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $rzkwsnjCapsule = new Capsule();
        $rzkwsnjCapsule->addConnection(
            array(
                'driver' => $_SERVER["DB_DRIVER"],
                'host' => $_SERVER["DB_HOST"],
                'database' => $_SERVER["DB_NAME"],
                'port' => $_SERVER["DB_PORT"],
                'username' => $_SERVER["DB_USER"],
                'password' => $_SERVER["DB_PASS"],
                'charset' => $_SERVER["DB_CHARSET"],
                'collation' => $_SERVER["DB_COLLATION"],
                'prefix' => $_SERVER["DB_PREFIX"],
            )
        );
        $rzkwsnjCapsule->setAsGlobal();
        $rzkwsnjCapsule->bootEloquent();
    }

}

class User
    extends Rzkwsnj
{
    public $incrementing = false;
    public $timestamps = false;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = self::DB_USERS;
    protected $guarded = ['user_id'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['user_id', 'session_id', 'user_password_hash', 'user_deleted', 'user_failed_logins', 'user_activation_hash'];

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'user_id');
    }

    public function role()
    {
        return $this->hasOne(UserRole::class, 'role_id', 'role_id');
    }

}

class UserProfile
    extends Rzkwsnj
{
    public $timestamps = false;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = self::DB_PROFILE;
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class UserRole
    extends Rzkwsnj
{
    public $timestamps = false;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = self::DB_ROLE;
    protected $guarded = ['role_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['role_id', 'role_permission'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


# =========================================
# S A T U - A P I  F U N C T I O N S
# =========================================

function responseWithJson(
    Response $response,
    string   $data,
    int      $status
): Response
{
    $response->getBody()->write($data);

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($status);
}


# =========================================
#  S A T U - A P I  R O U T E S
# =========================================

// TODO: PUBLIC ROUTES
$satu_api->group('', function (RouteCollectorProxy $group) {
    $group->get('/', function (Request $request, Response $response, $args) {
        $data = [
            'api' => $_SERVER['APP_NAME'],
            'version' => $_SERVER['APP_VERSION'],
            'timestamp' => time(),
        ];

        return responseWithJson($response, (string)json_encode($data), 200);
    });
    $group->get('/status', function (Request $request, Response $response, $args) {
        $status = [
            'status' => [
                'database' => 'OK',
            ],
            'api' => $_SERVER['APP_NAME'],
            'url' => $_SERVER['APP_URL'],
            'version' => $_SERVER['APP_VERSION'],
            'timestamp' => time(),
        ];

        return responseWithJson($response, (string)json_encode($status), 200);
    });
    $group->get('/users', function (Request $request, Response $response, $args) {
        $query = User::leftJoin('roles', 'users.role_id', '=', 'roles.role_id')
            ->leftJoin('user_profile', 'users.user_id', '=', 'user_profile.user_id')
            ->get();

        $all_users_profiles = [];

        foreach ($query as $user) :

            $all_users_profiles[$user->user_id] = new stdClass();
            $all_users_profiles[$user->user_id]->user_id = $user->user_id;
            $all_users_profiles[$user->user_id]->first_name = $user->first_name;
            $all_users_profiles[$user->user_id]->last_name = $user->last_name;
            $all_users_profiles[$user->user_id]->user_name = $user->user_name;
            $all_users_profiles[$user->user_id]->user_email = $user->user_email;
            $all_users_profiles[$user->user_id]->user_account_type = $user->user_account_type;
            $all_users_profiles[$user->user_id]->role_id = $user->role_id;
            $all_users_profiles[$user->user_id]->role_name = $user->role_name;
            $all_users_profiles[$user->user_id]->user_active = $user->user_active;
            $all_users_profiles[$user->user_id]->user_deleted = $user->user_deleted;
            $all_users_profiles[$user->user_id]->user_suspension_timestamp = $user->user_suspension_timestamp;
            $all_users_profiles[$user->user_id]->user_provider_type = $user->user_provider_type;

        endforeach;

        return responseWithJson($response, (string)json_encode($all_users_profiles), 200);

    });
});

// TODO: API ROUTES
$satu_api->group('/api/v' . $_SERVER['APP_VERSION'], function (RouteCollectorProxy $group) {
    $group->get('/', function (Request $request, Response $response, $args) {
        $data = [
            'api' => $_SERVER['APP_NAME'],
            'version' => $_SERVER['APP_VERSION'],
            'timestamp' => time(),
        ];

        return responseWithJson($response, (string)json_encode($data), 200);
    });
    $group->get('/login', function (Request $request, Response $response, $args) {
        $now = new DateTime();
        $future = new DateTime("now +2 hours");
        $secret = $_SERVER['JWT_SECRET'];
        $payload = [
            'iss' => $_SERVER['APP_URL'],
            'aud' => $_SERVER['APP_URL'],
            'iat' => $now->getTimestamp(),
            'exp' => $future->getTimestamp()
        ];

        $jwt = JWT::encode($payload, $secret, $_SERVER['JWT_ALGO']);

        $data["status"] = "success";
        $data["message"] = "Howdy, ";
        $data["data"] = "";
        $data["token"] = $_SERVER['JWT_HEADER'] . " " . $jwt;

        return responseWithJson($response, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), 200);
    });
});

$satu_api->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}',
    function (Request $request): void {
        throw new HttpNotFoundException($request);
    }
);


# =========================================
#  S A T U - A P I  R U N
# =========================================
$satu_api->run();