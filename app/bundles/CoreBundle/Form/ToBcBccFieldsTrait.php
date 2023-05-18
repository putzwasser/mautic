<?php

namespace Mautic\CoreBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;

trait ToBcBccFieldsTrait
{
    protected function addToBcBccFields(FormBuilderInterface $builder, ?ToBcBccFieldsCustomConstraintDTO $toBcBccFieldsCustomConstraintDTO = null)
    {
        $builder->add(
            'to',
            TextType::class,
            [
                'label'      => 'mautic.core.send.email.to',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'mautic.core.optional',
                    'tooltip'     => 'mautic.core.send.email.to.multiple.addresses',
                ],
                'required'    => false,
                'constraints' => $toBcBccFieldsCustomConstraintDTO && $toBcBccFieldsCustomConstraintDTO->getToConstraint() ? $toBcBccFieldsCustomConstraintDTO->getToConstraint() : new Email(
                        [
                            'message' => 'mautic.core.email.required',
                        ]
                    ),
            ]
        );

        $builder->add(
            'cc',
            TextType::class,
            [
                'label'      => 'mautic.core.send.email.cc',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'mautic.core.optional',
                    'tooltip'     => 'mautic.core.send.email.to.multiple.addresses',
                ],
                'required'    => false,
                'constraints' => $toBcBccFieldsCustomConstraintDTO && $toBcBccFieldsCustomConstraintDTO->getCcConstraint() ? $toBcBccFieldsCustomConstraintDTO->getCcConstraint() : new Email(
                        [
                            'message' => 'mautic.core.email.required',
                        ]
                    ),
            ]
        );

        $builder->add(
            'bcc',
            TextType::class,
            [
                'label'      => 'mautic.core.send.email.bcc',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'mautic.core.optional',
                    'tooltip'     => 'mautic.core.send.email.to.multiple.addresses',
                ],
                'required'    => false,
                'constraints' => $toBcBccFieldsCustomConstraintDTO && $toBcBccFieldsCustomConstraintDTO->getBccConstraint() ? $toBcBccFieldsCustomConstraintDTO->getBccConstraint() : new Email(
                        [
                            'message' => 'mautic.core.email.required',
                        ]
                    ),
            ]
        );
    }
}
