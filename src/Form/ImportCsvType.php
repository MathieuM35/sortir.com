<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportCsvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('csvfile',FileType::class,[
                'label'=>'Fichier CSV à importer :',
                'attr'=>['class'=>'border border-secondary']
            ])
            ->add('valider',SubmitType::class,[
                'label'=>'Envoyer',
                'attr'=>['class'=>'btn btn-primary btn-block']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
