<?php

namespace Setup\Form;
 
use Zend\Form\Form;
 
class Step2Form extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Step2');
        $this->setAttribute('method', 'post');
        $this->setHydrator(new \Zend\Hydrator\ClassMethods());

        $this->add([
            'name' => 'driver',
            'type' => 'Select',
            'attributes' => [
                'id' => 'driver',
            ],
            'options' => [
                'column-size' => 'sm-10',
                'label' => _('Database driver'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add([
            'name' => 'database',
            'type' => 'Text',
            'attributes' => [
                'id' => 'database',
            ],
            'options' => [
                'column-size' => 'sm-10',
                'label' => _('Database name'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add([
            'name' => 'hostname',
            'type' => 'Text',
            'attributes' => [
                'id' => 'host',
            ],
            'options' => [
                'column-size' => 'sm-10',
                'label' => _('Hostname'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add([
            'type' => 'Csrf',
            'name' => 'csrf_step2',
            'options' => [
                'csrf_options' => [
                    'timeout' => null,
                ]
            ]
        ]);

        $this->add([
        		'name' => 'previous',
        		'type' => 'Button',
        		'attributes' => [
        				'type'  => 'button',
        				'value' => _('Previous'),
        		],
        		'options' => [
        				'column-size' => 'sm-10 col-sm-offset-2',
        				'button-group' => 'group-1',
        		],
        ]);
        
        $this->add([
        		'name' => 'next',
        		'type'  => 'Button',
        		'attributes' => [
        				'type'  => 'submit',
        				'value' => _('Next'),
        				'class' => 'btn-primary',
        		],
        		'options' => [
        				'button-group' => 'group-1',
        		],
        ]);
    }
}