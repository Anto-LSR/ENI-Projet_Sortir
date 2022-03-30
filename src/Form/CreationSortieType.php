<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
Use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;



class CreationSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,['label' => "Nom *" ])
            ->add('dateHeureDebut', DateTimeType::class, ['widget'=>"single_text"])
            ->add('duree',IntegerType::class, ['label' => 'Durée en min *', 'required' => false])
            ->add('dateLimiteInscription',DateTimeType::class, [
                'widget'=>"single_text",
                'constraints' => [
                new Assert\NotNull(),
                new Assert\GreaterThan([
                    'propertyPath' => 'parent.all[dateHeureDebut].data',
                    'message'=>"La date limite d'inscription ne peut pas être antérieure à la date de début."])]])
            ->add('nbInscriptionsMax', TextType::class,['label' => "Nombre d'inscriptions maximum *"])
            ->add('infoSortie')
            ->add('urlPhoto')
            ->add('lieu',EntityType::class,[
                'class' => Lieu::class,
                'choice_label' => 'nomLieu',
                'label' => "Lieu *"])
            ->add('Enregistrer',SubmitType::class)
            ->add('Publier',SubmitType::class)
            //->add('Annuler',SubmitType::class)

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
