<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Lender;
use App\Entity\Product;
use App\Entity\Borrowing;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Types\ArrayType;
use App\Repository\ProductRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Repository\BorrowingRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BorrowingController extends AbstractController
{
    public function add_borrowing(Request $request, ProductRepository $productRepository, $id, BorrowingRepository $borrowingRepo)
    {
        $this->denyAccessUnlessGranted('ROLE_BORROWER');
        
      try {
        $produit = $productRepository->findOneById($id);
        $stat = $produit->getStatut();
        $listBorrowing = $borrowingRepo -> findBy(['idProduct'=>$id]);

        if (in_array('STATUT_DISPONIBLE', $stat)) {
            $mailuser = new AppController();
            $borrowing = new Borrowing();
            $proprio = new User;
            $entityManager = $this->getDoctrine()->getManager();

            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $borrowing);
            $date = date('d/m/y');
            $mydate = new DateTime($date);
            //echo $mydate;
            //$products = $productRepository -> findProductByStatut('STATUT_DISPONIBLE');

            $formBuilder
                
                ->add('dateFin', DateType::class,[
                  'widget' =>"single_text",
                ])
                ->add('save', SubmitType::class)
            
                ;

            $form = $formBuilder->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $borrowing = $form->getData();
                $borrowing-> setDateDebut($mydate);
                $borrowing->setIdUser($this->getUser());
                $product = $productRepository->findOneById($id);
                $borrowing->setIdProduct($product);
                $entityManager->persist($borrowing);
                $entityManager->flush();

                    $prod = $borrowing->getIdProduct();
                    $statut[] = 'STATUT_LOUE';
                    $prod->setStatut($statut);
                    $entityManager->flush();

                    $proprio = $product -> getOwner();
                    $owneremail = $proprio -> getEmail();
                    $ownername = $proprio -> getNom();
                    $productname = $product ->getNom();

                    $mailuser->send_email_product($ownername, $owneremail, $productname);

        
                    return $this->redirectToRoute('list_my_borrowings');
                }
    
          
                return $this->render('borrowing/add_borrowing.html.twig', array(
      'form' => $form->createView(),
    ));
            } else {
                return $this -> render('security/erreur.html.twig');
            }
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }




    public function list_borrowings(BorrowingRepository $borrowingRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');


        try {
            $listBorrowing = $borrowingRepository -> findAll();
            foreach ($listBorrowing as $bo) {
                $bo -> getIdUser();
                $bo -> getIdProduct();
                $bo -> getDateDebut();
                $bo -> getDateFin();
            }
            return $this -> render(
                'borrowing/list_borrowings.html.twig',
                array("listBorrowing" => $listBorrowing)
            );
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }



    public function list_my_borrowings(BorrowingRepository $borrowingRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_BORROWER');

        


        try {
            $user = $this -> getUser();
            $id = $user -> getId();


            $listBorrowing =  $borrowingRepository -> findBy(['idUser' => $id]);

            return $this -> render('borrowing/list_my_borrowings.html.twig', array("listBorrowing" => $listBorrowing));
        } catch (Exception $e) {
            echo $e;
            return $this -> render('security/erreur.html.twig');
        }
    }



    public function delete_borrowing(BorrowingRepository $borrowingRepository, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $bo = $borrowingRepository -> findOneById($id);

            $entityManager = $this->getDoctrine()->getManager();
      
            $entityManager->remove($bo);
            $entityManager->flush();

            $listBorrowing = $borrowingRepository -> findAll();
            return $this -> render('borrowing/list_borrowings.html.twig', array("listBorrowing" => $listBorrowing));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }

  


    public function rendre_product($id, ProductRepository $productRepository, BorrowingRepository $borrowingRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_BORROWER');
        try {
            $mailowner = new AppController();
            $entityManager = $this->getDoctrine()->getManager();
            $borrowing = $borrowingRepository -> findOneById($id);
            $idProduct = $borrowing->getIdProduct();
            $product = $productRepository -> findOneById($idProduct);
  
            $statut[] = "STATUT_DISPONIBLE";
            $product->setStatut($statut);
            $entityManager->flush();

            $lender = $product -> getOwner();
            $owneremail = $lender -> getEmail();
            $ownername = $lender -> getNom();
            $productname = $product ->getNom();

       

            $mailowner->send_email_rendre_product($owneremail, $ownername, $productname);

            $this -> delete_borrowing($borrowingRepository, $borrowing);
            $entityManager->flush();


            $listBorrowing =  $borrowingRepository -> findBy(['idUser' =>$borrowing->getIdUser()]);
            return $this -> render('borrowing/list_my_borrowings.html.twig', array("listBorrowing" => $listBorrowing));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }

    public function rendre_product_qrcode($id, ProductRepository $productRepository, BorrowingRepository $borrowingRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_BORROWER');
        try {
            $product = $productRepository -> findOneById($id);
            $mailowner = new AppController();
            $entityManager = $this->getDoctrine()->getManager();

            $borrowing =  $borrowingRepository -> findBy(['idProduct' => $id]);
            $user = $borrowing[0]->getIdUser();
           
            $statut[] = "STATUT_DISPONIBLE";
            $product->setStatut($statut);
            $entityManager->flush();

            $lender = $product -> getOwner();
            $owneremail = $lender -> getEmail();
            $ownername = $lender -> getNom();
            $productname = $product ->getNom();

     

            $mailowner->send_email_rendre_product($owneremail, $ownername, $productname);

            $this -> delete_borrowing($borrowingRepository, $borrowing);
            $entityManager->flush();


            $listBorrowing =  $borrowingRepository -> findBy(['idUser' =>$user]);
            return $this -> render('borrowing/list_my_borrowings.html.twig', array("listBorrowing" => $listBorrowing));
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }

    public function show_borrowings($id, ProductRepository $productRepository, BorrowingRepository $borrowingRepo, UserRepository $userRespo)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
      //  try {
            $borrowingl= $borrowingRepo -> findby(['id' => $id]);
            $borrowing = $borrowingl[0];
            $productid =  $borrowing -> getIdProduct();
            $productl = $productRepository -> findby(['id'=> $productid]);
            $product = $productl[0];
            $owner = $product -> getOwner();
            $lenderid = $borrowing -> getidUser();
            $lenderl = $userRespo -> findby(['id'=>$lenderid]);
            $lender = $lenderl[0];
            //$borrowingDeb = $borrowing -> getDateDebut();

            return $this-> render('borrowing/show_borrowing.html.twig', array(
                                                    'product'=>$product,
                                                    'owner'=> $owner,
                                                    'lender' => $lender,
                                                    'borrowing' => $borrowing,
                                                  ));
        //} catch (Exception $e) {
        //    return $this -> render('security/erreur.html.twig');
       // }
    }
}
