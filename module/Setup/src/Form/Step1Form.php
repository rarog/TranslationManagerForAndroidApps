<?php

namespace Setup\Form;

use Zend\Form\Form;

class Step1Form extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('formStep1');
        $this->setAttribute('method', 'post');
        $this->setHydrator(new \Zend\Hydrator\ClassMethods());

        $this->add([
            'name'       => 'setup_language',
            'type'       => 'Select',
            'attributes' => [
                'id' => 'setup_language',
            ],
            'options'    => [
                'column-size'      => 'sm-10',
                'label'            => _('Setup language'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add([
            'type'    => 'Csrf',
            'name'    => 'csrf_step1',
            'options' => [
                'csrf_options' => [
                    'timeout' => null,
                ]
            ]
        ]);

        $this->add([
            'name'       => 'back',
            'type'       => 'Button',
            'attributes' => [
                'type'  => 'button',
                'value' => _('Back'),
                'class' => 'disabled',
            ],
            'options'    => [
                'column-size'  => 'sm-10 col-sm-offset-2',
                'button-group' => 'group-1',
            ],
        ]);

        $this->add([
            'name'       => 'next',
            'type'       => 'Button',
            'attributes' => [
                'type'  => 'submit',
                'value' => _('Next'),
                'class' => 'btn-primary',
            ],
            'options'    => [
                'button-group' => 'group-1',
            ],
        ]);
    }
}
