<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * UnidadeForm Form
 * @author  <your name here>
 */
class UnidadeForm extends TPage
{
  protected $form; // form

  /**
   * Form constructor
   * @param $param Request
   */
  public function __construct($param)
  {
    parent::__construct();

    parent::setTargetContainer('adianti_right_panel');

    // creates the form
    $this->form = new BootstrapFormBuilder('form_Unidade');
    $this->form->setFormTitle('Unidade');
    $this->form->setClientValidation(TRUE);


    // create the form fields
    $id = new TEntry('id');
    $nome = new TEntry('nome');
    $sigla = new TEntry('sigla');
    $ativo = new TRadioGroup('ativo');

    $ativo->addItems(['S' => 'Sim', 'N' => 'Não']);
    $ativo->setLayout('horizontal');
    $ativo->setValue('S');

    // add the fields
    $this->form->addFields([new TLabel('#')], [$id]);
    $this->form->addFields([new TLabel('Nome')], [$nome]);
    $this->form->addFields([new TLabel('Sigla')], [$sigla]);
    $this->form->addFields([new TLabel('Ativo')], [$ativo]);



    // set sizes
    $id->setSize('100%');
    $nome->setSize('100%');
    $sigla->setSize('100%');
    $ativo->setSize('100%');

    $nome->addValidation('Nome', new TRequiredValidator);
    $sigla->addValidation('Sigla', new TRequiredValidator);



    if (!empty($id)) {
      $id->setEditable(FALSE);
    }


    // create the form actions
    $btn = $this->form->addAction('Salvar Unidade', new TAction([$this, 'onSave']), 'fa:save');
    $btn->class = 'btn btn-sm btn-success';
    $this->form->addActionLink('Nova Unidade',  new TAction([$this, 'onEdit']), 'fa:eraser red');

    $btnClose = $this->form->addActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times white');
    $btnClose->class = 'btn btn-lg btn-danger';

    $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');

    // vertical box container
    $container = new TVBox;
    $container->style = 'width: 100%';
    // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $container->add($this->form);

    parent::add($container);
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

      $object = new Unidade;  // create an empty object
      $object->fromArray((array) $data); // load the object with data
      $object->store(); // save the object

      // get the generated id
      $data->id = $object->id;

      $this->form->setData($data); // fill form data
      TTransaction::close(); // close the transaction

      new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
    } catch (Exception $e) // in case of exception
    {
      new TMessage('error', $e->getMessage()); // shows the exception error message
      $this->form->setData($this->form->getData()); // keep form data
      TTransaction::rollback(); // undo all pending operations
    }
  }

  /**
   * Clear form data
   * @param $param Request
   */
  public function onClear($param)
  {
    $this->form->clear(TRUE);
  }

  /**
   * Load object to form data
   * @param $param Request
   */
  public function onEdit($param)
  {
    $this->form->setFormTitle('EDITAR UNIDADE');
    try {
      if (isset($param['key'])) {
        $key = $param['key'];  // get the parameter $key
        TTransaction::open('grafica'); // open a transaction
        $object = new Unidade($key); // instantiates the Active Record
        $this->form->setData($object); // fill the form
        TTransaction::close(); // close the transaction
      } else {
        $this->form->setFormTitle('CRIAR NOVA UNIDADE');
        $this->form->clear(TRUE);
      }
    } catch (Exception $e) // in case of exception
    {
      new TMessage('error', $e->getMessage()); // shows the exception error message
      TTransaction::rollback(); // undo all pending operations
    }
  }

  public static function onClose()
  {
    TScript::create("Template.closeRightPanel()");
  }
}
