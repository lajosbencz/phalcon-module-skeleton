<?php

use Phalcon\Mvc\Router;
use Phalcon\Mvc\View;

date_default_timezone_set('US/Eastern');
setlocale(LC_ALL, 'ru_RU.UTF-8');

if (PHP_VERSION_ID < 50600) {
    iconv_set_encoding('internal_encoding', 'UTF-8');
}

$parameters = include_once __DIR__ . '/parameters.php';

return array(
    'parameters' => &$parameters,
    'services' => array(
        'db' => array(
            'class' => '\Phalcon\Db\Adapter\Pdo\Mysql',
            '__construct' => array(
                $parameters['db']
            )
        ),
        'logger' => array(
            'class' => '\Phalcon\Logger\Adapter\File',
            '__construct' => array(
                APPLICATION_PATH . '/logs/' . APPLICATION_ENV . '.log'
            )
        ),
        'url' => array(
            'class' => '\Phalcon\Mvc\Url',
            'shared' => true,
            'parameters' => $parameters['url']
        ),
        'tag' => array(
            'class' => '\App\Tag'
        ),
        'modelsMetadata' => array(
            'class' => function () {
                $metaData = new \Phalcon\Mvc\Model\MetaData\Memory();
                $metaData->setStrategy(new \Engine\Db\Model\Annotations\Metadata());

                return $metaData;
            }
        ),
        'dispatcher' => array(
            'class' => function ($application) {
                $evManager = $application->getDI()->getShared('eventsManager');

                $evManager->attach('dispatch:beforeException', function ($event, $dispatcher, $exception) use (&$application) {

                    if (!class_exists('Frontend\Module')) {
                        include_once APPLICATION_PATH . '/modules/frontend/Module.php';
                        $module = new Frontend\Module();
                        $module->registerServices($application->getDI());
                        $module->registerAutoloaders($application->getDI());
                    }

                    /**
                     * @var $dispatcher \Phalcon\Mvc\Dispatcher
                     */
                    $dispatcher->setModuleName('frontend');

                    $dispatcher->setParam('error', $exception);
                    $dispatcher->forward(
                        array(
                            'namespace' => 'Frontend\Controller',
                            'module' => 'frontend',
                            'controller' => 'error',
                            'action'     => 'index'
                        )
                    );
                    return false;
                });

                $dispatcher = new \Phalcon\Mvc\Dispatcher();
                $dispatcher->setEventsManager($evManager);
                return $dispatcher;
            }
        ),
        'modelsManager' => array(
            'class' => function ($application) {
                $eventsManager = $application->getDI()->get('eventsManager');

                $modelsManager = new \Phalcon\Mvc\Model\Manager();
                $modelsManager->setEventsManager($eventsManager);

                $eventsManager->attach('modelsManager', new \Engine\Db\Model\Annotations\Initializer());

                return $modelsManager;
            }
        ),
        'router' => array(
            'class' => function ($application) {
                $router = new Router(false);


                // This loop generates wildcard routes for every module
                foreach ($application->getModules() as $key => $module) {
                    $router->add('/'.$key.'/:params', array(
                        'module' => $key,
                        'controller' => 'index',
                        'action' => 'index',
                        'params' => 1
                    ))->setName($key);

                    $router->add('/'.$key.'/:controller/:params', array(
                        'module' => $key,
                        'controller' => 1,
                        'action' => 'index',
                        'params' => 2
                    ));

                    $router->add('/'.$key.'/:controller/:action/:params', array(
                        'module' => $key,
                        'controller' => 1,
                        'action' => 2,
                        'params' => 3
                    ));
                }

                // Create explicit routes:
                $router->add('/', array(
                    'module' => 'home',
                    'controller' => 'home',
                    'action' => 'index'
                ))->setName('default');
                // ->setName('whatever'); The name can be referenced at URL generation

                $router->add('/users/list', array(
                    'module' => 'users',
                    'controller' => 'list',
                    'action' => 'index',
                ));

                $router->add('/users/list/edit', array(
                    'module' => 'users',
                    'controller' => 'list',
                    'action' => 'edit',
                ));


                // If URL is not found, display this page
                $router->notFound(array(
                    'module' => 'home',
                    'namespace' => 'Home\Controller',
                    'controller' => 'Error',
                    'action' => 'index'
                ));

                return $router;
            },
            'parameters' => array(
                'uriSource' => Router::URI_SOURCE_SERVER_REQUEST_URI
            )
        ),
        'view' => array(
            'class' => function () {
                $class = new View();
                $class->registerEngines(array(
                    '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
                ));

                return $class;
            },
            'parameters' => array(
                'layoutsDir' => APPLICATION_PATH . '/layouts/' // No idea what this is for...
            )
        ),
        'auth' => array(
            'class' => '\App\Service\Auth'
        )
    ),
    'application' => array(
        'modules' => array(
            'oauth' => array(
                'className' => 'OAuth\Module',
                'path' => APPLICATION_PATH . '/modules/oauth/Module.php',
            ),
            'home' => array(
                'className' => 'Home\Module',
                'path' => APPLICATION_PATH . '/modules/home/Module.php',
            ),
            'users' => array(
                'className' => 'Users\Module',
                'path' => APPLICATION_PATH . '/modules/users/Module.php',
            ),
        )
    )
);
