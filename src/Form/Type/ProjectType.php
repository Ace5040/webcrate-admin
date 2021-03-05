<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use App\Entity\Project;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\HttpsType;
use App\Entity\Backend;
use App\Repository\HttpsTypeRepository;
use App\Repository\BackendRepository;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\Type\DomainsType;
use App\Form\Type\DomainType;

class ProjectType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'constraints' => new NotBlank(),
        ])
        ->add('password', PasswordType::class, [
            'always_empty' => true,
            'required' => false,
            'empty_data' => '',
        ])
        ->add('domains', DomainsType::class, [
            'entry_type' => DomainType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'constraints' => new NotBlank(),
            'prototype' => true
        ])
        ->add('https', EntityType::class, [
            'class' => HttpsType::class,
            'choice_label' => function ($httpsType) {
                return $httpsType->getName();
            },
            //'multiple' => false,
            'expanded' => true
        ])
        ->add('backend', EntityType::class, [
            'class' => Backend::class,
            'choice_label' => function ($backend) {
                return $backend->getFullName();
            },
            'query_builder' => function (BackendRepository $er) {
                return $er->createQueryBuilder('b')
                    ->addOrderBy('b.name', 'ASC')
                    ->addOrderBy('b.version', 'DESC');
            },
            //'multiple' => false,
            'expanded' => true
        ])
        ->add('backup', CheckboxType::class, [
            'required' => false,
        ])
        ->add('mysql', CheckboxType::class, [
            'required' => false,
        ])
        ->add('mysql5', CheckboxType::class, [
            'required' => false,
        ])
        ->add('postgre', CheckboxType::class, [
            'required' => false,
        ])
        ->add('nginx_config', CheckboxType::class, [
            'required' => false,
        ])
        ->add('root_folder', TextType::class, [
            'constraints' => new NotBlank(),
        ])
        ->add('gunicorn_app_module', TextType::class, [
            'required' => false,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }

}
