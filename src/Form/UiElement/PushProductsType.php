<?php

declare(strict_types=1);

namespace App\Form\UiElement;

use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonAutocompleteChoiceType;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class PushProductsType extends AbstractType
{
    /**
     * @var TaxonRepositoryInterface
     */
    protected $taxonRepository;
    /**
     * @var TaxonFactoryInterface
     */
    protected $taxonFactory;

    public function __construct(TaxonRepositoryInterface $taxonRepository, TaxonFactoryInterface $taxonFactory)
    {
        $this->taxonRepository = $taxonRepository;
        $this->taxonFactory = $taxonFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Title',
            ])
            ->add('collection', TaxonAutocompleteChoiceType::class, [
                'required' => true,
                'label' => 'Taxon',
                'constraints' => [
                    new Assert\NotBlank([])
                ]
            ])
            ->add('count', TextType::class, [
                'required' => true,
                'label' => 'Number of product',
                'constraints' => [
                    new Assert\NotBlank([]),
                    new Assert\GreaterThan(0),
                ],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

                $data = $event->getData();

                $taxonCode = $data['collection'] ?? '';

                if ($taxonCode)
                    $data['collection'] = $this->taxonRepository->findOneBy(['code' => $taxonCode]);

                $event->setData($data);
            });

        $builder->resetModelTransformers();
    }
}
