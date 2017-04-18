<?php

namespace Setup\Form;

use Zend\Form\Form;

class Step4Form extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('formStep4');
        $this->setAttribute('method', 'post');
        $this->setHydrator(new \Zend\Hydrator\ClassMethods());

        $this->add([
            'name'       => 'username',
            'type'       => 'Text',
            'attributes' => [
                'id' => 'username',
            ],
            'options'    => [
                'column-size'      => 'sm-10',
                'label'            => _('Username'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add(array(
            'name'       => 'email',
            'type'       => 'Email',
            'options'    => [
                'column-size'      => 'sm-10',
                'label'            => _('Email'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ));

        $this->add(array(
            'name'       => 'display_name',
            'type'       => 'Text',
            'options'    => [
                'column-size'      => 'sm-10',
                'label'            => _('Display name'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ));


        $this->add([
            'name'       => 'password',
            'type'       => 'Password',
            'attributes' => [
                'id' => 'password',
            ],
            'options'    => [
                'column-size'      => 'sm-10',
                'label'            => _('Password'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add([
            'name'       => 'passwordVerify',
            'type'       => 'Password',
            'attributes' => [
                'id' => 'password2',
            ],
            'options'    => [
                'column-size'      => 'sm-10',
                'label'            => _('Repeat Password'),
                'label_attributes' => [
                    'class' => 'col-sm-2',
                ],
            ],
        ]);

        $this->add([
            'type'    => 'Csrf',
            'name'    => 'csrf_step4',
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
            'name'       => 'create_user',
            'type'       => 'Button',
            'attributes' => [
                'id'    => 'createUser',
                'type'  => 'button',
                'value' => _('Create user'),
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
