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
            'name' => 'username',
            'type' => 'Text',
            'attributes' => [
                'id' => 'username',
            ],
            'options' => [
                'column-size' => 'sm-10',
                'label' => _('Username'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add([
            'name' => 'password',
            'type' => 'Text',
            'attributes' => [
                'id' => 'password',
            ],
            'options' => [
                'column-size' => 'sm-10',
                'label' => _('Password'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add([
            'name' => 'hostname',
            'type' => 'Text',
            'attributes' => [
                'id' => 'hostname',
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
            'name' => 'port',
            'type' => 'Number',
            'attributes' => [
                'id' => 'port',
            ],
            'options' => [
                'column-size' => 'sm-10',
                'label' => _('Port'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add([
            'name' => 'charset',
            'type' => 'Text',
            'attributes' => [
                'id' => 'charset',
            ],
            'options' => [
                'column-size' => 'sm-10',
                'label' => _('Charset'),
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
            'name' => 'test_connection',
            'type'  => 'Button',
            'attributes' => [
                'id'    => 'testConnection',
                'type'  => 'button',
                'value' => _('Test connection'),
                'class' => 'btn-info',
            ],
            'options' => [
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
