<?php
require sfConfig::get('sf_test_dir').'/bootstrap/unit.php';
require dirname(__FILE__).'/../../lib/validator/sfValidatorRealEmail.class.php';

$t = new lime_test(6, new lime_output_color);

$v = new sfValidatorRealEmail();

$msg = '->clean() validates a regular email address';
try
{
  $t->is($v->clean('vincent.agnano@particul.es'), 'vincent.agnano@particul.es', $msg);
}
catch(sfValidatorError $e)
{
  $t->fail($msg);
}


$msg = '->clean() throws an "invalid" error code for malformatted addresses';
try
{
  $v->clean('vincent.agnano @particul.es');
  $t->fail($msg);
}
catch(sfValidatorError $e)
{
  $t->is($e->getCode(), 'invalid', $msg);
}


$msg = '->clean() throws an "invalid" error code for malformatted addresses that fixes the sfValidatorEmail regex';
try
{
  $v->clean('.vincent.agnano.@particul.es');
  $t->fail($msg);
}
catch(sfValidatorError $e)
{
  $t->is($e->getCode(), 'invalid', $msg);
}

$msg = '->clean() throws an "invalid" error code for malformatted addresses that fixes the sfValidatorEmail regex';
try
{
  $v->clean('--vincent.agnano-@particul.es');
  $t->fail($msg);
}
catch(sfValidatorError $e)
{
  $t->is($e->getCode(), 'invalid', $msg);
}


$msg = '->clean() throws an "inexistant" error code for non-existant domain';
try
{
  $v->clean('vincent.agnano@particul.ees');
  $t->fail($msg);
}
catch(sfValidatorError $e)
{
  $t->is($e->getCode(), 'inexistant', $msg);
}


$msg = '->clean() throws an "inexistant" error code for non-existant username';
try
{
  $v->clean('thisuserobviouslydoesnotexistshopefullyatleast@gmail.com');
  $t->fail($msg);
}
catch(sfValidatorError $e)
{
  $t->is($e->getCode(), 'inexistant', $msg);
}