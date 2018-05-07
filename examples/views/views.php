<pre>
<?php
Class SMC {

  public $tag;
  public $fileData;

  function __construct($tag) {
    $this->tag = $tag;
  }


  // Tries to fetch the UNSERIALIZED data
  function getData() {
    $this->fileData = file_get_contents(__FILE__);
    //echo "+ Looking for tag\n";
    preg_match("/\/\*".$this->tag."(.*)\*\//", $this->fileData, $m);
    if( empty($m) ) {
      //echo "+ No data\n";
      return null;
    } else {
      //echo "+ Found data\n";
      return unserialize(base64_decode($m[1]));
    }
  }

  // Returns the data if available
  // else it adds empty data and return 0
  function init() {
    $data = $this->getData();
    if( $data ) {
      //echo "$data";
      return $data;
    } else {
      //echo "+ No data, writing";
      $data = "init";
      $base64Encoded = base64_encode(serialize($data));
      $newData = "\n<?php /*" . $this->tag . $base64Encoded . "*/ ?>\n";
      //echo "+ Adding $newData";
      $this->fileData .= $newData;
      file_put_contents(__FILE__, $this->fileData);
      return 0;
    }
  }

  function write($object) {
    $this->init();
    $base64Encoded = base64_encode(serialize($object));

    // "I know, I'll use regular expressions."  
    $this->fileData =  preg_replace("/(\/\*".$this->tag.")(.*)(\*\/)/", '$1'.$base64Encoded.'$3', $this->fileData);
    file_put_contents(__FILE__, $this->fileData);


  }


}

$logger = new SMC("logger");
$data = $logger->getData();
if( $data  ) { 
  $logger->write( $data + 1 );
} else {
  $logger->write(1);
}
echo "Views: " . ($data+1);

?>

