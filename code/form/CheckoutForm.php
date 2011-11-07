<?php
class CheckoutForm extends Form {
  
  public $currentOrder;
  protected $groupedFields = array();
  private $extraFieldsSet;
  
  function __construct($controller, $name, $groupedFields, FieldSet $actions, $validator = null, Order $currentOrder = null) {
    
    //Send fields in as associative array, then loop through and add to $fields array for parent constructuor
    //Overload the Fields() method to get fields for specific areas of the form
    
    $this->groupedFields = $groupedFields;
    
    $fields = new FieldSet();
    if (is_array($groupedFields)) foreach ($groupedFields as $setName => $setFields) {
      foreach ($setFields as $field) $fields->push($field);
    }
    else if ($groupedFields instanceof FieldSet) $fields = $groupedFields;
    
		parent::__construct($controller, $name, $fields, $actions, $validator);
		$this->setTemplate('CheckoutForm');
		$this->currentOrder = $currentOrder;
		$this->extraFieldsSet = new FieldSet();
  }
  
  function Cart() {
    return $this->currentOrder;
  }
  
	/**
	 * Return the forms fields for the template, but filter the fields for 
	 * a particular 'set' of fields.
	 * 
	 * @return FieldSet The form fields
	 */
	function Fields($set = null) {

	  if ($set) {
	    $fields = new FieldSet();
		
  		//TODO fix this, have to disable security token for now @see CheckoutPage::OrderForm()
  	  foreach ($this->getExtraFields() as $field) {
  			if (!$this->extraFieldsSet->fieldByName($field->Name())) {
  			  $this->extraFieldsSet->push($field);
  			  $fields->push($field);
  			}
  		}
  
  		if ($set && isset($this->groupedFields[$set])) {
  
  		  if (is_array($this->groupedFields[$set])) foreach ($this->groupedFields[$set] as $field) {
  		    $fields->push($field);
  		  }
  		  else $fields->push($this->groupedFields[$set]);
  		}
  		return $fields;
	  }
	  else return parent::Fields(); //For the validator to get fields
	}
	
	/**
	 * Overloaded so that form error messages are displayed.
	 * 
	 * @see OrderFormValidator::php()
	 * @see Form::validate()
	 */
  function validate(){

		if($this->validator){
			$errors = $this->validator->validate();
			
			//TODO errors seem to be getting populated with every error after form submission with one error
			//SS_Log::log(new Exception(print_r($errors, true)), SS_Log::NOTICE);

			if ($errors){

				if (Director::is_ajax() && $this->validator->getJavascriptValidationHandler() == 'prototype') {
				  
				  //Set error messages to form fields for display after form is rendered
				  $fields = $this->Fields();

				  foreach ($errors as $errorData) {
				    $field = $fields->dataFieldByName($errorData['fieldName']);
				    $field->setError($errorData['message'], $errorData['messageType']);
				    $fields->replaceField($errorData['fieldName'], $field);
				  }
				} 
				else {
				
					$data = $this->getData();

					$formError = array();
					if ($formMessageType = $this->MessageType()) {
					  $formError['message'] = $this->Message();
					  $formError['messageType'] = $formMessageType;
					}

					// Load errors into session and post back
					Session::set("FormInfo.{$this->FormName()}", array(
						'errors' => $errors,
						'data' => $data,
					  'formError' => $formError
					));

				}
				return false;
			}
		}
		return true;
	}
}

