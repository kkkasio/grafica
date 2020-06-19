<?php

use Adianti\Database\TRecord;

/**
 * Cidades Active Record
 * @author  <your-name-here>
 */
class Cidade extends TRecord
{
  const TABLENAME = 'cidades';
  const PRIMARYKEY = 'id';
  const IDPOLICY =  'max'; // {max, serial}
  const CREATEDAT = 'created_at';
  const UPDATEDAT = 'updated_at';


  private $estado;

  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
    parent::__construct($id, $callObjectLoad);
    parent::addAttribute('nome');
    parent::addAttribute('codigo_ibge');
    parent::addAttribute('estado_id');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }


  /**
   * Method set_estados
   * Sample of usage: $cidades->estados = $object;
   * @param $object Instance of Estados
   */
  public function set_estado(Estado $object)
  {
    $this->estado = $object;
    $this->estado_id = $object->id;
  }

  /**
   * Method get_estados
   * Sample of usage: $cidades->estados->attribute;
   * @returns Estados instance
   */
  public function get_estado()
  {
    // loads the associated object
    if (empty($this->estado))
      $this->estado = new Estado($this->estado_id);

    // returns the associated object
    return $this->estado;
  }
}
