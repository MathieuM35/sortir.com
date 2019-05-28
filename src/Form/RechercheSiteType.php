<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheSiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomContient', SearchType::class, [
                'label' => 'Le nom contient : ',
                'required'   => false,
            ])
            ->add('rechercher', SubmitType::class, [
                'label'=>'Rechercher',
                'attr'=>['class'=>'btn btn-primary btn-block']
            ])
            ->add('voirTout', SubmitType::class, [
                'label'=>'Voir toutes les sites',
                'attr'=>['class'=>'voirTout btn btn-secondary btn-sm btn-block']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => Site::class,
        ]);
    }
}
