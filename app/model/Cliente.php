<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRecord;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;

/**
 * Clientes Active Record
 * @author  <Kásio Eduardo>
 */
class Cliente extends TRecord
{
  const TABLENAME = 'clientes';
  const PRIMARYKEY = 'id';
  const IDPOLICY =  'max';
  const CREATEDAT = 'created_at';
  const UPDATEDAT = 'updated_at';


  private $telefones;
  private $cliente;
  /**
   * Constructor method
   */
  public function __construct($id = NULL, $callObjectLoad = TRUE)
  {
    parent::__construct($id, $callObjectLoad);
    parent::addAttribute('nome');
    parent::addAttribute('email');
    parent::addAttribute('cep');
    parent::addAttribute('tipo');
    parent::addAttribute('logradouro');
    parent::addAttribute('numero');
    parent::addAttribute('complemento');
    parent::addAttribute('bairro');
    parent::addAttribute('estado_id');
    parent::addAttribute('cidade_id');
    parent::addAttribute('created_at');
    parent::addAttribute('updated_at');
  }

  public function clearParts()
  {
    $this->telefones = array();
  }

  public function getTelefone()
  {
    return $this->telefones;
  }

  public function addTelefone(Telefone $object)
  {
    $this->telefones[] = $object;
  }

  /**
   * Load the object and its aggregates
   * @param $id object ID
   */
  public function load($id)
  {

    $this->contacts = parent::loadComposite('Telefone', 'cliente_id', $id);

    // load the object itself
    return parent::load($id);
  }

  /**
   * Store the object and its aggregates
   */
  public function store()
  {
    parent::store();

    parent::saveComposite('Telefone', 'cliente_id', $this->id, $this->telefones);
  }

  /**
   * Delete the object and its aggregates
   * @param $id object ID
   */
  public function delete($id = NULL)
  {
    $id = isset($id) ? $id : $this->id;
    parent::deleteComposite('Telefone', 'cliente_id', $id);

    // delete the object itself
    parent::delete($id);
  }

  public function get_cliente()
  {
    if (empty($this->cidade))
      if ($this->tipo === 'Física') {
        $this->cliente = new PessoaFisica($this->id);
      } else {
        $this->cliente = new PessoaJuridica($this->id);
      }

    return $this->cliente;
  }

  public function getVendas()
  {
    /* $criteria = new TCriteria;
    $criteria->add(new TFilter('cliente_id', '=', $this->id));

    $repository = new TRepository('Venda');

    $vendas = $repository->load($criteria);

    return $vendas;*/

    return Venda::where('cliente_id', '=', $this->id)->load();
  }
}
