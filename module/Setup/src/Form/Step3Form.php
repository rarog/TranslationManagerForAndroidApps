<?php

namespace Setup\Form;

use Zend\Form\Form;

class Step3Form extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('formStep3');
        $this->setAttribute('method', 'post');
        $this->setHydrator(new \Zend\Hydrator\ClassMethods());

        $this->add([
            'name'       => 'output',
            'type'       => 'Textarea',
            'attributes' => [
                'id'       => 'output',
                'readonly' => 'readonly',
                'style'    => 'height: 400px',
            ],
            'options'    => [
                'column-size'      => 'sm-10',
                'label'            => _('Installation output'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add([
            'type'    => 'Csrf',
            'name'    => 'csrf_step3',
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
                'id'    => 'back',
                'type'  => 'button',
                'value' => _('Back'),
            ],
            'options'    => [
                'column-size'  => 'sm-10 col-sm-offset-2',
                'button-group' => 'group-1',
            ],
        ]);

        $this->add([
            'name'       => 'install_schema',
            'type'       => 'Button',
            'attributes' => [
                'id'    => 'installSchema',
                'type'  => 'button',
                'value' => _('Install schema'),
                'class' => 'btn-info',
            ],
            'options'    => [
                'button-group' => 'group-1',
            ],
        ]);

        $this->add([
            'name'       => 'next',
            'type'       => 'Button',
            'attributes' => [
                'id'    => 'next',
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
