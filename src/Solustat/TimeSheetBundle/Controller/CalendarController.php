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
        return $this->render('SolustatTimeSheetBundle:Calendar:view.html.twig');
    }

    public function loadAction(Request $request)
    {
        $userCurrent = $this->container->get('security.token_storage')->getToken()->getUser();
        $startDate = new \DateTime($request->get('start'), new \DateTimeZone('America/Montreal'));
        $endDate = new \DateTime($request->get('end'),new \DateTimeZone('America/Montreal'));
        $filters = $request->get('filters');
        $filters['action'] = $request->get('action');
        $filters['title'] = $request->get('title');
        $filters['userCurrent'] = $userCurrent;

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
