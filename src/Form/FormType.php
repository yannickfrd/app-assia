<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;

class FormType extends AbstractType
{
    public function getchoices($const) 
    {
        foreach($const as $key => $value) {
            $output[$value] = $key;
        }
        return $output;
    }
}