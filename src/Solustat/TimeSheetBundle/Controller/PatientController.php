<?php

namespace Solustat\TimeSheetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use OC\TimeSheetBundle\Entity\Patient;

class PatientController extends Controller
{
    public function listAction($page)
    {
        if ($page < 1) {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        $nbPerPage = 10;

        $listPatients = $this->getDoctrine()
            ->getManager()
            ->getRepository('SolustatTimeSheetBundle:Patient')
            ->getPatients($page, $nbPerPage);

        $nbPages = ceil(count($listPatients) / $nbPerPage);

        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        return $this->render('SolustatTimeSheetBundle:Patient:list.html.twig', array(
            'listAdverts' => $listPatients,
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


    /**
     * @Security("has_role('ROLE_AUTEUR')")
     */
    public function addAction(Request $request)
    {
//        $advert = new Advert();
//
//        $form   = $this->get('form.factory')->create(AdvertType::class, $advert);
//
//
//        $em = $this->getDoctrine()->getManager();
//
//        $advert = $em->getRepository('OCPlatformBundle:Advert')->find(13);
//
//
//        $listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();
//
//        // Pour chaque compétence
//        foreach ($listSkills as $skill) {
//            // On crée une nouvelle « relation entre 1 annonce et 1 compétence »
//            $advertSkill = new AdvertSkill();
//
//            // On la lie à l'annonce, qui est ici toujours la même
//            $advertSkill->setAdvert($advert);
//            // On la lie à la compétence, qui change ici dans la boucle foreach
//            $advertSkill->setSkill($skill);
//
//            // Arbitrairement, on dit que chaque compétence est requise au niveau 'Expert'
//            $advertSkill->setLevel('Expert');
//
//            // Et bien sûr, on persiste cette entité de relation, propriétaire des deux autres relations
//            $em->persist($advertSkill);
//        }
//
//
//        $em->persist($advert);
//
//        $em->flush();
//
//        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
//            // On récupère toutes les compétences possibles
//
//            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
//
//            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
//        }
//
//        return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
//            'form' => $form->createView(),
//        ));
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