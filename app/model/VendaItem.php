<?php

use Adianti\Database\TRecord;

/**
 * VendaItem Active Record
 * @author  <your-name-here>
 */
class VendaItem extends TRecord
{
  const TABLENAME  = 'venda_item';
  const PRIMARYKEY = 'id';
  const IDPOLICY   = 'max'; // {max, serial}
  const CREATEDAT  = 'created_at';
  const UPDATEDAT  = 'updated_at';


  private $produto;

  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
    parent::__construct($id, $callObjectLoad);
    parent::addAttribute('venda_id');
    parent::addAttribute('produto_id');
    parent::addAttribute('altura');
    parent::addAttribute('largura');
    parent::addAttribute('area');
    parent::addAttribute('quantidade');
    parent::addAttribute('preco');
    parent::addAttribute('total');
    parent::addAttribute('arte');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }

  /**
   * Method set_produto
   * Sample of usage: $venda_item->produto = $object;
   * @param $object Instance of Produto
   */
  public function set_produto(Produto $object)
  {
    $this->produto = $object;
    $this->produto_id = $object->id;
  }

  /**
   * Method get_produto
   * Sample of usage: $venda_item->produto->attribute;
   * @returns Produto instance
   */
  public function get_produto()
  {
    // loads the associated object
    if (empty($this->produto))
      $this->produto = new Produto($this->produto_id);

    // returns the associated object
    return $this->produto;
  }
}
