<?php

namespace Solustat\TimeSheetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Solustat\TimeSheetBundle\Entity\Patient;
use Solustat\TimeSheetBundle\Form\PatientType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EventFreeController extends Controller
{
    public function listAction($page)
    {


        if ($page < 1) {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        $nbPerPage = 5;

        $listEvents = $this->getDoctrine()
            ->getManager()
            ->getRepository('SolustatTimeSheetBundle:Event')
            ->getEventFree($page, $nbPerPage);

        $nbPages = ceil(count($listEvents) / $nbPerPage);

        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        return $this->render('SolustatTimeSheetBundle:EventFree:list.html.twig', array(
            'listEvents'  => $listEvents,
            'nbPages'     => $nbPages,
            'page'        => $page,
        ));
    }
}