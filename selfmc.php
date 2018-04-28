<pre>
<?php
Class SMC {

  public $tag;

  function __construct($tag) {
    $this->tag = $tag;
  }


  function init() {
    $filename = __FILE__;
    echo "+ Reading self ($filename)\n";
    $selfData = file_get_contents($filename);
    echo "+ Looking for tag\n";
    preg_match("/\/\*".$this->tag."(.*)\*\//", $selfData, $m);
    if( empty($m) ) {
      echo "+ No data, initializing\n";

      $data = null;
      $serialized = serialize($data);
      $base64Encoded = base64_encode($serialized);
      $newData = "\n<?php /*" . $this->tag . $base64Encoded . "*/ ?>\n";

      echo "+ Adding $newData";

      file_put_contents($filename, $selfData . $newData);
    } else {
      echo "+ Found data\n";
      print_r($m);
    }
  }

}

$logger = new SMC("logger");
print_r($logger);

$logger->init();
?>




<?php /*loggerTjs=*/ ?>
