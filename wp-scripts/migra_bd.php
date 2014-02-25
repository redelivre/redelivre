
<?php

ini_set("register_globals","off");

if (sizeof($argv) != 7) die ('Número incorreto de parametros. Usar: php migra_bd.php DB_NAME DB_USER DB_PASS DB_HOST DOMAIN TABLE_PREFIX');

$db_name = $argv[1];
$db_user = $argv[2];
$db_pass = str_replace('%!%', '', $argv[3]);
$db_host = $argv[4];
$newUrl = $argv[5];
$tablePrefix = $argv[6];
global $counter;
$counter = 0;

function check_and_unserialize($serialized) {
  if (preg_match_all('/O:(\d+):"(\S+?)"/', $serialized, $m)) {
    // print $serialized; 
    //$len = $m[1][0];
    //$class = substr($serialized, 4 + strlen($len), (int)$len);
    // print_r($m); die;
    $class = $m[2][0];
    //print "aaaa"; var_dump( $class ); exit;
    //echo $class; die;
    eval ("if (!class_exists($class)) { class $class {}  }");
  }
  //echo $serialized;
  $unserialized = unserialize($serialized);
  return $unserialized;
	 
}

function replace_recursive($data) {
    global $oldUrl, $newUrl, $counter;  
    if (is_array($data)) {
        foreach ($data as $key => $info) {
            if ( !is_array($info) && !is_object($info)) {
                if (strpos($info, $oldUrl) !== false) {
                    $data[$key] = str_replace($oldUrl, $newUrl, $info);
                    $counter ++;
                }
            } else {
                $data[$key] = replace_recursive($data[$key]);
            }
        }
        
    } elseif (is_object($data)) {
    
        foreach ($data as $key => $info) {
            if ( !is_array($info) && !is_object($info)) {
                if (strpos($info, $oldUrl) !== false) {
                    $data->$key = str_replace($oldUrl, $newUrl, $info);
                    $counter ++;
                }
            } else {
                $data->$key = replace_recursive($data->$key);
            }
        }
    }
    
    return $data;
}

function is_serialized( $data ) {
    // if it isn't a string, it isn't serialized
    if ( !is_string( $data ) )
        return false;
    $data = trim( $data );
    if ( 'N;' == $data )
        return true;
    if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
        return false;
    switch ( $badions[1] ) {
        case 'a' :
        case 'O' :
        case 's' :
            if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
                return true;
            break;
        case 'b' :
        case 'i' :
        case 'd' :
            if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
                return true;
            break;
    }
    return false;
}


$con = mysql_connect($db_host, $db_user, $db_pass);
if (!$con)
  die('Could not connect: ' . mysql_error());

mysql_select_db($db_name, $con);

mysql_query("SET NAMES 'utf8'");

$oldUrl = mysql_query("SELECT option_value FROM {$tablePrefix}options WHERE option_name = 'siteurl'");
if ($oldUrl) $oldUrl = mysql_fetch_object($oldUrl);
if (is_object($oldUrl)) $oldUrl = $oldUrl->option_value;

if (!$oldUrl) {
    // devemos estar em um wordpress MU
    $oldUrl = mysql_query("SELECT domain FROM {$tablePrefix}site WHERE id = 1");
    if ($oldUrl) $oldUrl = mysql_fetch_object($oldUrl);
    if (is_object($oldUrl)) $oldUrl = $oldUrl->domain;
    if (!$oldUrl) die('Nao foi encontrada a Url antiga do site');
} 

$oldUrl = str_replace('http://', '', $oldUrl);

if ($oldUrl == $newUrl) die('Site já aponta para dominio atual');

$result = mysql_query('SHOW TABLES');
$tables = array();
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
    array_push($tables, $row[0]); 
}

foreach ($tables as $table) {
	
	$query_fields = mysql_query("DESC $table");

	// descobre a chave primaria
	while ($row = mysql_fetch_array($query_fields)) {
		if ($row['Key'] == 'PRI') {
			$primaryKey = $row['Field'];
			break;
		}
	}
    
	
	$query_fields = mysql_query("DESC $table");
	
	while ($field = mysql_fetch_array($query_fields)) {
		
		if (strpos($field['Type'], 'int') === false) {
			
            
			echo '.';
			$fieldName = $field['Field'];
			
            $records_query = mysql_query("SELECT $fieldName, $primaryKey FROM $table");
			
			if ($records_query) {
				
				while ($record = mysql_fetch_object($records_query)) {
					
					if (strpos($record->$fieldName, $oldUrl) !== false) {

						
						if (is_serialized($record->$fieldName)) {


							
							$uns = check_and_unserialize($record->$fieldName);


							
							if (is_array($uns)) {


                                $uns = replace_recursive($uns);
	                            $newRecord = serialize($uns);
	                            $newRecord = addslashes($newRecord);
	                            
	                            mysql_query("UPDATE $table SET $fieldName = '$newRecord' WHERE $primaryKey = {$record->$primaryKey}");
	                            echo mysql_error();
							}
							
						} else {
							if (strpos($record->$fieldName, $oldUrl) !== false) {
	                            $newRecord = addslashes(str_replace($oldUrl, $newUrl, $record->$fieldName));
							}
							mysql_query("UPDATE $table SET $fieldName = '$newRecord' WHERE $primaryKey = {$record->$primaryKey}");
							echo mysql_error();
                            $counter ++;
						}
					}	
				}
			}	
		}
	}
}

echo "\nMigração finalizada. ", $counter, " ocorrencias substituídas\n";

?>
