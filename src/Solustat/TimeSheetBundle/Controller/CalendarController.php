<?php

namespace Solustat\TimeSheetBundle\Controller;

use AncaRebeca\FullCalendarBundle\Controller\CalendarController as CalendarControllerBundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends CalendarControllerBundle
{
    public function viewAction()
    {
        $userCurrent = $this->container->get('security.token_storage')->getToken()->getUser();

        $patients = $this->getDoctrine()
            ->getManager()
            ->getRepository('SolustatTimeSheetBundle:Patient')
            ->getPatientsByUser($userCurrent->getId());

        $visitstime = $this->getDoctrine()
            ->getManager()
            ->getRepository('SolustatTimeSheetBundle:VisitTime')
            ->getAllVisitsTime();


        return $this->render('SolustatTimeSheetBundle:Calendar:view.html.twig', array('patients' => $patients, 'visitstime' => $visitstime));
    }

    public function loadAction(Request $request)
    {
        $userCurrent = $this->container->get('security.token_storage')->getToken()->getUser();
        $startDate = new \DateTime($request->get('start'), new \DateTimeZone('America/Montreal'));
        $endDate = new \DateTime($request->get('end'),new \DateTimeZone('America/Montreal'));
        $filters = $request->get('filters');
        $filters['action'] = $request->get('action');
        $filters['patientId'] = $request->get('patientId');
        $filters['userCurrent'] = $userCurrent;

        if ($request->get('id') && $filters['action'] == 'delete') {
            try {
                $content = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('SolustatTimeSheetBundle:Event')
                    ->deleteEvent($request->get('id'));

                $status = empty($content) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

            } catch (\Exception $exception) {
                $content = json_encode(array('error' => $exception->getMessage()));
                $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($content);
            $response->setStatusCode($status);
            return $response;
        }

        try {
            $content = $this
                ->get('solustat_time_sheet_calendar.calendar')
                ->getData($startDate, $endDate, $filters);
            $status = empty($content) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        } catch (\Exception $exception) {
            $content = json_encode(array('error' => $exception->getMessage()));
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($content);
        $response->setStatusCode($status);

        return $response;
    }
}
