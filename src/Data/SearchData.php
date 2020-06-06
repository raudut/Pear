<?php

namespace App\Data;

class SearchData{
        
        /**
         * @var string
         */
        public $q = '';
    
        /**
         * @var Categorie[]
         */
        public $categorie = [];
    
        /**
         * @var null|integer
         */
        public $max;
    
        /**
         * @var null|integer
         */
        public $min;  

                /**
         * @var string
         */
        public $statut ;
        
        
    }