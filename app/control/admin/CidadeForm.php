<?php

use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * CidadeForm Form
 * @author  <your name here>
 */
class CidadeForm extends TPage
{
  protected $form; // form

  /**
   * Form constructor
   * @param $param Request
   */

  use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods

  public function __construct($param)
  {
    parent::__construct();

    parent::setTargetContainer('adianti_right_panel');
    $this->setAfterSaveAction(new TAction(['CidadeList', 'onReload'], ['register_state' => 'true']));

    $this->setDatabase('grafica');              // defines the database
    $this->setActiveRecord('Cidade');     // defines the active record

    // creates the form
    $this->form = new BootstrapFormBuilder('form_Cidade');
    $this->form->setFormTitle('Cidade');
    $this->form->setFieldSizes('100%');

    // create the form fields
    $id = new TEntry('id');
    $nome = new TEntry('nome');
    $codigo_ibge = new TEntry('codigo_ibge');
    $estado_id = new TDBUniqueSearch('estado_id', 'grafica', 'Estado', 'id', 'nome');


    // add the fields
    $this->form->addFields([new TLabel('#'), $id]);
    $this->form->addFields([new TLabel('Nome'), $nome]);
    $this->form->addFields([new TLabel('Código Ibge'), $codigo_ibge]);
    $this->form->addFields([new TLabel('Estado'), $estado_id]);



    // set sizes
    $id->setSize('100%');
    $nome->setSize('100%');
    $codigo_ibge->setSize('100%');
    $estado_id->setSize('100%');



    if (!empty($id)) {
      $id->setEditable(FALSE);
    }

    /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
     **/

    // create the form actions
    $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
    $btn->class = 'btn btn-sm btn-primary';
    $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
    $this->form->addActionLink(_t('Close'),  new TAction([$this, 'onClose']), 'fa:times red');
    $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');

    // vertical box container
    $container = new TVBox;
    $container->style = 'width: 100%';
    // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $container->add($this->form);

    parent::add($container);
  }

  public static function onClose($param)
  {
    TScript::create("Template.closeRightPanel()");
  }
}
