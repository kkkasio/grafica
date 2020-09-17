<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Widget\Util\TFullCalendar;

class InstalacoesView extends TPage
{
	private $fc;

	public function __construct()
	{
		parent::__construct();


		$options = ['register_state' => 'false'];
		$this->fc = new TFullCalendar(date('Y-m-d'), 'agendaWeek');
		$this->fc->setReloadAction(new TAction(array($this, 'getEvents')));
		$this->fc->setDayClickAction(new TAction(array('InstalacoesForm', 'newEvent'), $options));
		//$this->fc->setEventClickAction(new TAction(array('CalendarioForm', 'onEdit'), $options));
		//$this->fc->setEventUpdateAction(new TAction(array('CalendarioForm', 'onUpdateEvent'), $options));

		$this->fc->disableDragging(TRUE);
		$this->fc->disableResizing(TRUE);

		$this->fc->setTimeRange('06:00', '20:00');

		$this->fc->setOption('businessHours', [['dow' => [1, 2, 3, 4, 5, 6], 'start' => '08:00', 'end' => '18:00']]);
		parent::add($this->fc);
	}

	public static function getEvents($param = NULL)
	{
		$return = array();
		try {
			TTransaction::open('grafica');

			$instalacoes = Instalacao::where('date', '>=', $param['start'])
				->where('date',   '<=', $param['end'])->load();
			//->where('funcionario_id', '=', TSession::getValue('userid'))->load();

			if ($instalacoes) {
				foreach ($instalacoes as $instalacao) {

					$instalacao->start = NULL;
					$instalacao->end = NULL;
					//$instalacao_array = $instalacao->toArray();

					if ($instalacao->periodo === 'manha') {
						$instalacao->start = $instalacao->date . 'T08:30:00';
						$instalacao->end   = $instalacao->date . 'T11:30:00';
					} else {
						$instalacao->start = $instalacao->date . 'T13:30:00';
						$instalacao->end   = $instalacao->date . 'T17:30:00';
					}
					$instalacao->color = $instalacao->cor;

					$popover_content = $instalacao->render("<b>Cliente</b> {venda->cliente->nome} <br> <b>Descrição</b>: {descricao}");
					$instalacao->title = TFullCalendar::renderPopover($instalacao->descricao, 'Instalação', $popover_content);

					$instalacao_array = $instalacao->toArray();
					$instalacao_array['start'] = $instalacao->start;
					$instalacao_array['end'] = $instalacao->end;
					$instalacao_array['title'] = $instalacao->title;

					$return[] = $instalacao_array;
				}
			}
			TTransaction::close();
			echo json_encode($return);
		} catch (Exception $e) {
			new TMessage('error', $e->getMessage());
		}
	}
	public function onReload($param = null)
	{
		if (isset($param['view'])) {
			$this->fc->setCurrentView($param['view']);
		}

		if (isset($param['date'])) {
			$this->fc->setCurrentDate($param['date']);
		}
	}
}
