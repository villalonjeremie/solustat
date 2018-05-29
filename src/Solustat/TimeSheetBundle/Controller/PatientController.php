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

class PatientController extends Controller
{
    public function listAction($page)
    {
        if ($page < 1) {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        $nbPerPage = 5;

        $listPatients = $this->getDoctrine()
            ->getManager()
            ->getRepository('SolustatTimeSheetBundle:Patient')
            ->getPatients($page, $nbPerPage);

        $nbPages = ceil(count($listPatients) / $nbPerPage);

        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        return $this->render('SolustatTimeSheetBundle:Patient:list.html.twig', array(
            'listPatients' => $listPatients,
            'nbPages'     => $nbPages,
            'page'        => $page,
        ));
    }

    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository('SolustatTimeSheetBundle:Patient')->find($id);
        if (null === $patient) {
          throw new NotFoundHttpException("Le patient d'id ".$id." n'existe pas.");
        }
        
        return $this->render('SolustatTimeSheetBundle:Patient:view.html.twig', array(
            'patient' => $patient,
        ));
    }

    public function addAction(Request $request)
    {
        $patient = new Patient();
        $patient->setCreatedAt(new \Datetime());
        $form = $this->createForm(PatientType::class, $patient);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();

            try{
                $em->persist($patient);
                $em->flush();
                $request->getSession()->getFlashBag()->add('notice', 'Patient bien enregistré.');
                return $this->redirectToRoute('solustat_time_sheet_patient_list', array('page' => 1));
            } catch (\Exception $e) {
                $request->getSession()->getFlashBag()->add('error', 'Patient deja enregistré.');
                return $this->redirectToRoute('solustat_time_sheet_patient_list', array('page' => 1));
            }
        }

        return $this->render('SolustatTimeSheetBundle:Patient:add.html.twig', array(
          'form' => $form->createView(),
        ));
    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository('SolustatTimeSheetBundle:Patient')->find($id);

        $patient->setUpdatedAt(new \Datetime());

        if (null === $patient) {
            throw new NotFoundHttpException("Le patient id ".$id." n'existe pas.");
        }

        $form = $this->get('form.factory')->create(PatientType::class, $patient);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Patient bien modifié.');
            return $this->redirectToRoute('solustat_time_sheet_patient_list', array('page' => 1));
        }

        return $this->render('SolustatTimeSheetBundle:Patient:edit.html.twig', array(
            'patient' => $patient,
            'form'   => $form->createView(),
        ));
    }

    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository('SolustatTimeSheetBundle:Patient')->find($id);

        if (null === $patient) {
            throw new NotFoundHttpException("Le patient d'id ".$id." n'existe pas.");
        }

        $form = $this->get('form.factory')->create();
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($patient);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', "Le Patient a bien été supprimé.");
            return $this->redirectToRoute('solustat_time_sheet_patient_list', array('page' => 1));
        }
    
        return $this->render('SolustatTimeSheetBundle:Patient:delete.html.twig', array(
            'patient'   => $patient,
            'form'      => $form->createView(),
        ));
    }


    public function menuAction($limit)
    {
//        $em = $this->getDoctrine()->getManager();
//
//        $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findBy(
//            array(),
//            array('date' => 'desc'),
//            $limit,
//            0
//        );
//
//        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
//            'listAdverts' => $listAdverts
//        ));
    }
}