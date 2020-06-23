<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Control\TWindow;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TNumeric;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * ProdutosForm Registration
 * @author  <your name here>
 */
class ProdutosForm extends TWindow
{
  protected $form; // form

  use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods

  /**
   * Class constructor
   * Creates the page and the registration form
   */
  function __construct()
  {
    parent::__construct();


    $this->setDatabase('grafica');              // defines the database
    $this->setActiveRecord('Produto');     // defines the active record

    // creates the form
    $this->form = new BootstrapFormBuilder('form_Produtos');
    $this->form->setFormTitle('Produtos');
    $this->form->setClientValidation(TRUE);


    $filtro = new TCriteria;
    $filtro->add(new TFilter('ativo', '=', 'Y'));

    // create the form fields
    $id = new TEntry('id');
    $nome = new TEntry('nome');
    $observacao = new TEntry('observacao');
    $unidade_id = new TDBCombo('unidade_id', 'grafica', 'Unidade', 'id', 'nome', 'nome', $filtro);


    $categoria_id = new TDBCombo('categoria_id', 'grafica', 'Categoria', 'id', 'nome', 'nome', $filtro);
    $valor_compra = new TNumeric('valor_compra', 2, ',', '.', true);
    $valor_minimo = new TNumeric('valor_minimo', 2, ',', '.', true);
    $valor_venda = new TNumeric('valor_venda', 2, ',', '.', true);
    $ativo = new TRadioGroup('ativo');

    $ativo->addItems(['Y' => 'Sim', 'N' => 'Não']);
    $ativo->setLayout('horizontal');
    $ativo->setValue('Y');

    $unidade_id->enableSearch();
    $categoria_id->enableSearch();

    $categoria_id->setChangeAction(new TAction([$this, 'onChangeCategoria']));


    // add the fields
    $this->form->addFields([new TLabel('Nome')], [$nome], [new TLabel('#')], [$id]);

    $this->form->addFields([new TLabel('Observação')], [$observacao]);
    $this->form->addFields([new TLabel('Unidade')], [$unidade_id], [new TLabel('Categoria')], [$categoria_id]);
    $this->form->addFields([new TLabel('Valor Compra')], [$valor_compra], [new TLabel('Valor Minimo')], [$valor_minimo], [new TLabel('Valor Venda')], [$valor_venda]);
    $this->form->addFields([new TLabel('Ativo')], [$ativo]);



    // set sizes
    $id->setSize('100%');
    $nome->setSize('100%');
    $observacao->setSize('100%');
    $unidade_id->setSize('100%');
    $categoria_id->setSize('100%');
    $valor_compra->setSize('100%');
    $valor_minimo->setSize('100%');
    $valor_venda->setSize('100%');
    $ativo->setSize('100%');


    $nome->addValidation('Nome', new TRequiredValidator);
    $unidade_id->addValidation('Unidade Id', new TRequiredValidator);
    $categoria_id->addValidation('Categoria Id', new TRequiredValidator);
    $valor_compra->addValidation('Valor Compra', new TRequiredValidator);
    $valor_minimo->addValidation('Valor Minimo', new TRequiredValidator);
    $valor_venda->addValidation('Valor Venda', new TRequiredValidator);


    if (!empty($id)) {
      $id->setEditable(FALSE);
    }


    // create the form actions
    $btn = $this->form->addAction('Salvar Produto', new TAction([$this, 'onSave']), 'fa:save');
    $btn->class = 'btn btn-sm btn-success';
    $this->form->addActionLink('Novo Produto',  new TAction([$this, 'onEdit']), 'fa:eraser red');
    $btnClose = $this->form->addActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times white');
    $btnClose->class = 'btn btn-lg btn-danger';

    // vertical box container
    $container = new TVBox;
    $container->style = 'width: 100%';
    // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $container->add($this->form);

    parent::add($container);
  }

  public function onEdit($param)
  {
    $this->form->setFormTitle('EDITAR PRODUTOS');
    try {
      if (isset($param['key'])) {
        $key = $param['key'];
        TTransaction::open('grafica');
        $object = new Produto($key);
        $this->form->setData($object);
        TTransaction::close();
      } else {
        $this->form->setFormTitle('CRIAR NOVO PRODUTO');
        $this->form->clear(TRUE);
      }
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
      TTransaction::rollback();
    }
  }


  /**
   * Save form data
   * @param $param Request
   */
  public function onSave($param)
  {
    try {
      TTransaction::open('grafica'); // open a transaction

      $this->form->validate(); // validate form data
      $data = $this->form->getData(); // get form data as array

      $object = new Produto;  // create an empty object
      $object->fromArray((array) $data); // load the object with data
      $object->store(); // save the object

      // get the generated id
      $data->id = $object->id;

      $this->form->setData($data); // fill form data
      TTransaction::close(); // close the transaction

      $action = new TAction(array('ProdutosList', 'onReload'));
      new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $action);
    } catch (Exception $e) // in case of exception
    {
      new TMessage('error', $e->getMessage()); // shows the exception error message
      $this->form->setData($this->form->getData()); // keep form data
      TTransaction::rollback(); // undo all pending operations
    }
  }

  public static function onClose()
  {
    parent::closeWindow();
  }
  public static function onChangeCategoria($param)
  {
    var_dump($param);
  }
}
