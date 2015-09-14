<?php

namespace Users\Controller;

use Phalcon\Exception;
use Phalcon\Mvc\Controller;
use Users\Model\User;

/**
 * Class ListController
 * @package Users\Controller
 */
class ListController extends Controller
{
    /**
     * @throws \Phalcon\Exception
     */
    public function listAction()
    {
        $this->view->users = User::find();
    }

    public function editAction()
    {
        $this->view->user = User::findFirst($this->request->getQuery('id','int'));

        // If using routed parameter:
        // $this->view->user = User::findFirst($this->dispatcher->getParam('id','int'));
    }
}
