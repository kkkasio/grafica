<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * VendaList Listing
 * @author  <your name here>
 */
class VendasList extends TPage
{
	private $form; // form
	private $datagrid; // listing
	private $pageNavigation;
	private $formgrid;
	private $loaded;
	private $deleteButton;

	/**
	 * Class constructor
	 * Creates the page, the form and the listing
	 */
	public function __construct()
	{
		parent::__construct();

		// creates the form
		$this->form = new BootstrapFormBuilder('form_search_Venda');
		$this->form->setFormTitle('Venda');


		// create the form fields
		$numero = new TEntry('numero');
		$created_at = new TDate('created_at');
		$cliente_id = new TDBUniqueSearch('cliente_id', 'grafica', 'Cliente', 'id', 'nome');
		$vendedor_id = new TDBUniqueSearch('vendedor_id', 'grafica', 'SystemUserUnit', 'id', 'system_user_id');
		$forma_pagamento = new TCombo('forma_pagamento');
		$status = new TCombo('status');

		$formas_pagamento = ['Dinheiro', 'Crédito', 'Débito', 'Boleto'];
		$forma_pagamento->addItems($formas_pagamento);


		// add the fields
		$this->form->addFields([new TLabel('Número')], [$numero], [new TLabel('Data')], [$created_at]);
		$this->form->addFields([new TLabel('Cliente')], [$cliente_id], [new TLabel('Vendedor')], [$vendedor_id]);
		$this->form->addFields([new TLabel('Forma Pagamento')], [$forma_pagamento], [new TLabel('Status')], [$status]);



		// set sizes
		$numero->setSize('100%');
		$created_at->setSize('100%');
		$cliente_id->setSize('100%');
		$vendedor_id->setSize('100%');
		$forma_pagamento->setSize('100%');
		$status->setSize('100%');


		// keep the form filled during navigation with session data
		$this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

		// add the search form actions
		$btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
		$btn->class = 'btn btn-sm btn-primary';
		$this->form->addActionLink(_t('New'), new TAction(['VendaForm', 'onEdit']), 'fa:plus green');

		// creates a Datagrid
		$this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
		$this->datagrid->style = 'width: 100%';
		$this->datagrid->datatable = 'true';
		// $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


		// creates the datagrid columns
		$column_id = new TDataGridColumn('id', '#', 'right');
		$column_numero = new TDataGridColumn('numero', 'Número', 'left');
		$column_created_at = new TDataGridColumn('created_at', 'Data', 'left');
		$column_cliente_id = new TDataGridColumn('cliente->nome', 'Cliente', 'left');
		$column_vendedor_id = new TDataGridColumn('vendedor->name', 'Vendedor', 'right');
		$column_valor_real = new TDataGridColumn('valor_real', 'Valor', 'left');
		$column_desconto = new TDataGridColumn('desconto', 'Desconto', 'right');
		$column_forma_pagamento = new TDataGridColumn('forma_pagamento', 'Forma Pagamento', 'left');
		$column_status = new TDataGridColumn('status', 'Status', 'left');


		// add the columns to the DataGrid
		$this->datagrid->addColumn($column_id);
		$this->datagrid->addColumn($column_numero);
		$this->datagrid->addColumn($column_created_at);
		$this->datagrid->addColumn($column_cliente_id);
		$this->datagrid->addColumn($column_vendedor_id);
		$this->datagrid->addColumn($column_valor_real);
		$this->datagrid->addColumn($column_desconto);
		$this->datagrid->addColumn($column_forma_pagamento);
		$this->datagrid->addColumn($column_status);


		$column_created_at->setTransformer(function ($value) {
			return TDate::date2br($value);
		});

		$column_valor_real->setTransformer(function ($value) {
			return 'R$ ' . number_format($value, 2, ',', '.');
		});

		$column_desconto->setTransformer(function ($value) {
			return $value . '%';
		});



		// creates the datagrid column actions
		$column_cliente_id->setAction(new TAction([$this, 'onReload']), ['order' => 'cliente_id']);


		/*$action1 = new TDataGridAction(['VendaForm', 'onEdit'], ['id' => '{id}']);
		$action2 = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);

		$this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
		$this->datagrid->addAction($action2, _t('Delete'), 'far:trash-alt red');*/

		// create the datagrid model
		$this->datagrid->createModel();

		// creates the page navigation
		$this->pageNavigation = new TPageNavigation;
		$this->pageNavigation->setAction(new TAction([$this, 'onReload']));
		$this->pageNavigation->setWidth($this->datagrid->getWidth());

		// vertical box container
		$container = new TVBox;
		$container->style = 'width: 100%';
		// $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
		$container->add($this->form);
		$container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));

		parent::add($container);
	}


	/**
	 * Register the filter in the session
	 */
	public function onSearch()
	{
		// get the search form data
		$data = $this->form->getData();

		// clear session filters
		TSession::setValue(__CLASS__ . '_filter_numero',   NULL);
		TSession::setValue(__CLASS__ . '_filter_created_at',   NULL);
		TSession::setValue(__CLASS__ . '_filter_cliente_id',   NULL);
		TSession::setValue(__CLASS__ . '_filter_vendedor_id',   NULL);
		TSession::setValue(__CLASS__ . '_filter_forma_pagamento',   NULL);
		TSession::setValue(__CLASS__ . '_filter_status',   NULL);

		if (isset($data->numero) and ($data->numero)) {
			$filter = new TFilter('numero', 'like', "%{$data->numero}%"); // create the filter
			TSession::setValue(__CLASS__ . '_filter_numero',   $filter); // stores the filter in the session
		}


		if (isset($data->created_at) and ($data->created_at)) {
			$filter = new TFilter('created_at', 'like', "%{$data->created_at}%"); // create the filter
			TSession::setValue(__CLASS__ . '_filter_created_at',   $filter); // stores the filter in the session
		}


		if (isset($data->cliente_id) and ($data->cliente_id)) {
			$filter = new TFilter('cliente_id', '=', $data->cliente_id); // create the filter
			TSession::setValue(__CLASS__ . '_filter_cliente_id',   $filter); // stores the filter in the session
		}


		if (isset($data->vendedor_id) and ($data->vendedor_id)) {
			$filter = new TFilter('vendedor_id', 'like', "%{$data->vendedor_id}%"); // create the filter
			TSession::setValue(__CLASS__ . '_filter_vendedor_id',   $filter); // stores the filter in the session
		}


		if (isset($data->forma_pagamento) and ($data->forma_pagamento)) {
			$filter = new TFilter('forma_pagamento', 'like', "%{$data->forma_pagamento}%"); // create the filter
			TSession::setValue(__CLASS__ . '_filter_forma_pagamento',   $filter); // stores the filter in the session
		}


		if (isset($data->status) and ($data->status)) {
			$filter = new TFilter('status', 'like', "%{$data->status}%"); // create the filter
			TSession::setValue(__CLASS__ . '_filter_status',   $filter); // stores the filter in the session
		}


		// fill the form with data again
		$this->form->setData($data);

		// keep the search data in the session
		TSession::setValue(__CLASS__ . '_filter_data', $data);

		$param = array();
		$param['offset']    = 0;
		$param['first_page'] = 1;
		$this->onReload($param);
	}

	/**
	 * Load the datagrid with data
	 */
	public function onReload($param = NULL)
	{
		try {
			// open a transaction with database 'grafica'
			TTransaction::open('grafica');

			// creates a repository for Venda
			$repository = new TRepository('Venda');
			$limit = 10;
			// creates a criteria
			$criteria = new TCriteria;

			// default order
			if (empty($param['order'])) {
				$param['order'] = 'id';
				$param['direction'] = 'asc';
			}
			$criteria->setProperties($param); // order, offset
			$criteria->setProperty('limit', $limit);


			if (TSession::getValue(__CLASS__ . '_filter_numero')) {
				$criteria->add(TSession::getValue(__CLASS__ . '_filter_numero')); // add the session filter
			}


			if (TSession::getValue(__CLASS__ . '_filter_created_at')) {
				$criteria->add(TSession::getValue(__CLASS__ . '_filter_created_at')); // add the session filter
			}


			if (TSession::getValue(__CLASS__ . '_filter_cliente_id')) {
				$criteria->add(TSession::getValue(__CLASS__ . '_filter_cliente_id')); // add the session filter
			}


			if (TSession::getValue(__CLASS__ . '_filter_vendedor_id')) {
				$criteria->add(TSession::getValue(__CLASS__ . '_filter_vendedor_id')); // add the session filter
			}


			if (TSession::getValue(__CLASS__ . '_filter_forma_pagamento')) {
				$criteria->add(TSession::getValue(__CLASS__ . '_filter_forma_pagamento')); // add the session filter
			}


			if (TSession::getValue(__CLASS__ . '_filter_status')) {
				$criteria->add(TSession::getValue(__CLASS__ . '_filter_status')); // add the session filter
			}


			// load the objects according to criteria
			$objects = $repository->load($criteria, FALSE);

			if (is_callable($this->transformCallback)) {
				call_user_func($this->transformCallback, $objects, $param);
			}

			$this->datagrid->clear();
			if ($objects) {
				// iterate the collection of active records
				foreach ($objects as $object) {
					// add the object inside the datagrid
					$this->datagrid->addItem($object);
				}
			}

			// reset the criteria for record count
			$criteria->resetProperties();
			$count = $repository->count($criteria);

			$this->pageNavigation->setCount($count); // count of records
			$this->pageNavigation->setProperties($param); // order, page
			$this->pageNavigation->setLimit($limit); // limit

			// close the transaction
			TTransaction::close();
			$this->loaded = true;
		} catch (Exception $e) {
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
	}

	/*
	/**
	 * Ask before deletion

	public static function onDelete($param)
	{
		// define the delete action
		$action = new TAction([__CLASS__, 'Delete']);
		$action->setParameters($param); // pass the key parameter ahead

		// shows a dialog to the user
		new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
	}

	/**
	 * Delete a record

	public static function Delete($param)
	{
		try {
			$key = $param['key']; // get the parameter $key
			TTransaction::open('grafica'); // open a transaction with database
			$object = new Venda($key, FALSE); // instantiates the Active Record
			$object->delete(); // deletes the object from the database
			TTransaction::close(); // close the transaction

			$pos_action = new TAction([__CLASS__, 'onReload']);
			new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
		} catch (Exception $e) // in case of exception
		{
			new TMessage('error', $e->getMessage()); // shows the exception error message
			TTransaction::rollback(); // undo all pending operations
		}
	}
	*/

	/**
	 * method show()
	 * Shows the page
	 */
	public function show()
	{
		// check if the datagrid is already loaded
		if (!$this->loaded and (!isset($_GET['method']) or !(in_array($_GET['method'],  array('onReload', 'onSearch'))))) {
			if (func_num_args() > 0) {
				$this->onReload(func_get_arg(0));
			} else {
				$this->onReload();
			}
		}
		parent::show();
	}
}
