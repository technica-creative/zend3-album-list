<?php

namespace Album\Form;

use Zend\Form\Form;

/*
Within the constructor of AlbumForm we do several things. First, we set the name of the form as we call the parent's constructor. Then, we create four form elements: the id, title, artist, and submit button. For each item we set various attributes and options, including the label to be displayed

HTML forms can be sent using POST and GET. zend-form defaults to POST; therefore you don't have to be explicit in setting this option. If you want to change it to GET however, set the method attribute in the constructor:

$this->setAttribute('method', 'GET');
*/
class AlbumForm extends Form
{
    public function __construct($name = null)
    {
        // We will ignore the name provided to the constructor
        parent::__construct('album');

        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);
        $this->add([
            'name' => 'title',
            'type' => 'text',
            'options' => [
                'label' => 'Title',
            ],
        ]);
        $this->add([
            'name' => 'artist',
            'type' => 'text',
            'options' => [
                'label' => 'Artist',
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Go',
                'id'    => 'submitbutton',
            ],
        ]);
    }
}
