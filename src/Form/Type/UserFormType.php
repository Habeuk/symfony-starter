<?php
namespace App\Form\Type;

use App\DTO\UserDto;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 *
 * @author stephane
 * @extends AbstractType<null>
 */
class UserFormType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options): void {
    // TODO le formatage eclipse n'est pas ok.
    $builder->add('email', EmailType::class, [
      'label' => 'Email',
      'required' => true,
      'attr' => [
        'placeholder' => 'exemple@studio.com'
      ]
    ])
      ->add('plainPassword', PasswordType::class, [
      'label' => 'Mot de passe',
      'required' => false,
      'attr' => [
        'placeholder' => 'Au moins 8 caractères'
      ]
    ])
      ->add('lastName', TextType::class, [
      'label' => 'Nom',
      'required' => true,
      'attr' => [
        'maxlength' => 100
      ]
    ])
      ->add('firstName', TextType::class, [
      'label' => 'Prénom',
      'required' => false,
      'attr' => [
        'maxlength' => 100
      ]
    ])
      ->
    add('phone', TextType::class, [
      'label' => 'Téléphone',
      'required' => false,
      'attr' => [
        'placeholder' => '+33 6 12 34 56 78',
        'data-mask' => '+99 99 99 99 99'
      ]
    ])
      ->add('submit', SubmitType::class, [
      'label' => 'Enregistrer',
      'attr' => [
        'class' => 'btn btn-primary'
      ]
    ]);
  }

  public function configureOptions(OptionsResolver $resolver): void {
    // TODO comment la securisation du formulaire se passe.
    $resolver->setDefaults([
      'data_class' => UserDto::class,
      'current_user' => null,
      'csrf_protection' => false
    ]);
    $resolver->setAllowedTypes('current_user', [
      'null',
      User::class
    ]);
  }
}