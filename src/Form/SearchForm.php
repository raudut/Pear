<?php

namespace App\Form;

use App\Data\SearchData;
use App\Entity\Categorie;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher'
                ]
            ])
             ->add('categorie', EntityType::class, [
                 'label' => false,
                 'required' => false,
                 'class' => Categorie::class,
                 'expanded' => true,
                 'multiple' => true
             ])
             ->add('min', NumberType::class, [
                 'label' => false,
                 'required' => false,
                 'attr' => [
                     'placeholder' => 'Prix min'
                 ]
             ])
             ->add('max', NumberType::class, [
                 'label' => false,
                 'required' => false,
                 'attr' => [
                     'placeholder' => 'Prix max'
                 ]
             ])
             ->add('statut', ChoiceType::class,[
                 'choices' => ["STATUT_LOUE" => true , "STATUT_DISPONIBLE" => false],
                 'expanded' => true,
                 'multiple' => false, 
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Statut'
                ]
            ] 
             
             )

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

}