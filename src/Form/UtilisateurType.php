<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Entity\Ville;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true
            ])
           ->add('password', PasswordType::class, [
                'mapped' => false,
                'required' => true,
            ])
            ->add('ConfirmPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
            ])
            ->add('pseudo', TextType::class, [
                'required' => true
            ])
            ->add('prenom', TextType::class, [
            'required' => true
            ])
            ->add('nom', TextType::class, [
                'required' => true
            ])
            ->add('num_tel', TelType::class)
            ->add('ImportPhoto', FileType::class,  [
                'mapped' => false,
                'required' => false
            ])
            ->add('id_ville',EntityType::class, [
                'required' => true,
                'class' => Ville::class,
                'choice_label' => 'nom',
                'label' => 'Ville'
            ])
            ->add("roles", ChoiceType::class, [
                "choices" => [
                    "ADMIN" => "ROLE_ADMIN",
                    "ORGA" => "ROLE_ORGA",
                    "USER" => "ROLE_USER"
                ],
                "mapped" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
