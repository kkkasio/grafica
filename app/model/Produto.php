<?php

use Adianti\Database\TRecord;

/**
 * Produtos Active Record
 * @author  <Kásio Eduardos>
 */
class Produto extends TRecord
{
  const TABLENAME = 'produtos';
  const PRIMARYKEY = 'id';
  const IDPOLICY =  'serial'; // {max, serial}
  const CREATEDAT = 'created_at';
  const UPDATEDAT = 'updated_at';


  private $categoria;
  private $unidade;

  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
    parent::__construct($id, $callObjectLoad);
    parent::addAttribute('nome');
    parent::addAttribute('observacao');
    parent::addAttribute('unidade_id');
    parent::addAttribute('categoria_id');
    parent::addAttribute('valor_compra');
    parent::addAttribute('valor_minimo');
    parent::addAttribute('valor_venda');
    parent::addAttribute('ativo');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }


  /**
   * Method set_categoria
   * Sample of usage: $produtos->categoria = $object;
   * @param $object Instance of Categoria
   */
  public function set_categoria(Categoria $object)
  {
    $this->categoria = $object;
    $this->categoria_id = $object->id;
  }

  /**
   * Method get_categoria
   * Sample of usage: $produtos->categoria->attribute;
   * @returns Categoria instance
   */
  public function get_categoria()
  {
    // loads the associated object
    if (empty($this->categoria))
      $this->categoria = new Categoria($this->categoria_id);

    // returns the associated object
    return $this->categoria;
  }


  /**
   * Method set_unidade
   * Sample of usage: $produtos->unidade = $object;
   * @param $object Instance of Unidade
   */
  public function set_unidade(Unidade $object)
  {
    $this->unidade = $object;
    $this->unidade_id = $object->id;
  }

  /**
   * Method get_unidade
   * Sample of usage: $produtos->unidade->attribute;
   * @returns Unidade instance
   */
  public function get_unidade()
  {
    // loads the associated object
    if (empty($this->unidade))
      $this->unidade = new Unidade($this->unidade_id);

    // returns the associated object
    return $this->unidade;
  }
}
