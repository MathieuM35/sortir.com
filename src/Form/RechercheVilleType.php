<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheVilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomContient', SearchType::class,
                ['label'=>'Le nom contient : ',
                    'required'   => false,
                    ])
            ->add('rechercher',SubmitType::class,[
                'label'=>'Rechercher',
                'attr'=>['class'=>'btn btn-primary btn-block']
            ])
            ->add('voirTout',SubmitType::class,[
                'label'=>'Voir toutes les villes',
                'attr'=>['class'=>'voirTout btn btn-secondary btn-sm btn-block']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //
        ]);
    }
}
