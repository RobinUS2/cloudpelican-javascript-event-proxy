<?php
/**
* Local proxy to push events to the CloudPelican backend without exposing the code
* @author Robin Verlangen (CloudPelican)
*/
define('CP_TIMEOUT', 5);

class CloudPelican_EventProxy
{
   /**
    * Your CloudPelican JS API token
    * @todo Configure
    * @var string
    */
   private $_jsApiToken = '';
   
   /**
    * The CloudPelican endpoint
    * @var string
    */
   private $_endPoint = 'https://api.cloudpelican.com/api/push/pixel';
   
   /**
    * Execute
    */
   public function run()
   {
       // Get input fields
       $fixedFields = array();
       $fields = array_merge($this->_getParam('f', array()), $fixedFields);
       
       // Query params
       $queryParams = array(
           'f' => $fields,
           't' => $this->_jsApiToken
       );
       
       // Assemble URL
       $url = $this->_endPoint . '?' . http_build_query($queryParams);
       
       // Call the file
       if (function_exists('curl_init')) {
           // Use CURL
           $ch = curl_init();
           curl_setopt($ch, CURLOPT_URL, $url);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
           curl_setopt($ch, CURLOPT_TIMEOUT, CP_TIMEOUT);
           $content = curl_exec($ch);
           curl_close($ch);
       } else {
           // Fallback to file get contents
           $ctx = stream_context_create(array('http'=>
               array(
                   'timeout' => CP_TIMEOUT
               )
           ));
           $content =  file_get_contents($url, false, $ctx);
       }
       
       // Output the right headers
       header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
       header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
       header("Cache-Control: no-store, no-cache, must-revalidate");
       header("Cache-Control: post-check=0, pre-check=0", false);
       header("Pragma: no-cache");
       
       // Image
       if ($content !== false && substr($content, 0, 1) != '{') {
           header('Content-type: image/gif');
       }
       echo $content;
   }
   
   /**
    * Get param from GET/POST
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
   protected function _getParam($key, $default = null)
   {
       if (isset($_POST[$key])) {
           return $_POST[$key];
       }
       if (isset($_GET[$key])) {
           return $_GET[$key];
       }
       return $default;
   }
}

set_time_limit(CP_TIMEOUT);
$instance = new CloudPelican_EventProxy();
$instance->run();
