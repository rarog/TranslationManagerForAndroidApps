<?php

namespace Setup\Form;
 
use Zend\Form\Form;
 
class Step1Form extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Step1');
        $this->setAttribute('method', 'post');
        $this->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());

        $this->add(array(
            'name' => 'setup_language',
            'type' => 'Select',
            'attributes' => array(
                'id' => 'setup_language',
            ),
            'options' => array(
                'column-size' => 'sm-10',
                'label' => _('Setup language'),
                'label_attributes' => array(
                    'class' => 'col-sm-2',
                ),
            ),
        ));

        $this->add(array(
            'type' => 'Csrf',
            'name' => 'csrf_step1',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => null,
                )
            )
        ));

        $this->add(array(
            'name' => 'previous',
            'type' => 'Button',
            'attributes' => array(
                'type'  => 'button',
                'value' => _('Previous'),
                'class' => 'disabled',
            ),
            'options' => array(
                'column-size' => 'sm-10 col-sm-offset-2',
                'button-group' => 'group-1',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type'  => 'Button',
            'attributes' => array(
                'type'  => 'submit',
                'value' => _('Next'),
                'class' => 'btn-primary',
            ),
            'options' => array(
                'button-group' => 'group-1',
            ),
        ));
    }
}
