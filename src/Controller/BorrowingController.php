<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Lender;
use App\Entity\Borrowing;
use Doctrine\DBAL\Types\ArrayType;
use App\Repository\ProductRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Repository\BorrowingRepository;
use Doctrine\ORM\EntityManager;
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
    public function add_borrowing(Request $request, ProductRepository $productRepository, $id)
    {

        $user = $this -> getUser();
    if ($user == null){
      return $this->redirectToRoute('login');
    }
    else{

        $produit = $productRepository->findOneById($id);
        $stat = $produit->getStatut();

        if (in_array('STATUT_DISPONIBLE', $stat)) {
            $mailuser = new AppController();
            $borrowing = new Borrowing();
            $proprio = new User;
            $lender = new Lender;
            $entityManager = $this->getDoctrine()->getManager();

            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $borrowing);

            //$products = $productRepository -> findProductByStatut('STATUT_DISPONIBLE');

            $formBuilder
      ->add('dateDebut', DateType::class)
      ->add('dateFin', DateType::class)
      ->add('save', SubmitType::class)
      /*->add('idProduct', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'nom',
                'placeholder' => '== Choisir un objet ==',
                'choices' => $productRepository -> findProductByStatut('STATUT_DISPONIBLE')
            ]) */
      
      ;
         
            $form = $formBuilder->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $borrowing = $form->getData();
                $borrowing->setIdUser($this->getUser());
                $product = $productRepository->findOneById($id);
                $borrowing->setIdProduct($product);
                $entityManager->persist($borrowing);
                $entityManager->flush();

                $prod = $borrowing->getIdProduct();
                $statut[] = 'STATUT_LOUE';
                $prod->setStatut($statut);
                $entityManager->flush();

                $lender = $product -> getIdlender();
                $owneremail = $lender -> getEmail ();
                $ownername = $lender -> getNom();
                $productname = $product ->getNom();

                $mailuser->send_email_product($ownername, $owneremail,  $productname);
        
                return $this->redirectToRoute('list_my_borrowings');
                
            }
    
          
            return $this->render('borrowing/add_borrowing.html.twig', array(
      'form' => $form->createView(),
    ));
        } else {
            return $this -> render('security/erreur.html.twig');
        }
    }
  }



    public function list_borrowings(BorrowingRepository $borrowingRepository)
    {

        $user = $this -> getUser();
    if ($user == null){
      return $this->redirectToRoute('login');
    }
    else{

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
    }
  }


    public function list_my_borrowings(BorrowingRepository $borrowingRepository)
    {

        $user = $this -> getUser();
    if ($user == null){
      return $this->redirectToRoute('login');
    }
    else{

        $user = $this -> getUser();
        $id = $user -> getId();


        $listBorrowing =  $borrowingRepository -> findBy(['idUser' => $id]);

        return $this -> render('borrowing/list_my_borrowings.html.twig', array("listBorrowing" => $listBorrowing));
    }
  }



    public function delete_borrowing(BorrowingRepository $borrowingRepository, $id)
    {

        $user = $this -> getUser();
    if ($user == null){
      return $this->redirectToRoute('login');
    }
    else{

        $bo = $borrowingRepository -> findOneById($id);

        $entityManager = $this->getDoctrine()->getManager();
      
        $entityManager->remove($bo);
        $entityManager->flush();

        $listBorrowing = $borrowingRepository -> findAll();
        return $this -> render('borrowing/list_borrowings.html.twig', array("listBorrowing" => $listBorrowing));
    }
  }

    public function rendre_product($id, ProductRepository $productRepository, BorrowingRepository $borrowingRepository){
      
      $user = $this -> getUser();
    if ($user == null){
      return $this->redirectToRoute('login');
    }
    else{

      $mailowner = new AppController();
      $entityManager = $this->getDoctrine()->getManager();
      $borrowing = $borrowingRepository -> findOneById($id);
      $idProduct = $borrowing->getIdProduct();
      $product = $productRepository -> findOneById($idProduct);
  
      $statut[] = "STATUT_DISPONIBLE";
      $product->setStatut($statut);
      $entityManager->flush();

      $lender = $product -> getIdlender();
      $owneremail = $lender -> getEmail ();
      $ownername = $lender -> getNom();
      $productname = $product ->getNom();
       

      $mailowner->send_email_rendre_product($owneremail, $ownername,  $productname);

      $this -> delete_borrowing($borrowingRepository, $borrowing);  
      $entityManager->flush();

      $listBorrowing =  $borrowingRepository -> findBy(['idUser' =>$borrowing->getIdUser()]);
        return $this -> render ('borrowing/list_my_borrowings.html.twig', array("listBorrowing" => $listBorrowing));
      
    }
  }




}
