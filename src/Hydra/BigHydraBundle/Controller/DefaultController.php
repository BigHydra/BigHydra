<?php

namespace Hydra\BigHydraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('HydraBigHydraBundle:Default:index.html.twig', array('name' => $name));
    }
}
