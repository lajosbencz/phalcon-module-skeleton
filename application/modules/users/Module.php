<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Users;

use Phalcon\DiInterface;

class Module implements \Phalcon\Mvc\ModuleDefinitionInterface
{
    public function registerAutoloaders(DiInterface $dependencyInjector = null)
    {
        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces(array(
            'Users\Controller' => APPLICATION_PATH . '/modules/users/controllers/',
            'Users\Model' => APPLICATION_PATH . '/modules/users/models/',
        ));
        $loader->register();
    }

    public function registerServices(DiInterface $dependencyInjector)
    {
        $dispatcher = $dependencyInjector->get('dispatcher');
        $dispatcher->setDefaultNamespace('Users\Controller');

        /**
         * @var $view \Phalcon\Mvc\View
         */
        $view = $dependencyInjector->get('view');
        $view->setLayout('index');
        $view->setViewsDir(APPLICATION_PATH . '/modules/users/views/');
        $view->setLayoutsDir('../../common/layouts/');
        $view->setPartialsDir('../../common/partials/');

        /**
         * Common master layout
         * @see https://github.com/phalcon/mvc/blob/master/multiple-shared-layouts/apps/frontend/Module.php#L49
         */
        $view->setTemplateAfter('html');

        $dependencyInjector->set('view', $view);
    }
}
