<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('hydra_big_hydra_homepage', new Route('/hello/{name}', array(
    '_controller' => 'HydraBigHydraBundle:Default:index',
)));

return $collection;
