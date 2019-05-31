<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['attr' => ['placeholder' => 'Nom du lieu']])
            ->add('rue', TextType::class, ['attr' => ['placeholder' => 'Nom de la rue']])
            ->add('latitude', NumberType::class, [
                'required'=>false,
                'attr' => ['placeholder' => 'Latitude du lieu']
            ])
            ->add('longitude', NumberType::class, [
                'required'=>false,
                'attr' => ['placeholder' => 'Longitude du lieu']
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
