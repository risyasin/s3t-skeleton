<?php

namespace App\Action;

use App\Base;
use App\Tools;
use App\AbstractAction;
use App\DataSeed;
use App\Models\User as User;


final class Home extends AbstractAction
{


    public function index($request, $response, $args)
    {

//         $post = R::findOne('posts', 'id = ?', [4]);
//
//        $data['post'] = $post->getProperties();

        $data['users'] = User::findAll('ORDER BY name ASC LIMIT 5');
        // $data['nusers'] = R::findAll( 'fblg_user', 'ORDER BY name ASC LIMIT 5');

        DataSeed::defaultPage();
        // return Base::json($data);

        return Base::render('web/default.twig', $data);

    }

}
