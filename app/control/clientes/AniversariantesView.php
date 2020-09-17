<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TSqlStatement;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Util\TFullCalendar;
use Adianti\Widget\Util\TXMLBreadCrumb;

class AniversariantesView extends TPage
{
  private $fc;

  /**
   * Page constructor
   */
  public function __construct()
  {
    parent::__construct();
    $this->fc = new TFullCalendar(date('Y-m-d'), 'month');
    $this->fc->setTimeRange('07:00:00', '21:00:00');
    $this->fc->enablePopover('Aniversário de: {nome}', '<b></b> <br> <i class="fa fa-user" aria-hidden="true"></i>  <br> ');

    $today = date("Y-m-d");


    TTransaction::open('grafica');

    $aniversariantes = PessoaFisica::where('month(nascimento)', '=', date('m'))->load();

    foreach ($aniversariantes as $aniversariante) {
      $this->fc->addEvent($aniversariante->id, 'Aniversário ' . $aniversariante->cliente->nome, $today . 'T' . rand(10, 17) . ':30:00', null, null, '#7159c1', $aniversariante);
    }


    // $this->fc->setDayClickAction(new TAction(array($this, 'onDayClick')));
    $this->fc->setEventClickAction(new TAction(array($this, 'onEventClick')));

    $vbox = new TVBox;
    $vbox->style = 'width: 100%';
    $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $vbox->add($this->fc);

    parent::add($vbox);
  }

  public static function onDayClick($param)
  {
    $date = $param['date'];
    new TMessage('info', "You clicked at date: {$date}");
  }

  public static function onEventClick($param)
  {
    $id = $param['id'];
    new TMessage('info', "You clicked at id: {$id}");
  }
}
