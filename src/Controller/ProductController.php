<?php
// src/Controller/AppController.php

namespace App\Controller;

use App\Entity\Product;
use App\Data\SearchData;
use App\Form\SearchForm;
use App\Entity\Categorie;
use App\Repository\ProductRepository;
use App\Controller\BorrowingController;
use App\Repository\BorrowingRepository;
use App\Repository\CategorieRepository;
use App\Repository\UserRepository;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ProductController extends AbstractController
{
  
    public function add_product(Request $request, CategorieRepository $catrepo){

    $this->denyAccessUnlessGranted('ROLE_LENDER');

    // On crée un objet Advert
    $product = new Product();
    $entityManager = $this->getDoctrine()->getManager();
    // On crée le FormBuilder grâce au service form factory
    $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $product);

    // On ajoute les champs de l'entité que l'on veut à notre formulaire
    $formBuilder
      ->add('nom',      TextType::class)
      ->add('prix',     TextType::class)
      ->add('categorie', EntityType::class, [
        'label' => false,
        'required' => true,
        'class' => Categorie::class,
        'expanded' => true,
        'multiple' => false
    ])
      ->add('caution',   TextType::class)
      ->add('etat',    TextType::class)
      ->add('emplacement',    TextType::class,[
        'required'=> false
      ])
      ->add('num_serie',    TextType::class, [
        'required'=> false
      ]
      )
      ->add('kit',    TextType::class, [
        'required'=> false,
      ])
      ->add('save',      SubmitType::class)
      ->add('statut', CollectionType::class, [
        
        'entry_type'   => ChoiceType::class,
        'entry_options'  => [
            'label' => false,
            'choices'  => [
              $product->getStatutNames()
            ],
        ],
    ])
    ;
    // Pour l'instant, pas de candidatures, catégories, etc., on les gérera plus tard

    // À partir du formBuilder, on génère le formulaire
    $form = $formBuilder->getForm();

    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()) {
      $product = $form->getData();
      $product->setOwner($this->getUser());
      $entityManager -> persist($product);
      $entityManager->flush();

      return $this->redirectToRoute('list_products_by_lender');
    }

    // On passe la méthode createView() du formulaire à la vue
    // afin qu'elle puisse afficher le formulaire toute seule

    return $this->render('product/add_product.html.twig', array(
      'form' => $form->createView(),
    ));
  }



 
  public function list_products_by_lender(ProductRepository $productRepository)
  {
    $this->denyAccessUnlessGranted('ROLE_LENDER');
    

    $user = $this -> getUser();
    $id = $user -> getId();
    $listProduct =  $productRepository -> findBy(['owner' => $id]);


    return $this -> render ('product/list_products_by_lender.html.twig', array("listProduct" => $listProduct));
  }


  public function filtreproduit(Request $request, ProductRepository $productRepository){

    

    $data = new SearchData();
        
    $form = $this->createForm(SearchForm::class, $data);
    $form->handleRequest($request);
    
    $products = $productRepository->findSearch($data);
    return array($form, $products);
  }


  public function list_products( ProductRepository $productRepository, Request $request)
  {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    

    $listProducts = $productRepository -> findAll();

      foreach($listProducts as $product)
      {
        
        $product -> getNom();
        $product -> getPrix();
        $product -> getCategorie();
        $product -> getCaution();
        $product -> getEtat();
        $product -> getEmplacement();
        $product -> getNumSerie();
        $product -> getKit();
      }

      $form= $this -> filtreproduit($request, $productRepository)[0];
      $products = $this -> filtreproduit($request, $productRepository)[1];


       return $this  -> render('product/list_products.html.twig',
        array("Liste"=> $listProducts,
        'products' => $products,
        'form' => $form->createView()
        
        )
      
      );

}
        
  


    public function list_products_dispo( ProductRepository $productRepository, Request $request)
  {
    $this->denyAccessUnlessGranted('ROLE_BORROWER');

    $listProducts = $productRepository -> findBy(['statut' => "STATUT_DISPONIBLE"]); 

      foreach($listProducts as $product)
      {
        
        $product -> getNom();
        $product -> getPrix();
        $product -> getCategorie();
        $product -> getCaution();
        $product -> getEtat();
        $product -> getEmplacement();
        $product -> getNumSerie();
        $product -> getKit();
      }

        $data = new SearchData();
        
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);
        
        $products = $productRepository->findSearch($data);
        //dd($products);
        //foreach($products as $product){ echo $product->getNom();}


       return $this  -> render('product/list_products_dispo.html.twig',
        array("Liste"=> $listProducts,
        'products' => $products,
        'form' => $form->createView()
        
        )
      
      );


        return $this  -> render('product/list_products_dispo.html.twig',

        array("Liste"=> $listProducts));
  }


  
    
  public function delete_products(ProductRepository $productRepository, BorrowingRepository $borrowingRepository, $id, Request $request)
  {
    $this->denyAccessUnlessGranted('ROLE_LENDER');

    $user = $this -> getUser();
    $product = $productRepository -> findOneById($id);
    $borrowing = $borrowingRepository -> findOneByidUser($id);

      $entityManager = $this->getDoctrine()->getManager();
      if(!is_null($borrowing)) {$entityManager->remove($borrowing);}
      $entityManager->remove($product);
      $entityManager->flush();

      $listProducts = $productRepository -> findAll();
      $form= $this -> filtreproduit($request, $productRepository)[0];
      $products = $this -> filtreproduit($request, $productRepository)[1];
      if(in_array("ROLE_ADMIN", $user->getRoles())){
          return $this->redirectToRoute('home_admin');
        }
        elseif (in_array("ROLE_LENDER",  $user->getRoles())) {
          return $this->redirectToRoute('home_lender');
        }
        else{
          return $this->redirectToRoute('home_user');
        }
  }


  public function genarateQRcode(Request $request,ProductRepository $productRepository, $id){

    $this->denyAccessUnlessGranted('ROLE_LENDER');
    

    // On crée un objet Advert
    $product = $productRepository -> findOneById($id);
    
    $etat= $product->getEtat();
    $numSerie=$product->getNumserie();
    $nom=$product->GetNom();
    $statut=$product->GetStatut();
    //$borrowing=$product->getBorrowing();
    
    //$qrcode_message="Lobjet $nom ayant pour numero de serie $numSerie est : Dispo car le site n'est pas en ligne pour le moment pour le moment. Il est en $etat état.";
    $qrcode_message="127.0.0.1:8000/qrcode-confirmation/$id";
    $encodeurl = urlencode($qrcode_message);
    //echo($encodeurl); 
    // goqr $url = "https://api.qrserver.com/v1/create-qrcode/?data=$encodeurl&size=100x100";
    $url = "https://chart.googleapis.com/chart?chs=500x500&cht=qr&chl=$encodeurl&choe=UTF-8"; //API google

    return $this->render('product/qrcode_product.html.twig', array(
      'url' => $url,
      'statut' => $statut,
      'product' => $product
       ));
  }

  public function confirmationQRcode(Request $request,ProductRepository $productRepository, $id){

    $this->denyAccessUnlessGranted('ROLE_BORROWER');

    // On crée un objet Advert
    $product = $productRepository -> findOneById($id);
    
    $etat= $product->getEtat();
    $numSerie=$product->getNumserie();
    $nom=$product->GetNom();
    $statut=$product->GetStatut();
    $owner=$product->getOwner();
    $idOwner= $owner->getId();
    //$borrowing=$product->getBorrowing();
    

    return $this->render('product/qrcode_confirmation.html.twig', array(
      'statut' => $statut,
      'idOwner' => $idOwner,
      'product' => $product
       ));
  }



  public function show_product($id, ProductRepository $productRepository){

    $this->denyAccessUnlessGranted('ROLE_BORROWER');

    $product = $productRepository -> findOneById($id);
    
    
    return $this->render('product/show_product.html.twig', array(
      'product'=> $product
    ));
  }



 public function edit_product(Request $request, Product $product){

  $this->denyAccessUnlessGranted('ROLE_LENDER');
    
    $entityManager = $this->getDoctrine()->getManager();

    // On crée le FormBuilder grâce au service form factory
    $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $product);

    // On ajoute les champs de l'entité que l'on veut à notre formulaire
    $formBuilder
      ->add('nom',      TextType::class)
      ->add('prix',     TextType::class)
      ->add('caution',   TextType::class)
      ->add('etat',    TextType::class)
      ->add('emplacement',    TextType::class,[
        'required'=> false
      ])
      ->add('num_serie',    TextType::class, [
        'required'=> false
      ]
      )
      ->add('kit',    TextType::class, [
        'required'=> false,
      ])
      ->add('statut', CollectionType::class, [
        
        'entry_type'   => ChoiceType::class,
        'entry_options'  => [
            'choices'  => [
              $product->getStatutNames()
            ],
        ],
    ])
      ->add('categorie', EntityType::class, [
        'label' => false,
        'required' => true,
        'class' => Categorie::class,
        'expanded' => true,
        'multiple' => false
    ])
      
      
      ->add('save',      SubmitType::class)
      
    ;
      


    $form = $formBuilder->getForm();


     $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $product = $form->getData();
        $entityManager->persist($product);
        $entityManager->flush();
        
        return $this->redirectToRoute('list_products_by_lender');
    }


    // À partir du formBuilder, on génère le formulaire
    

    // On passe la méthode createView() du formulaire à la vue
    // afin qu'elle puisse afficher le formulaire toute seule

    return $this->render('product/edit_product.html.twig', array(
      'form' => $form->createView(),
    ));

  }


}