<?php

use Adianti\Widget\Base\TElement;
use Adianti\Widget\Form\AdiantiWidgetInterface;
use Adianti\Widget\Form\TField;
use Adianti\Widget\Util\TImage;

/**
 * TSlim Container
 * Copyright (c) 2006-2010 Nataniel Rabaioli
 * @author  Nataniel Rabaioli
 * @version 2.0, 2007-08-01
 */
class TSlim extends TField implements AdiantiWidgetInterface
{
  protected $value;
  public $container;
  /**
   * Class Constructor
   */
  public function __construct($name)
  {
    parent::__construct($name);
    //$this->id = 'slim_' . uniqid();

    $this->tag->type = 'file';

    $this->container = new TElement('div');
    $this->container->class = 'slim';

    $this->setDataProperties([
      'size' => '640,640',
      'label' => 'Upload imagem',
      'button-confirm-label' => 'Confirmar',
      'button-confirm-title' => 'Confirmar',
      'button-cancel-label'  => 'Cancelar',
      'button-cancel-title'  => 'Cancelar',
      'button-edit-label'    => 'Editar',
      'button-edit-title'    => 'Editar',
      'button-remove-label'  => 'Remover',
      'button-remove-title'  => 'Remover',
      'button-rotate-label'  => 'Girar',
      'button-rotate-title'  => 'Girar'
    ]);
  }


  public function setDataProperties($props)
  {
    foreach ($props as $prop => $val) {
      $this->container->{"data-{$prop}"} = $val;
    }
  }

  /**
   * Shows the widget at the screen
   */
  public function show()
  {
    $this->container->add($this->tag);

    if ($this->value)
      $this->container->add(new TImage($this->value));

    $js = TScript::create('', false);
    $js->src = 'app/lib/slim/js/slim.kickstart.min.js';
    $this->container->add($js);

    $this->container->show();
  }
}
