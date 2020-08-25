<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PolicyController extends AbstractController
{
    /**
     * Display privacy policy page
     * 
     * @Route("/privacy", name="app_privacy")
     * @return Response
     */
    public function privacy(): Response
    {        
        return $this->render('privacy.html.twig');
    }

    /**
     * Display legal notice page
     * 
     * @Route("/legal", name="app_legal")
     * @return Response
     */
    public function legal(): Response
    {        
        return $this->render('legal.html.twig');
    }

}
