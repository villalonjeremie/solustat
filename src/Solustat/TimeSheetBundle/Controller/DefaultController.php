<?php

namespace Solustat\TimeSheetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SolustatTimeSheetBundle:Default:index.html.twig');
    }
}
