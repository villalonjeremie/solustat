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

        $nbPerPage = 2;

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
//        $em = $this->getDoctrine()->getManager();
//
//        // Pour récupérer une seule annonce, on utilise la méthode find($id)
//        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
//
//        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
//        // ou null si l'id $id n'existe pas, d'où ce if :
//        if (null === $advert) {
//            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
//        }
//
//        // Récupération de la liste des candidatures de l'annonce
//        $listApplications = $em
//            ->getRepository('OCPlatformBundle:Application')
//            ->findBy(array('advert' => $advert));
//
//        // Récupération des AdvertSkill de l'annonce
//        $listAdvertSkills = $em
//            ->getRepository('OCPlatformBundle:AdvertSkill')
//            ->findBy(array('advert' => $advert));
//
//        return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
//            'advert'           => $advert,
//            'listApplications' => $listApplications,
//            'listAdvertSkills' => $listAdvertSkills,
//        ));
    }

    public function addAction(Request $request)
    {
        $patient = new Patient();
        $patient->setCreatedAt(new \Datetime());
        $form = $this->createForm(PatientType::class, $patient);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($patient);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Patient bien enregistré.');
            return $this->redirectToRoute('SolustatTimeSheetBundle:Patient', array('id' => $patient->getId()));
        }

        return $this->render('SolustatTimeSheetBundle:Patient:form.html.twig', array(
          'form' => $form->createView(),
        ));
    }

    public function editAction($id, Request $request)
    {
//        $em = $this->getDoctrine()->getManager();
//
//        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
//
//        if (null === $advert) {
//            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
//        }
//
//        if ($request->isMethod('POST')) {
//            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
//
//            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
//        }
//
//        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
//            'advert' => $advert
//        ));
    }

    public function deleteAction($id)
    {
//        $em = $this->getDoctrine()->getManager();
//
//        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
//
//        if (null === $advert) {
//            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
//        }
//
//        // On boucle sur les catégories de l'annonce pour les supprimer
//        foreach ($advert->getCategories() as $category) {
//            $advert->removeCategory($category);
//        }
//
//        $em->flush();
//
//        return $this->render('OCPlatformBundle:Advert:delete.html.twig');
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