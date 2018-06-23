<?php

namespace Solustat\TimeSheetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EventFreeController extends Controller
{
    public function listAction($page, Request $request)
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
            $request->getSession()->getFlashBag()->add('notice', 'Plus d\'element');
            return $this->render('SolustatTimeSheetBundle:EventFree:list.html.twig', array(
                'listEvents'  => $listEvents,
                'nbPages'     => 1,
                'page'        => 1
            ));
        }

        return $this->render('SolustatTimeSheetBundle:EventFree:list.html.twig', array(
            'listEvents'  => $listEvents,
            'nbPages'     => $nbPages,
            'page'        => $page,
        ));
    }

    public function linkAction($id, Request $request)
    {
        $userCurrent = $this->container->get('security.token_storage')->getToken()->getUser();

        $eventFree = $this->getDoctrine()
            ->getManager()
            ->getRepository('SolustatTimeSheetBundle:Event')
            ->linkEvent($id,$userCurrent);

        if($eventFree) {
            $request->getSession()->getFlashBag()->add('notice', 'Ce patient a été lié à votre emploi de temps');
        } else {
            $request->getSession()->getFlashBag()->add('notice', 'Ce patient n\'a pas été lié');
        }
        return $this->redirectToRoute('solustat_time_sheet_eventfree_list', array('page' => 1));

    }
}