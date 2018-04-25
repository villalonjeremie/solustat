<?php
namespace Solustat\TimeSheetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Solustat\TimeSheetBundle\Entity\Nurse;
use Solustat\TimeSheetBundle\Form\NurseType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class NurseController extends Controller
{
    public function listAction($page)
    {
        if ($page < 1) {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        $nbPerPage = 5;

        $listNurses = $this->getDoctrine()
            ->getManager()
            ->getRepository('SolustatTimeSheetBundle:Nurse')
            ->getNurses($page, $nbPerPage);
        $nbPages = ceil(count($listNurses) / $nbPerPage);

        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        return $this->render('SolustatTimeSheetBundle:Nurse:list.html.twig', array(
            'listNurses'    => $listNurses,
            'nbPages'       => $nbPages,
            'page'          => $page,
        ));
    }

    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $nurse = $em->getRepository('SolustatTimeSheetBundle:Nurse')->find($id);
        if (null === $nurse) {
          throw new NotFoundHttpException("L'Employé(e) d'id ".$id." n'existe pas.");
        }
        
        return $this->render('SolustatTimeSheetBundle:Nurse:view.html.twig', array(
            'nurse' => $nurse,
        ));
    }

    public function addAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('GET OUT!');
        }
        $nurse = new Nurse();
        $nurse->setCreatedAt(new \Datetime());
        $nurse->setSecurityLevel('user');
        $form = $this->createForm(NurseType::class, $nurse);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($nurse);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Employé(e) bien enregistré.');
            return $this->redirectToRoute('solustat_time_sheet_nurse_list', array('page' => 1));
        }

        return $this->render('SolustatTimeSheetBundle:Nurse:add.html.twig', array(
          'form' => $form->createView(),
        ));
    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $nurse = $em->getRepository('SolustatTimeSheetBundle:Nurse')->find($id);
        $nurse->setUpdatedAt(new \Datetime());

        if (null === $nurse) {
            throw new NotFoundHttpException("L'Employé(e) id ".$id." n'existe pas.");
        }

        $form = $this->get('form.factory')->create(NurseType::class, $nurse);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Employé(e) a bien été modifié.');
            return $this->redirectToRoute('solustat_time_sheet_nurse_list', array('page' => 1));
        }

        return $this->render('SolustatTimeSheetBundle:Nurse:edit.html.twig', array(
            'nurse' => $nurse,
            'form'   => $form->createView(),
        ));
    }

    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $nurse = $em->getRepository('SolustatTimeSheetBundle:Nurse')->find($id);

        if (null === $nurse) {
            throw new NotFoundHttpException("L'Employé(e) d'id ".$id." n'existe pas.");
        }

        $form = $this->get('form.factory')->create();
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($nurse);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', "L'Employé(e) a bien été supprimé.");
            return $this->redirectToRoute('solustat_time_sheet_nurse_list', array('page' => 1));
        }
    
        return $this->render('SolustatTimeSheetBundle:Nurse:delete.html.twig', array(
            'nurse' => $nurse,
            'form'   => $form->createView(),
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