<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Borrowing;
use App\Repository\ProductRepository;
use App\Repository\BorrowingRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $bool = false;

        if (in_array('STATUT_DISPONIBLE', $stat)) {
            $mailuser = new AppController();
            $borrowing = new Borrowing();
            $proprio = new User;
            $entityManager = $this->getDoctrine()->getManager();

            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $borrowing);
            $date = date('d/m/y');
            $mydate = new DateTime($date);

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
                $dateFin = $borrowing->getDateFin();

                $dateFin = $dateFin->format('Y-m-d H:i:s');
                $mydate = $mydate->format('Y-d-m H:i:s');

                if ($mydate > $dateFin){
                    $bool =true ;
                    return $this->render('borrowing/add_borrowing.html.twig', array(
                        'form' => $form->createView(),
                        'bool' => $bool,
                    ));
                }
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
      'bool' => $bool,
    ));
            } else {
                return $this -> render('security/erreur.html.twig');
            }
        } catch (Exception $e) {
            echo($e);
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
                array("listBorrowing" => $listBorrowing
                )
            );
        } catch (Exception $e) {
            echo $e;
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
            return $this -> render('security/erreur.html.twig');
        }
    }



    public function delete_borrowing(BorrowingRepository $borrowingRepository, $id, $bool, ProductRepository $productRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_LENDER');

        try {
            $bo = $borrowingRepository -> findOneById($id);

            $entityManager = $this->getDoctrine()->getManager();
            $idProduct = $bo->getIdProduct();
            $product = $productRepository -> findOneById($idProduct);
            $entityManager = $this->getDoctrine()->getManager();
            $statut[] = "STATUT_DISPONIBLE";
            $entityManager->remove($bo);
            $entityManager->flush();
            $listBorrowing = $borrowingRepository -> findAll();
            if($bool == false){
                
                return $this -> render('borrowing/list_borrowings.html.twig', array("listBorrowing" => $listBorrowing));
            }
            else{
                return $this -> render('product/qrcode_affichage_rendu_step_two.html.twig');
            }
        
        
        
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }
    public function delete_borrowing_from_list( ProductRepository $productRepository, BorrowingRepository $borrowingRepository, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_LENDER');

        try {
            $bo = $borrowingRepository -> findOneById($id);
            $idProduct = $bo->getIdProduct();
            $product = $productRepository -> findOneById($idProduct);
            $entityManager = $this->getDoctrine()->getManager();
            $statut[] = "STATUT_DISPONIBLE";
            $product->setStatut($statut);
            $entityManager->flush();
            $entityManager->remove($bo);
            $entityManager->flush();
            $listBorrowing = $borrowingRepository -> findAll();
            
                return $this -> render('borrowing/list_borrowings.html.twig', array("listBorrowing" => $listBorrowing));
            
        
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }
  


    public function rendre_product($id, BorrowingRepository $borrowingRepository, ProductRepository $productRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_BORROWER');
        try {
            $mailowner = new AppController();
            $entityManager = $this->getDoctrine()->getManager();
            $borrowing = $borrowingRepository -> findOneById($id);
            $idProduct = $borrowing->getIdProduct();
            $product = $productRepository -> findOneById($idProduct);
  
           
            $lender = $product -> getOwner();
            $owneremail = $lender -> getEmail();
            $ownername = $lender -> getNom();
            $productname = $product ->getNom();

       

            $mailowner->send_email_confirmation_rendu($ownername, $owneremail, $productname,$id); 
            $bool = false;
            //$this -> delete_borrowing($borrowingRepository, $borrowing,$bool);
            $entityManager->flush();


            $listBorrowing =  $borrowingRepository -> findBy(['idUser' =>$borrowing->getIdUser()]);
            return $this -> render('product/qrcode_affichage_rendu.html.twig', array("product" => $product));
        } catch (Exception $e) {
            echo $e;
            return $this -> render('security/erreur.html.twig');
        }
    }

    public function rendre_product_qrcode($id, ProductRepository $productRepository, BorrowingRepository $borrowingRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_LENDER');
        try {
            $product = $productRepository -> findOneById($id);
            $mailowner = new AppController();
            $entityManager = $this->getDoctrine()->getManager();
            $bool=true;
            $borrowing =  $borrowingRepository -> findBy(['idProduct' => $id]);
            $user = $borrowing[0]->getIdUser();

            $lender = $product -> getOwner();
            $owneremail = $lender -> getEmail();
            $ownername = $lender -> getNom();
            $productname = $product ->getNom();

     

            $mailowner->send_email_rendre_product($owneremail, $ownername, $productname);

            $this -> delete_borrowing($borrowingRepository, $borrowing, $bool, $productRepository);
            $entityManager->flush();


            $listBorrowing =  $borrowingRepository -> findBy(['idUser' =>$user]);
            return $this -> render('product/qrcode_affichage_rendu_step_two.html.twig', array("listBorrowing" => $listBorrowing));
        } catch (Exception $e) {
            
            return $this -> render('security/erreur.html.twig');
        }
    }

    public function show_borrowings($id, ProductRepository $productRepository, BorrowingRepository $borrowingRepo, UserRepository $userRespo)
    {
        $this->denyAccessUnlessGranted('ROLE_BORROWER');
        try {
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
        } catch (Exception $e) {
            return $this -> render('security/erreur.html.twig');
        }
    }
}
