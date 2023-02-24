<?php

namespace App\Form;

use App\Entity\Comments;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('author', TextType::class, ['label' => 'Author', 'label_attr' => ['class' => 'text-sm text-white'], 'constraints' => [
                new NotBlank([
                    'message' => 'Please enter author name! ',
                ]),
                new Length([
                    'min' => 4,
                    'minMessage' => 'Your name should be at least {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 40,
                ]),
            ],])
            ->add('content', TextareaType::class, ['label' => 'Comment', 'label_attr' => ['class' => 'text-sm text-white'], 'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a comment!',
                ]),
                new Length([
                    'min' => 10,
                    'minMessage' => 'Your comment should be at least {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 255,
                ]),
            ],])
            ->add('post', SubmitType::class,[
                'label' => 'Post comment'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comments::class,
        ]);
    }
}
