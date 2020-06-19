<?php

use Adianti\Database\TRecord;

/**
 * Telefones Active Record
 * @author  <Kásio Eduardo>
 */
class Telefone extends TRecord
{
  const TABLENAME = 'telefones';
  const PRIMARYKEY = 'id';
  const IDPOLICY =  'max'; // {max, serial}
  const CREATEDAT = 'created_at';
  const UPDATEDAT = 'updated_at';


  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
    parent::__construct($id, $callObjectLoad);
    parent::addAttribute('cliente_id');
    parent::addAttribute('tipo');
    parent::addAttribute('ddd');
    parent::addAttribute('numero');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }
}
