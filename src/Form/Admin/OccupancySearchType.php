<?php

namespace App\Form\Admin;

use App\Entity\Organization\Pole;
use App\Form\Model\Admin\OccupancySearch;
use App\Form\Model\Event\EventSearch;
use App\Form\Type\DateSearchType;
use App\Form\Utils\Choices;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OccupancySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $this->setData($event->getData());
        });
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $this->setData($event->getData());
        });

        $builder
            ->add('year', ChoiceType::class, [
                'choices' => Choices::getYears(5),
                'placeholder' => 'Year',
                'required' => false,
            ])
            ->add('pole', EntityType::class, [
                'class' => Pole::class,
                'choice_label' => 'name',
                'placeholder' => 'placeholder.pole',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => EventSearch::class,
            ])
            // ->add('export')
        ;
    }

    private function setData(OccupancySearch $search): OccupancySearch
    {
        $today = new \DateTime('today');

        if (null === $search->getStart()) {
            $search->setStart((clone $today)->modify('-1 day'));
        }
        if (null === $search->getEnd()) {
            $search->setEnd($today);
        }

        return $search;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OccupancySearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
