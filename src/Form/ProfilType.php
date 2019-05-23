<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, ['label'=>'Pseudo :', 'attr'=>['placeholder'=>'Veuillez renseigner le pseudo']])
            ->add('prenom', TextType::class, ['label'=>'Prénom :', 'attr'=>['placeholder'=>'Veuillez renseigner le prénom']])
            ->add('nom', TextType::class, ['label'=>'Nom :', 'attr'=>['placeholder'=>'Veuillez renseigner le nom']])
            ->add('telephone',TextType::class, ['label'=>'Nom :', 'attr'=>['placeholder'=>'Veuillez renseigner le numéro de téléphone']])
            ->add('email', EmailType::class, ['label'=>'Email :', 'attr'=>['placeholder'=>'Veuillez renseigner l\'adresse email']])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must be matched',
                'required' => true,
                'first_options' => array('label' => 'Mot de passe :', 'attr'=>['placeholder'=>'Veuillez renseigner votre mot de passe']),
                'second_options' => array('label' => 'Confirmation :', 'attr'=>['placeholder'=>'Veuillez confirmer votre mot de passe']),
            ])
            ->add('site', EntityType::class, ['class' => Site::class, 'choice_label' => 'nom'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
