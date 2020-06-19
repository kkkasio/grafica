<?php

use Adianti\Database\TRecord;

/**
 * Estados Active Record
 * @author  <your-name-here>
 */
class Estado extends TRecord
{
  const TABLENAME = 'estados';
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
    parent::addAttribute('nome');
    parent::addAttribute('uf');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }
}
