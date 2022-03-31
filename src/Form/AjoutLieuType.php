<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutLieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomLieu', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue'
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'required' => false
            ] )
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'required' => false,
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => function(?Ville $ville) {
                    return $ville ? strtoupper($ville->getNomVille()) : '';
                }

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
