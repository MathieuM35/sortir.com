<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnulationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('motif',TextareaType::class,[
                'label'=>'Motif de l\'annulation : ',
                'attr'=>['placeholder'=>'Entrez ici le motif de l\'annulation de la sortie']
            ])
            ->add('annuler',SubmitType::class,[
                'label'=>'Annuler la sortie',
                'attr' => [
                    'class' => 'btn btn-danger',
                ]
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
