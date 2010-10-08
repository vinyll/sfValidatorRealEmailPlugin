<?php
require dirname(__FILE__).'/../RealEmailValidator.class.php';
/**
 *
 * Checks wether an email adress is existant
 * @author Vincent Agnano <vincent.agnano@particul.es>
 */
class sfValidatorRealEmail extends sfValidatorEmail
{
  
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addOption('strong', true);
    $this->addMessage('inexistant', 'Inexistant.');
  }
  
  
  protected function doClean($value)
  {
    $clean = parent::doClean($value);
    
    $strongValidator = new RealEmailValidator();
    if($strongValidator->validate($clean) === false)
    {
      throw new sfValidatorError($this, 'inexistant', array('value' => $value));
    }

    return $clean;
  }
}