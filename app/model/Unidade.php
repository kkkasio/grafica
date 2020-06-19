<?php

use Adianti\Database\TRecord;

/**
 * Unidade Active Record
 * @author  <Kásio Eduardo>
 */
class Unidade extends TRecord
{
  const TABLENAME = 'unidades';
  const PRIMARYKEY = 'id';
  const IDPOLICY =  'serial'; // {max, serial}
  const CREATEDAT = 'created_at';
  const UPDATEDAT = 'updated_at';


  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
    parent::__construct($id, $callObjectLoad);
    parent::addAttribute('nome');
    parent::addAttribute('sigla');
    parent::addAttribute('ativo');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }
}
