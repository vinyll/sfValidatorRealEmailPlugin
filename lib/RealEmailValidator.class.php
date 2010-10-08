<?php
class RealEmailValidator
{
  protected $from = "no-reply@no-mail.com",
            $timeout = 5;
  
  public function __construct()
  {
  }
  
  public function setFrom($from)
  {
    $this->from = $from;
  }
  
  public function getFrom()
  {
    return $this->from;
  }
  
  public function setTimeout($timeout)
  {
    $this->timeout = $timeout;
  }
  
  public function getTimeout()
  {
    return $this->timeout;
  }
  
  public function validate($email)
  {
    if (!preg_match('/([^\@]+)\@(.+)$/', $email, $matches))
    {
      return false;
    }

    $user = $matches[1];
    $domain = $matches[2];

    if(!function_exists('checkdnsrr'))
    {
      throw new Exception(sprintf('%s could not find function "checkdnsrr"', __CLASS__));
    }
    if(!function_exists('getmxrr'))
    {
      throw new Exception(sprintf('%s could not find function "getmxrr"', __CLASS__));
    }
    // Get MX Records to find smtp servers handling this domain
    if(getmxrr($domain, $mxhosts, $mxweight))
    {
      for($i = 0; $i < count($mxhosts); $i++)
      {
        $mxs[$mxhosts[$i]] = $mxweight[$i];
      }
      asort($mxs);
      $mailers = array_keys($mxs);
    }
    elseif(checkdnsrr($domain, 'A'))
    {
      $mailers[0] = gethostbyname($domain);
    }
    else
    {
      return false;
    }
    // Try to send to each mailserver
    $total = count($mailers);
    $ok = false;
    for($n = 0; $n < $total; $n++)
    {
      $timeout = $this->timeout;
      $errno = 0;
      $errstr = 0;
      $sock = @fsockopen($mailers[$n], 25, $errno , $errstr, $timeout);
      if(!$sock)
      {
        continue;
      }
      $response = fgets($sock);
      stream_set_timeout($sock, $timeout);
      $meta = stream_get_meta_data($sock);
      $cmds = array(
            "HELO localhost",
            sprintf("MAIL FROM: <%s>", $this->from),
            "RCPT TO: <$email>",
            "QUIT",
      );
      if(!$meta['timed_out'] && !preg_match('/^2\d\d[ -]/', $response))
      {
        break;
      }
      $success_ok = true;
      foreach($cmds as $cmd)
      {
        fputs($sock, "$cmd\r\n");
        $response = fgets($sock, 4096);
        if(!$meta['timed_out'] && preg_match('/^5\d\d[ -]/', $response))
        {
          $success_ok = false;
          break;
        }
      }
      fclose($sock);
      return $success_ok;
    }
    return false;
  }

}