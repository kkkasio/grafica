<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Util\TDropDown;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * ProdutosList Listing
 * @author  <your name here>
 */
class ProdutosList extends TPage
{
  protected $form;     // registration form
  protected $datagrid; // listing
  protected $pageNavigation;
  protected $formgrid;
  protected $deleteButton;

  use Adianti\base\AdiantiStandardListTrait;

  /**
   * Page constructor
   */
  public function __construct()
  {
    parent::__construct();

    $this->setDatabase('grafica');            // defines the database
    $this->setActiveRecord('Produto');   // defines the active record
    $this->setDefaultOrder('id', 'asc');         // defines the default order
    $this->setLimit(10);
    // $this->setCriteria($criteria) // define a standard filter

    $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
    $this->addFilterField('unidade_id', '=', 'unidade_id'); // filterField, operator, formField
    $this->addFilterField('categoria_id', '=', 'categoria_id'); // filterField, operator, formField
    $this->addFilterField('valor_compra', 'like', 'valor_compra'); // filterField, operator, formField
    $this->addFilterField('valor_venda', 'like', 'valor_venda'); // filterField, operator, formField
    $this->addFilterField('ativo', 'like', 'ativo'); // filterField, operator, formField

    // creates the form
    $this->form = new BootstrapFormBuilder('form_search_Produtos');
    $this->form->setFormTitle('Produtos');


    // create the form fields
    $nome = new TEntry('nome');
    $unidade_id = new TDBCombo('unidade_id', 'grafica', 'Unidade', 'id', 'nome');
    $categoria_id = new TDBCombo('categoria_id', 'grafica', 'Categoria', 'id', 'nome');
    $ativo = new TRadioGroup('ativo');

    $ativo->addItems(['Y' => 'Sim', 'N' => 'Não']);
    $ativo->setLayout('horizontal');
    $ativo->setValue('Y');

    $unidade_id->enableSearch();
    $categoria_id->enableSearch();



    // add the fields
    $this->form->addFields([new TLabel('Nome')], [$nome]);
    $this->form->addFields([new TLabel('Unidade')], [$unidade_id]);
    $this->form->addFields([new TLabel('Categoria')], [$categoria_id]);
    $this->form->addFields([new TLabel('Ativo')], [$ativo]);


    // set sizes
    $nome->setSize('100%');
    $unidade_id->setSize('100%');
    $categoria_id->setSize('100%');
    $ativo->setSize('100%');


    // keep the form filled during navigation with session data
    $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

    // add the search form actions
    $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
    $btn->class = 'btn btn-sm btn-primary';
    $this->form->addActionLink(_t('New'), new TAction(['ProdutosForm', 'onEdit']), 'fa:plus green');

    // creates a Datagrid
    $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
    $this->datagrid->style = 'width: 100%';
    $this->datagrid->datatable = 'true';
    // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


    // creates the datagrid columns
    $column_id = new TDataGridColumn('id', '#', 'right');
    $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
    $column_observacao = new TDataGridColumn('observacao', 'Observação', 'left');
    $column_unidade_id = new TDataGridColumn('{unidade->nome} ({unidade->sigla})', 'Unidade', 'right');
    $column_categoria_id = new TDataGridColumn('categoria->nome', 'Categoria', 'right');
    $column_valor_compra = new TDataGridColumn('valor_compra', 'Valor de Compra', 'right');
    $column_valor_minimo = new TDataGridColumn('valor_minimo', 'Valor Minimo', 'right');
    $column_valor_venda = new TDataGridColumn('valor_venda', 'Valor de Venda', 'right');
    $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'left');
    $column_created_at = new TDataGridColumn('created_at', 'Criado em', 'left');
    $column_updated_at = new TDataGridColumn('updated_at', 'Última Atualização', 'left');

    $column_id->setTransformer(function ($value, $object, $row) {
      if ($object->ativo == 'N') {
        $row->style = 'color: silver';
      }

      return $value;
    });


    $column_valor_compra->setTransformer(function ($value) {
      if ($value) {
        return "R$ " . number_format($value, '2', ',', '.');
      }
    });

    $column_valor_minimo->setTransformer(function ($value) {
      if ($value) {
        return "R$ " . number_format($value, '2', ',', '.');
      }
    });

    $column_valor_venda->setTransformer(function ($value) {
      if ($value) {
        return "R$ " . number_format($value, '2', ',', '.');
      }
    });

    $column_ativo->setTransformer(function ($value, $object, $row) {

      if ($value == 'S') {
        $div = new TElement('span');
        $div->class = "label label-success";
        $div->style = "text-shadow:none; font-size:13px";
        $div->add('Sim');
        return $div;
      } else if ($value == 'N') {
        $div = new TElement('span');
        $div->class = "label label-danger";
        $div->style = "text-shadow:none; font-size:13px";
        $div->add('Não');
        return $div;
      }
    });


    $column_created_at->setTransformer(function ($value) {
      if ($value) {
        try {
          $date = new DateTime($value);
          return $date->format('d/m/Y h:m');
        } catch (Exception $e) {
          return $value;
        }
      }
      return $value;
    });

    $column_updated_at->setTransformer(function ($value) {
      if ($value) {
        try {
          $date = new DateTime($value);
          return $date->format('d/m/Y hh:m');
        } catch (Exception $e) {
          return $value;
        }
      }
      return $value;
    });


    // add the columns to the DataGrid
    $this->datagrid->addColumn($column_id);
    $this->datagrid->addColumn($column_nome);
    $this->datagrid->addColumn($column_observacao);
    $this->datagrid->addColumn($column_unidade_id);
    $this->datagrid->addColumn($column_categoria_id);
    $this->datagrid->addColumn($column_valor_compra);
    $this->datagrid->addColumn($column_valor_minimo);
    $this->datagrid->addColumn($column_valor_venda);
    $this->datagrid->addColumn($column_ativo);
    $this->datagrid->addColumn($column_created_at);
    $this->datagrid->addColumn($column_updated_at);


    // creates the datagrid column actions
    $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
    $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
    $column_observacao->setAction(new TAction([$this, 'onReload']), ['order' => 'observacao']);
    $column_unidade_id->setAction(new TAction([$this, 'onReload']), ['order' => 'unidade_id']);
    $column_categoria_id->setAction(new TAction([$this, 'onReload']), ['order' => 'categoria_id']);
    $column_ativo->setAction(new TAction([$this, 'onReload']), ['order' => 'ativo']);


    $action1 = new TDataGridAction(['ProdutosForm', 'onEdit'], ['id' => '{id}']);
    $action2 = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);
    $action3 = new TDataGridAction([$this, 'toggleAtivo'], ['id' => '{id}']);

    $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
    $this->datagrid->addAction($action2, _t('Delete'), 'far:trash-alt red');
    $this->datagrid->addAction($action3, 'Ativar/Desativar',   'fa:power-off orange');

    // create the datagrid model
    $this->datagrid->createModel();

    // creates the page navigation
    $this->pageNavigation = new TPageNavigation;
    $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

    $panel = new TPanelGroup('', 'white');
    $panel->add($this->datagrid);
    $panel->addFooter($this->pageNavigation);

    // header actions
    $dropdown = new TDropDown(_t('Export'), 'fa:list');
    $dropdown->setPullSide('right');
    $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
    $dropdown->addAction(_t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static' => '1']), 'fa:table blue');
    $dropdown->addAction(_t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static' => '1']), 'far:file-pdf red');
    $panel->addHeaderWidget($dropdown);

    // vertical box container
    $container = new TVBox;
    $container->style = 'width: 100%';
    // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $container->add($this->form);
    $container->add($panel);

    parent::add($container);
  }

  public function toggleAtivo($param)
  {
    try {
      TTransaction::open('grafica');
      $produto = Produto::find($param['id']);

      if ($produto instanceof Produto) {
        $produto->ativo = $produto->ativo == 'S' ? 'N' : 'S';
        $produto->store();
      }
      TTransaction::close();

      $this->onReload($param);
    } catch (Exception $e) {
      new TMessage('error', $e->getMessage());
      TTransaction::rollback();
    }
  }
}
