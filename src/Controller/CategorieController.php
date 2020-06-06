<?php
// src/Controller/CategorieController.php

namespace App\Controller;

use Exception;
use App\Entity\Categorie;
use App\Controller\AppController;
use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CategorieController extends AppController
{
    public function add_categorie(Request $request, CategorieRepository $repo)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $categorie = new Categorie();
            $listCat= $repo -> findAll();
            $categorie = new Categorie();
            $entityManager = $this->getDoctrine()->getManager();

            // On crée le FormBuilder grâce au service form factory
            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $categorie);

            // On ajoute les champs de l'entité que l'on veut à notre formulaire
            $formBuilder
            ->add('categorie', TextType::class, [
                'attr' => [
                 'placeholder' => 'Nom de la categorie'
                ]
           ])
            ->add('save', SubmitType::class)
               ;

            $form = $formBuilder->getForm();
            $bool = false;
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $categorie = $form->getData();
      
                foreach ($listCat as $cat) {
                    if ($cat->getCategorie() == $categorie->getCategorie()) {
                        $bool = true;
                    }
                }

                if ($bool==false) {
                    $entityManager -> persist($categorie);
                    $entityManager->flush();
  
                    return $this->redirectToRoute('list_categories');
                } else {
                    return $this->render('categories/add_categorie.html.twig', array(
                      'form' => $form->createView(),
                      'bool' => $bool,
                      
                    ));
                }
            }

            return $this->render('categories/add_categorie.html.twig', array(
      'form' => $form->createView(),
      'bool' => $bool,
    ));
        } catch (Exception $e) {
            echo($e);
            return $this -> render('security/erreur.html.twig');
        }
    }

    public function list_categories(CategorieRepository $repo, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_LENDER');
  

        try {
            $list = $repo -> findAll();

  
            foreach ($list as $categorie) {
                $categorie->getId();
                $categorie->getCategorie();
            }
  
  
            return $this  -> render(
                'categories/list_categories.html.twig',
                array("list"=> $list,
          
          )
            );
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }

    public function delete_categorie(CategorieRepository $repo, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $categorie = $repo -> findOneById($id);

            $entityManager = $this->getDoctrine()->getManager();
      
            $entityManager->remove($categorie);
            $entityManager->flush();

            $list = $repo -> findAll();

            return $this -> render('categories/list_categories.html.twig', array("list" => $list));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }


      

    public function edit_categorie(Request $request, Categorie $categorie)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
            $entityManager = $this->getDoctrine()->getManager();

            // On crée le FormBuilder grâce au service form factory
            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $categorie);

            // On ajoute les champs de l'entité que l'on veut à notre formulaire
            $formBuilder
      ->add('categorie', TextType::class, [
          'attr' => [
              'placeholder' => 'Nom de la categorie'
          ]
      ])
      ->add('save', SubmitType::class)
      ;

            $form = $formBuilder->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $categorie = $form->getData();
                $entityManager -> persist($categorie);
                $entityManager->flush();

                return $this->redirectToRoute('list_categories');
            }

            // On passe la méthode createView() du formulaire à la vue
            // afin qu'elle puisse afficher le formulaire toute seule

            return $this->render('categories/edit_categorie.html.twig', array(
      'form' => $form->createView(),
    ));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }
}
