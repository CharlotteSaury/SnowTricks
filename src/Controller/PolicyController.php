<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PolicyController extends AbstractController
{
    /**
     * @Route("/privacy", name="app_privacy")
     */
    public function privacy(): Response
    {        
        return $this->render('privacy.html.twig');
    }

    /**
     * @Route("/legal", name="app_legal")
     */
    public function legal(): Response
    {        
        return $this->render('legal.html.twig');
    }

}
