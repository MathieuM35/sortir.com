<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'attr'=>['placeholder'=>'Nom de votre sortie']
            ])
            ->add('dateHeureDebut', DateTimeType::class, ['widget' => 'single_text'])
            ->add('dateLimiteInscription', DateType::class, ['widget' => 'single_text'])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'attr'=>['placeholder'=>'Nombre d\'inscription maximum']
            ])
            ->add('duree', IntegerType::class, [
                'label'=>'Durée (en min)',
                'attr'=>['placeholder'=>'Durée en minutes']
            ])
            ->add('infosSortie', TextareaType::class,[
                'attr'=>['placeholder'=>'Entrer une description pour votre sortie',]
            ])
        ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
                'mapped' => false,
            ])
        ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
            ])
        ->add('enregister',SubmitType::class,[
            'label'=>'Enregistrer',
            'attr' => ['class' => 'btn btn-primary btn-block']
        ])
        ->add('publier',SubmitType::class,[
            'label'=>'Publier',
            'attr' => ['class' => 'btn btn-primary btn-block']
            ]);

        /*
        $formModifier = function (FormInterface $form, Ville $ville = null) {
            $lieux = null === $ville ? array() : $ville->getLieux();

            $form->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'choices' => $lieux,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getLieu());//->getVille()
            }
        );

        $builder->get('ville')->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $ville = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $ville);
            }
        );*/

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'villes' => null
        ]);
    }
}
