<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\CallbackTransformer;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'placeholder' => 'Ex: Jean Dupont',
                    'class' => 'form-control',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'placeholder' => 'exemple@domaine.com',
                    'class' => 'form-control',
                ],
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 0612345678',
                    'class' => 'form-control',
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse postale',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 12 Rue des Fleurs, Paris',
                    'class' => 'form-control',
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false, // Sécurité : à hacher manuellement dans le contrôleur
                'attr' => [
                    'placeholder' => '8 caractères minimum',
                    'class' => 'form-control',
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle attribué',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Manager' => 'ROLE_MANAGER',
                ],
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Compte actif',
                'required' => false,
                'data' => true, // Actif par défaut
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ])
        ;

        // Convertit la valeur sélectionnée du rôle en tableau pour l'entité User
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                fn ($rolesArray) => $rolesArray[0] ?? null,
                fn ($rolesString) => $rolesString ? [$rolesString] : []
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
