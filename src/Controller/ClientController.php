<?php
// src/Controller/ClientController.php

namespace App\Controller;

//use App\Form\UserFormType;
use App\Entity\User;
use App\Repository\BorrowingRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\ArrayType;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Test\FormBuilderInterface;
use Symfony\Component\Form\ChoiceList\ChoiceList;

class ClientController extends AbstractController
{
    public function add_client(Request $request)
    {
        try {
            // On crée un objet User
            $user = new User();
            $mailadmin = new AppController();
            $mailuser = new AppController();

            $entityManager = $this->getDoctrine()->getManager();

            // On crée le FormBuilder grâce au service form factory
            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user);

            // On ajoute les champs de l'entité que l'on veut à notre formulaire
            $formBuilder
      ->add('nom', TextType::class)
      ->add('prenom', TextType::class)
      ->add('email', EmailType::class)
      ->add('password', PasswordType::class)
      ->add('naissance', BirthdayType::class, [
        'widget' =>"single_text",
      ])
      ->add('save', SubmitType::class)
    ;

      
      


            $form = $formBuilder->getForm();


            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->getData();
                $role[] = 'ROLE_BORROWER';
                $user->setRoles($role);
                $userlastname = $user->getNom();
                $userfirstname = $user->getPrenom();
                $username = " $userfirstname $userlastname";
                $usermail = $user->getEmail();
                $mailadmin-> send_email_add_user_admin($username, $usermail);
                $mailuser -> send_email_add_user_confirmation($username, $usermail);
                $entityManager->persist($user);
                $entityManager->flush();
        
                return $this->redirectToRoute('login');
            }


            // À partir du formBuilder, on génère le formulaire
    

            // On passe la méthode createView() du formulaire à la vue
            // afin qu'elle puisse afficher le formulaire toute seule

            return $this->render('user/add_user.html.twig', array(
      'form' => $form->createView(),
    ));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }

    public function show_user(UserRepository $userRepo, $id, ProductRepository $productRepository, BorrowingRepository $borrowingRepo)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
            $client= $userRepo -> findOneById($id);
            $listProduct =  $productRepository -> findBy(['owner' => $id]);
            $listBorrow = $borrowingRepo -> findBy(['idUser' => $id]);
    
            return $this-> render('user/show_user.html.twig', array(

      'client'=>$client,
      'listLendings'=> $listProduct,
      'listBorrowings' => $listBorrow
    ));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }


    public function edit_client(Request $request, User $user)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $entityManager = $this->getDoctrine()->getManager();

            // On crée le FormBuilder grâce au service form factory
            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user);

            // On ajoute les champs de l'entité que l'on veut à notre formulaire
            $formBuilder
      ->add('nom', TextType::class)
      ->add('prenom', TextType::class)
      ->add('email', EmailType::class)
      ->add('naissance', BirthdayType::class, [
        'widget' =>"single_text",
      ])
      ->add('save', SubmitType::class)
      ->add('roles', CollectionType::class, [
        'entry_type'   => ChoiceType::class,
        'entry_options'  => [
            'label' => false,
            'choices'  => [
              'Choisir un rôle' => $user->getRolesNames()
            ],
        ],
    ]);
      


            $form = $formBuilder->getForm();


            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->getData();
                $entityManager->persist($user);
                $entityManager->flush();
        
                return $this->redirectToRoute('list_clients');
            }


            // À partir du formBuilder, on génère le formulaire
    

            // On passe la méthode createView() du formulaire à la vue
            // afin qu'elle puisse afficher le formulaire toute seule

            return $this->render('user/edit_user.html.twig', array(
      'form' => $form->createView(),
    ));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }

  


    public function edit_me(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BORROWER');
        try {
            $user = $this -> getUser();

  
    
            $entityManager = $this->getDoctrine()->getManager();

            // On crée le FormBuilder grâce au service form factory
            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user);

            // On ajoute les champs de l'entité que l'on veut à notre formulaire
            $formBuilder
      ->add('nom', TextType::class)
      ->add('prenom', TextType::class)
      ->add('email', EmailType::class)
      ->add('naissance', BirthdayType::class, [
        'widget' =>"single_text",
      ])
      ->add('password', PasswordType::class)
      ->add('save', SubmitType::class)
      ;
      


            $form = $formBuilder->getForm();


            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->getData();
                $entityManager->persist($user);
                $entityManager->flush();

                if (in_array("ROLE_ADMIN", $user->getRoles())) {
                    return $this->redirectToRoute('home_admin');
                } elseif (in_array("ROLE_LENDER", $user->getRoles())) {
                    return $this->redirectToRoute('home_lender');
                } else {
                    return $this->redirectToRoute('home_user');
                }
            }


            // À partir du formBuilder, on génère le formulaire
    

            // On passe la méthode createView() du formulaire à la vue
            // afin qu'elle puisse afficher le formulaire toute seule

            return $this->render('user/edit_me.html.twig', array(
      'form' => $form->createView(),
    ));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }




    public function list_clients(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
            $listUser = $userRepository -> findAll();
            foreach ($listUser as $user) {
                $user -> getNom();
                $user -> getPrenom();
                $user -> getEmail();
                $user -> getNaissance();
            }
            return $this -> render(
                'user/list_users.html.twig',
                array("listUser" => $listUser)
            );
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }



    public function delete_client(UserRepository $userRepository, BorrowingRepository $borrowingRepository, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $user = $userRepository -> findOneById($id);
            $borrowing = $borrowingRepository -> findOneByidUser($id);

            $entityManager = $this->getDoctrine()->getManager();
            if (!is_null($borrowing)) {
                $entityManager->remove($borrowing);
            }
      
            $entityManager->remove($user);
            $entityManager->flush();

            $listUser = $userRepository -> findAll();
            return $this -> render('user/list_users.html.twig', array("listUser" => $listUser));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }

    

    public function add_lender()
    {
        $this->denyAccessUnlessGranted('ROLE_BORROWER');

        try {
            $mailadmin = new AppController();
            //$entityManager = $this->getDoctrine()->getManager();
            $connUser = $this->getUser();
            // $role[] = 'ROLE_LENDER';
            // $connUser->setRoles($role);
            // $entityManager->flush();

            $iduser = $connUser -> getId();
            $nameuser = $connUser -> getUsername();
            $mailuser = $connUser ->getEmail();

 
            $mailadmin->send_email_confirmation_preteur($nameuser, $mailuser, $iduser);

            return $this->render('user/user_en_preteur.html.twig');
        } catch (Exception $e) {
            echo $e;
            return $this -> render('security/erreur.html.twig');
        }
    }


    public function add_lender_admin(UserRepository $userRepo, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $mailadmin = new AppController();
            //echo($id);
            $entityManager = $this->getDoctrine()->getManager();
            //$connUser = $this->getUser();
            $oldborrower = $userRepo -> findBy(['id' => $id]);
            $newlender = $oldborrower[0];
            $role[] = 'ROLE_LENDER';
            $newlender->setRoles($role);
            $entityManager->flush();

            $nameuser = $newlender -> getUsername();
            $mailuser = $newlender ->getEmail();

            $mailadmin->send_email_info_passage_preteur($nameuser, $mailuser);

            return $this->render('user/admin_user_passer_en_preteur.html.twig');

        } catch (Exception $e) {

            return $this -> render('security/erreur.html.twig');
        }
    }


    public function list_lenders(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
            $listLender = $userRepository -> findAllLenders('ROLE_LENDER');

            return $this -> render('lender/list_lenders.html.twig', array("listLender" => $listLender));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }
}
