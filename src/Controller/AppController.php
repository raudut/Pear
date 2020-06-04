<?php
// src/Controller/AppController.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Product;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Controller\ArrayList;
use Doctrine\DBAL\Types\ArrayType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class AppController extends AbstractController
{
    public function home()
    {
        return $this -> render('app/home.html.twig');
    }

    public function home_admin()
    {
        $user = $this -> getUser();
        if ($user == null) {
            return $this->redirectToRoute('login');
        } else {
            return $this -> render('app/home_admin.html.twig');
        }
    }

    public function home_lender()
    {
        $user = $this -> getUser();
        if ($user == null) {
            return $this->redirectToRoute('login');
        } else {
            return $this -> render('app/home_lender.html.twig');
        }
    }

    public function home_user()
    {
        $user = $this -> getUser();
        if ($user == null) {
            return $this->redirectToRoute('login');
        } else {
            return $this -> render('app/home_user.html.twig');
        }
    }



    public function send_email_add_user_admin($user, $mailuseradd)
    {
        $bodyAdmin = [
      'Messages' => [
          [
          'From' => [
              'Email' => "pear@epf.fr",
              'Name' => "Billy The Pear"
          ],
          'To' => [
              [
              'Email' => "pear@epf.fr",
              'Name' => "Billy The Pear"
              ]
          ],
          'Subject' => "Un utilisateur de plus sur Pear !",
          'HTMLPart' => "<h3>Le user $user a ete ajouté avec succes ! </h3> </br> Son adresse mail est : $mailuseradd </br> Vous pouvez administrer son role et ses actions sur notre plateforme a tout instant. <br/>Et ca c'est beauuu ! <br/> Bien à vous <br/> Billy The Pear "
          ]
      ]
  ];
  
        $ch = curl_init();
  
        curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3.1/send");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyAdmin));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
      'Content-Type: application/json')
        );
        curl_setopt($ch, CURLOPT_USERPWD, "47219a1c999266c91efd07942860e61d:46478c82213deacd3a251becee8e5776");
        $server_output = curl_exec($ch);
        curl_close($ch);
  
        $response = json_decode($server_output);
        if ($response->Messages[0]->Status == 'success') {
            // echo "Email sent successfully.";
        }
    }

    public function send_email_add_user_confirmation($user, $mailuseradd)
    {
        $bodyAdmin = [
    'Messages' => [
        [
        'From' => [
            'Email' => "pear@epf.fr",
            'Name' => "Billy The Pear"
        ],
        'To' => [
            [
            'Email' => "$mailuseradd",
            'Name' => "$user"
            ]
        ],
        'Subject' => "Bienvenue !",
        'HTMLPart' => "<h3>Bienvenue $user sur Pear Plateforme !</h3></br> Bonjour, </br> Je suis Billy The Pear, et je suis là pour répondre a tes question sur toute l'utilisation de PearPlateforme! Ton adresse mail pour te connecter sur Pear est : $mailuseradd </br> Amuse toi bien, <br/>Bien à toi, <br/> Billy The Pear "
        ]
    ]
];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3.1/send");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyAdmin));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
    'Content-Type: application/json')
        );
        curl_setopt($ch, CURLOPT_USERPWD, "47219a1c999266c91efd07942860e61d:46478c82213deacd3a251becee8e5776");
        $server_output = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($server_output);
        if ($response->Messages[0]->Status == 'success') {
            // echo "Email sent successfully.";
        }
    }


    public function send_email_product($user, $mailuseradd, $object)
    {
        $bodyAdmin = [
        'Messages' => [
            [
            'From' => [
                'Email' => "pear@epf.fr",
                'Name' => "Billy The Pear"
            ],
            'To' => [
                [
                'Email' => "$mailuseradd",
                'Name' => "Billy The Pear"
                ]
            ],
            'Subject' => "$user Il y a du mouvement!",
            'HTMLPart' => "<h3>$user il y a du mouvement !</h3></br> Bonjour, </br> Votre objet $object a été emprunté ! Plus d'informations sur Pear ..</br> A bientôt, <br/>Bien à toi, <br/> Billy The Pear "
            ]
        ]
    ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3.1/send");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyAdmin));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
        'Content-Type: application/json')
        );
        curl_setopt($ch, CURLOPT_USERPWD, "47219a1c999266c91efd07942860e61d:46478c82213deacd3a251becee8e5776");
        $server_output = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($server_output);
        if ($response->Messages[0]->Status == 'success') {
            // echo "Email sent successfully.";
        }
    }

    public function send_email_rendre_product($usermail, $user, $object)
    {
        $bodyAdmin = [
        'Messages' => [
            [
            'From' => [
                'Email' => "pear@epf.fr",
                'Name' => "Billy The Pear"
            ],
            'To' => [
                [
                'Email' => "$usermail",
                //'Name' => "Billy The Pear"
                ]
            ],
            'Subject' => " Il y a du mouvement!",
            'HTMLPart' => "<h3>$user il y a du mouvement !</h3></br> Bonjour, </br> Votre objet $object a été rendu ! Plus d'informations sur Pear ..</br> A bientôt, <br/>Bien à toi, <br/> Billy The Pear "
            ]
        ]
    ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3.1/send");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyAdmin));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
        'Content-Type: application/json')
        );
        curl_setopt($ch, CURLOPT_USERPWD, "47219a1c999266c91efd07942860e61d:46478c82213deacd3a251becee8e5776");
        $server_output = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($server_output);
        if ($response->Messages[0]->Status == 'success') {
        }
    }
}