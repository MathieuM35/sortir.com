<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Ville;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'required' => false,
                'multiple' => false,
                'placeholder' => 'Choisir un site',
            ])
            ->add('nomContient', SearchType::class, [
                'label' => 'Le nom de la sortie contient : ',
                'required' => false,
            ])
            ->add('periodeDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Du : ',
                'required' => false,
            ])
            ->add('periodeFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'au : ',
                'required' => false,
            ])
            ->add('organisateur', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur',
                'required' => false,
            ])
            ->add('inscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit(e)',
                'required' => false,
            ])
            ->add('nonInscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit(e)',
                'required' => false,
            ])
            ->add('sortiePassee', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false,
            ])
            ->add('rechercher', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => ['class' => 'btn btn-primary btn-lg btn-block']
            ])
            ->add('reinitialiser', ResetType::class,[
                'label'=>'Réinitialiser les champs',
                'attr'=>['class'=>'btn btn-secondary btn-sm btn-block']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
