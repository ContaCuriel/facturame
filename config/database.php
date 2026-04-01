<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar cuál de las conexiones de base de datos a
    | continuación deseas usar como tu conexión predeterminada para las
    | operaciones de base de datos. Esta es la conexión que se utilizará
    | a menos que se especifique explícitamente otra conexión al ejecutar
    | una consulta o declaración.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'), // Cambiado de 'sqlite' a 'mysql' como default si no se encuentra DB_CONNECTION

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | A continuación se definen todas las conexiones de base de datos para
    | tu aplicación. Se proporciona una configuración de ejemplo para cada
    | sistema de base de datos compatible con Laravel. Eres libre de
    | añadir/eliminar conexiones.
    |
    */

    'connections' => [

        // La conexión 'landlord' se mantiene si necesitas acceder a una base de datos de "propietario"
        // pero NO será la conexión predeterminada a menos que lo especifiques en DB_CONNECTION
        'landlord' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE_LANDLORD', 'credinos_db'), // Esta usará DB_DATABASE_LANDLORD
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        // La conexión 'tenant' se mantiene, pero solo se usaría si tu aplicación
        // cambia dinámicamente de conexión a esta. Para una sola base de datos,
        // no será la predeterminada.
        'tenant' => [
            'driver'           => 'mysql',
            'host'             => env('DB_HOST', '127.0.0.1'),
            'port'             => env('DB_PORT', '3306'),
            'database'         => null, // Correcto: se deja en null para ser llenado dinámicamente
            'username'         => env('DB_USERNAME', 'root'),
            'password'         => env('DB_PASSWORD', ''),
            'unix_socket'      => env('DB_SOCKET', ''),
            'charset'          => 'utf8mb4',
            'collation'        => 'utf8mb4_unicode_ci',
            'prefix'           => '',
            'prefix_indexes'   => true,
            'strict'           => true,
            'engine'           => null,
            'options'          => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        // Esta es la conexión PRINCIPAL que usará tu aplicación
        // ya que tu .env tiene DB_CONNECTION=mysql
        'mysql' => [ // ¡IMPORTANTE! Esta clave ahora es 'mysql'
            'driver' => 'mysql', // El driver debe ser 'mysql'
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'), // Esto tomará 'universo_db' de tu .env
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | Esta tabla rastrea todas las migraciones que ya se han ejecutado para
    | tu aplicación. Usando esta información, podemos determinar cuáles de
    | las migraciones en disco aún no se han ejecutado en la base de datos.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis es un almacén de clave-valor de código abierto, rápido y avanzado
    | que también proporciona un cuerpo de comandos más rico que un sistema
    | de clave-valor típico como Memcached. Puedes definir tu configuración
    | de conexión aquí.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];