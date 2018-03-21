<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Watcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => array(
                    new Email(),
                    new Regex([
                        'pattern' => "/^([1-9]{1})?$/",
                        'message' => 'v.number.invalid'
                    ]),
                ),
            ])
            ->add('nickName', TextType::class)
            ->add('oldPassword', PasswordType::class, [
                'mapped' => false,
            ])
            ->add('newPassword', PasswordType::class, [
                'mapped' => false,
            ])
            ->add('repeatPassword', PasswordType::class, [
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}

//class AddWatcherType extends AbstractType
//{
//    public function buildForm(FormBuilderInterface $builder, array $options)
//    {
//        $builder
//            ->add('title', TextType::class, [
//                'mapped' => false,
//                'constraints' => array(
//                    new NotBlank(),
//                ),
//            ])
//            ->add('url', UrlType::class)
//            ->add('percent', IntegerType::class, [
//                'mapped' => false,
//                'constraints' => array(
//                    new NotBlank(),
//                    new Range(['min' => 1, 'max' => 99]),
//                ),
//            ])
//            ->add('price', TextType::class, [
//                'mapped' => false,
//                'constraints' => array(
//                    new NotBlank,
//                    new Regex([
//                        'pattern' => "/^([1-9][0-9]*|0)(\.[0-9]{2})?$/",
//                        'message' => 'v.number.invalid'
//                    ]),
//                ),
//            ]);
//    }
//
//    public function configureOptions(OptionsResolver $resolver)
//    {
//        $resolver->setDefaults(array(
//            'data_class' => Watcher::class,
//        ));
//    }
//}