<?php
// src/Controller/AppController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AppController extends AbstractController
{

  public function home()
  {
  	return $this -> render('app/home.html.twig');
  }

  public function home_admin()
  {
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
  	return $this -> render('app/home_admin.html.twig');
    
  }

  public function home_lender()
  {
     $this->denyAccessUnlessGranted('ROLE_LENDER');
      return $this -> render('app/home_lender.html.twig');
    
  }

  public function home_user()
  {
     $this->denyAccessUnlessGranted('ROLE_BORROWER');
  	return $this -> render('app/home_user.html.twig');
    
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
        'HTMLPart' => "<h3>Bienvenue $user sur Pear Plateforme !</h3></br> Bonjour, </br> Je suis Billy The Pear, et je suis là pour répondre a tes question sur toute l'utilisation de PearPlateforme! Ton adresse mail pour te connecter sur Pear est : $mailuseradd </br> Amuse toi bien, <br/>Bien à vous, <br/> Billy The Pear "
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
            'HTMLPart' => "<h3>$user il y a du mouvement !</h3></br> Bonjour, </br> Votre objet $object a été emprunté ! Plus d'informations sur Pear ..</br> A bientôt, <br/>Bien à vous, <br/> Billy The Pear "
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
            'HTMLPart' => "<h3>$user il y a du mouvement !</h3></br> Bonjour, </br> Votre objet $object a été rendu ! Plus d'informations sur Pear ..</br> A bientôt, <br/>Bien à vous, <br/> Billy The Pear "
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

    public function send_email_confirmation_rendu($ownername, $owneremail, $productname,$id)
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
                'Email' => "$owneremail",
                //'Name' => "Billy The Pear"
                ]
            ],
            'Subject' => " Confirmation de rendu demandée ",
            'HTMLPart' => "<h3>$ownername il y a du mouvement !</h3>
            </br> Bonjour, </br> Votre objet $productname a été rendu !
            </br>
            </br>Veuillez confirmé que votre objet est bien rendu pour que d'autre puisse l'emprunter en cliquant sur le lien suivant : 
            </br> https://pear.min.epf.fr/rendre-product-qrcode/$id
            </br> 
            </br> A bientôt, <br/>Bien à vous, <br/> Billy The Pear "
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

    public function send_email_confirmation_preteur($preteurname, $preteuremail, $id)
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
                //'Name' => "Billy The Pear"
                ]
            ],
            'Subject' => " Un utilisateur veut devenir prêteur ",
            'HTMLPart' => "<h3> $preteurname veut devenir prêteur !</h3>
            </br> Bonjour, </br>$preteurname veut devenir prêteur !
            </br>
            </br>Veuillez approuver son changement de statut en cliquant sur le lien suivant : 
            </br> https://pear.min.epf.fr/add-lender-admin/$id
            </br> 
            </br> A bientôt, <br/>Bien à vous, <br/> Billy The Pear "
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
    public function send_email_info_passage_preteur($preteurname, $preteuremail)
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
                'Email' => "$preteuremail",
                //'Name' => "Billy The Pear"
                ]
            ],
            'Subject' => " Vous êtes devenu prêteur ",
            'HTMLPart' => "<h3> $preteurname vous êtes devenu préteur !</h3>
            </br> Bonjour, </br>$preteurname vous êtes devenu préteur !
            </br>
            </br>Billy The Pear vous a approuvé, vous êtes maintenant préteur. Rendez-vous sur Pear pour de nouvelles avantures : 
            </br> https://pear.min.epf.fr/
            </br> 
            </br> A bientôt, <br/>Bien à vous, <br/> Billy The Pear "
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