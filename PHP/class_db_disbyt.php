<?php
/*********************
* class_db_disbyt.php
* class db pdo style
* 2014-12-03                       * 
* @var mixed
*/
  if(!defined('LOCAL_PATH')){
    define('LOCAL_PATH',"G:/TechPlat/apache/htdocs");
    define('LOCAL_DIR',"/Disbyt2");
  }
  
  class db{
    /***
    * The constructor is set to private so nobody 
    * can create a new instance using new 
    */
    private static $instance = NULL;
    private static $instance2 = NULL;  // For IMPORT LOCAL DATA ...
    private static $mySQLi = NULL; // MySQLi version
//
//OBS! lokal anpassning
    private static $database = 'RGDindatavalid'; // This database will be used
    private static $user = 'rgd';
    private static $pw = 'pilot';
//OBS!
    private function __construct(){
      
    }
    
    public static function getDBname(){
      return self::$database;
    }
    
    /***********
    * Return DB instance or create initial connection
    * @return object (PDO)
    * @access public
    */
    public static function getInstance(){
      if(!self::$instance){
        $database = self::$database;  
        self::$instance = new PDO("mysql:host=localhost;dbname=$database;charset=utf8", self::$user, self::$pw);
        self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      return self::$instance;
    }
    /**************************
    * for local data import pdo
    **************/
    public static function getInstance2(){
      if(!self::$instance2){
        $database = self::$database;
        self::$instance2 = new PDO("mysql:host=localhost;dbname=$database;charset=utf8", self::$user, self::$pw , array(PDO::MYSQL_ATTR_LOCAL_INFILE=>1));
        self::$instance2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      return self::$instance2;
    }
    /**************************
    * For local data import mySQLi on server not supporting PDO style
    **************/
    public static function getMySQLi(){
      if(!self::$mySQLi){
        self::$mySQLi = mysqli_init();
        self::$mySQLi->options(MYSQLI_OPT_LOCAL_INFILE, true);
        if(!self::$mySQLi->real_connect('localhost',self::$user,self::$pw,self::$database)){
          echo "Connect error: (" . mysqli_connect_errno() . ') ' . mysqli_connect_error();
          die();
        }
      }
      return self::$mySQLi;
    }
    /**********
    * Like the constructor we make clone private
    * so nobody can clone the instance
    */
    private function __clone(){    }
    
    /******************
    * Converting seconds to minutes and seconds
    * @param int $secs
    */
    public static function runTime($secs){
      $minutes = floor(($secs / 60) % 60);
      $seconds = $secs % 60;
      if($minutes == 0)
        return "$seconds sekunder";
      elseif($minutes == 1)
        return "$minutes minut $seconds sekunder";
      else return "$minutes minuter $seconds sekunder";   
    }
    /**********************************************
    * LOAD DATA LOCAL Import data to table in database
    * 
    * @param mixed $table
    * @param mixed $file with path
    * @param num $logg default = 0 
    * @param mixed $engine Not set PDO, set sqli
    * @return Error message if set
    */
    public static function dataImport($table,$file,$logg = FALSE, $engine = FALSE){
      $message = FALSE;
      $fk_stop = "SET foreign_key_checks = 0";
      $fk_start = "SET foreign_key_checks = 1";
      $keystop = sprintf("ALTER TABLE %s DISABLE KEYS",$table);
      $keystart = sprintf("ALTER TABLE %s ENABLE KEYS",$table);
      $ql = sprintf('LOAD DATA LOCAL INFILE "%s" INTO TABLE %s FIELDS TERMINATED BY "\t" LINES TERMINATED BY "\n"',$file,$table);
      if(empty($engine)){
        DB::getInstance2()->exec($fk_stop);
        DB::getInstance2()->exec($keystop);
        DB::getInstance2()->exec($ql);
        list($tmp,$flag,$ee)= DB::getInstance2()->errorInfo();
        if(!$flag) $message = 'Data importerade till tabell: ' . $table . ' (PDO-engine)';
        else{
          fwrite($logg,'Error:' . $tmp . '- ' . $flag . '- ' . $ee . "\n");
          fclose($logg);
          exit;
        }
        DB::getInstance2()->exec($keystart);
        DB::getInstance2()->exec($fk_start);}
      else {
        DB::getMySQLi()->query($fk_stop);
        DB::getMySQLi()->query($keystop);
        if(!DB::getMySQLi()->query($ql)){
          $message = "Error: " . DB::getMySQLi()->error;
          return $message;}
        else $message = 'Data importerade till tabell: ' . $table; 
        DB::getMySQLi()->query($keystart);
        DB::getMySQLi()->query($fk_start);
      }
      if($logg) fwrite($logg,'Data importerade till tabell: ' . $table . ' från fil: '  . $file . " Engine: " . $engine . "\n");
      return $message;   
    }
    /***************************************
    * Delete selected contribution(-s) in disbyt
    * @param mixed $FileIDs one or more comma separated
    * @return array with number of affected rows
    *********************************************/
    public static function delFileID($FileIDs){
      $ant = array();
      $ql = sprintf("DELETE FROM event WHERE PID IN (SELECT PID FROM person WHERE FileID IN (%s))",$FileIDs);      
      $ql1 = sprintf("DELETE FROM family WHERE PID IN (SELECT PID FROM person WHERE FileID IN (%s))",$FileIDs);   
      $ql2 = sprintf("DELETE FROM person WHERE FileID IN (%s)",$FileIDs);
      $ql3 = sprintf("DELETE FROM farm WHERE FileID IN (%s)",$FileIDs);
      $ql4 = sprintf("UPDATE file SET Status = 9, Comment = '%s' WHERE FileID IN (%s)",'Delete:' . date('Y-m-d H:i',time()),$FileIDs);
      $ant['event'] = DB::getInstance()->exec($ql);
      $ant['family']  = DB::getInstance()->exec($ql1);
      $ant['person']  = DB::getInstance()->exec($ql2);
      $ant['farm']  = DB::getInstance()->exec($ql3);
      DB::getInstance()->exec($ql4);
      return $ant;
    }
  }
?>
