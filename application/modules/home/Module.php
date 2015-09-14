<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Home;

class Module
{
    public function registerAutoloaders()
    {
        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces(array(
            'Home\Controller' => APPLICATION_PATH . '/modules/home/controllers/',
            'Home\Model' => APPLICATION_PATH . '/modules/home/models/',
        ));
        $loader->register();
    }

    public function registerServices($di)
    {
        $dispatcher = $di->get('dispatcher');
        $dispatcher->setDefaultNamespace('Home\Controller');

        /**
         * @var $view \Phalcon\Mvc\View
         */
        $view = $di->get('view');
        $view->setLayout('index');
        $view->setViewsDir(APPLICATION_PATH . '/modules/home/views/');
        $view->setLayoutsDir('../../common/layouts/');
        $view->setPartialsDir('../../common/partials/');

        /**
         * Common master layout
         * @see https://github.com/phalcon/mvc/blob/master/multiple-shared-layouts/apps/frontend/Module.php#L49
         */
        $view->setTemplateAfter('html');

        $di->set('view', $view);
    }
}
