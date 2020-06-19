<?php
/**
 * EstadoForm Registration
 * @author  <your name here>
 */
class EstadoForm extends TPage
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
        
        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['EstadoList', 'onReload'], ['register_state' => 'true']) );
        //$this->setClientValidation(true);
        

        $this->setDatabase('grafica');              // defines the database
        $this->setActiveRecord('Estado');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Estado');
        $this->form->setFormTitle('Estado');
        $this->form->setFieldSizes('100%');

        // create the form fields
        $nome = new TEntry('nome');
        $uf = new TEntry('uf');


        // add the fields
        $this->form->addFields( [ new TLabel('Nome'), $nome ] );
        $this->form->addFields( [ new TLabel('UF'), $uf ] );
        
        $nome->addValidation('Nome', new TRequiredValidator);
        $nome->addValidation('UF', new TRequiredValidator);



        // set sizes
        $nome->setSize('100%');
        $uf->setSize('100%');


        
        if (!empty($id))
        {
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
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
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
