<?php
namespace App\Action;

use App\AbstractAction;

final class Home extends AbstractAction
{

    public function dispatch($request, $response, $args)
    {

        $data['title'] = 'Slim 3 asd asdasdasd';

        $this->view->render($response, 'home.twig', $data);

        $this->logger->info('etwrasdasd');

        return $response;
    }

}
