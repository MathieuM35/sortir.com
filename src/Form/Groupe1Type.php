<?php

namespace App\Form;

use App\Entity\Groupe;
use App\Entity\Lieu;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Groupe1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('membres', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
//                        ->andWhere('u.id NOT IN') //ne pas afficher l'utilisateur courant
                        ->orderBy('u.nom', 'ASC');
                },
                'choice_label' => function ($membres) {
                    return $membres->getNom() . ' ' . $membres->getPrenom() . ' | ' . $membres->getSite()->getNom();
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Choisissez les membres de votre groupe :'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Groupe::class,
        ]);
    }
}
