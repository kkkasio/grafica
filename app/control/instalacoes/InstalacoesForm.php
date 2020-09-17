<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * InstalacoesForm Form
 * @author  <your name here>
 */
class InstalacoesForm extends TPage
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
		$this->form = new BootstrapFormBuilder('form_Instalacoes');
		$this->form->setFormTitle('Instalacoes');


		// create the form fields
		$id = new TEntry('id');
		$date = new TDate('date');
		$periodo = new TCombo('periodo');
		$funcionario_id = new TDBUniqueSearch('funcionario_id', 'grafica', 'SystemUser', 'id', 'name');
		$venda_id = new TDBUniqueSearch('venda_id', 'grafica', 'Venda', 'id', 'numero');
		$descricao = new TEntry('descricao');
		$cor = new TColor('cor');
		$status = new TCombo('status');


		$periodo->addItems(['manha' => 'Manhã', 'tarde' => 'Tarde']);

		$status->addItems(['Finalizado' => 'Finalizado', 'Aberto' => 'Aberto', 'Pendente' => 'Pendente']);
		$status->setValue('Aberto');

		$date->setMask('dd/mm/yyyy');
		$date->setDatabaseMask('yyyy-mm-dd');

		// add the fields
		$this->form->addFields([new TLabel('#')], [$id]);
		$this->form->addFields([new TLabel('Data')], [$date]);
		$this->form->addFields([new TLabel('Periodo')], [$periodo]);
		$this->form->addFields([new TLabel('Funcionario')], [$funcionario_id]);
		$this->form->addFields([new TLabel('Venda')], [$venda_id]);
		$this->form->addFields([new TLabel('Descrição')], [$descricao]);
		$this->form->addFields([new TLabel('Cor')], [$cor]);
		$this->form->addFields([new TLabel('Status')], [$status]);



		// set sizes
		$id->setSize('100%');
		$date->setSize('100%');
		$periodo->setSize('100%');
		$funcionario_id->setSize('100%');
		$venda_id->setSize('100%');
		$descricao->setSize('100%');
		$cor->setSize('100%');
		$status->setSize('100%');




		if (!empty($id)) {
			$id->setEditable(FALSE);
		}


		$date->addValidation('Data', new TRequiredValidator);
		$periodo->addValidation('Período', new TRequiredValidator);
		$funcionario_id->addValidation('Funcionario', new TRequiredValidator);
		$venda_id->addValidation('Venda', new TRequiredValidator);
		$cor->addValidation('cor', new TRequiredValidator);
		$descricao->addValidation('Descrição', new TRequiredValidator);



		// create the form actions
		$btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
		$btn->class = 'btn btn-sm btn-primary';
		$this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
		$this->form->addHeaderActionLink(_t('Close'), new TAction(array($this, 'onClose')), 'fa:times red');

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

			$object = new Instalacao();  // create an empty object

			if ($object->verificaDataPeriodo($data->date, $data->periodo)) {
				throw new Exception('Já existe uma instalação para a data e o período selecionado');
			}

			$object->fromArray((array) $data); // load the object with data
			$object->store(); // save the object

			// get the generated id
			$data->id = $object->id;

			$this->form->setData($data); // fill form data
			TTransaction::close(); // close the transaction

			TScript::create("Template.closeRightPanel()");

			$redirect = new TAction(array('InstalacoesView', 'onReload'));
			$redirect->setParameter('target_container', 'adianti_div_content');
			$redirect->setParameter('view', $data->view);
			$redirect->setParameter('date', $data->data_inicial);

			new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $redirect);
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
		try {
			if (isset($param['key'])) {
				$key = $param['key'];  // get the parameter $key
				TTransaction::open('grafica'); // open a transaction
				$object = new Instalacao($key); // instantiates the Active Record
				$this->form->setData($object); // fill the form
				TTransaction::close(); // close the transaction
			} else {
				$this->form->clear(TRUE);
			}
		} catch (Exception $e) // in case of exception
		{
			new TMessage('error', $e->getMessage()); // shows the exception error message
			TTransaction::rollback(); // undo all pending operations
		}
	}

	public function newEvent($param)
	{
		$this->onClear($param);
		$data = new stdClass;
		$data->view = $param['view'];
		//$data->cor = '#3a87ad';

		if ($param['date'])

			$data->date = substr($param['date'], 0, 10);

		$this->form->setData($data);
	}

	public static function onClose($param)
	{
		TScript::create("Template.closeRightPanel()");
	}
}
