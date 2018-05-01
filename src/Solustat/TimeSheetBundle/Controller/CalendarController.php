<?php

namespace Solustat\TimeSheetBundle\Controller;

use AncaRebeca\FullCalendarBundle\Controller\CalendarController as CalendarControllerBundle;

class CalendarController extends CalendarControllerBundle
{
    public function viewAction()
    {
        return $this->render('SolustatTimeSheetBundle:Calendar:view.html.twig');
    }
}
