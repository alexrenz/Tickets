<?php
/**
      
      felox
      Data Management Abstraction Engine
      
    * @author  Alexander Renz
    * @version 1.0
    * @module  felox-core
    
*/


class felox
{
  
  
  var $data       = Array( );
  var $dataRaw    = Array( );
  var $listRows   = Array( );

  var $real = FALSE;
  
  var $listRes;
  
  var $numTotalElements;
  var $numListElements;
  
/* 
      ----------------------------
      
      Database Functions
      
      ----------------------------
*/
  
  // Make a query on the DB
  function query( $query )
  {
    $res = mysql_query( $query );
    if( $res)
      return $res;
    else
      tkSendError( "<h4>Database Error query</h4>SQL: <pre>".
                    htmlentities( $query )."</pre><br /><br /><pre>".
                    mysql_error( )."</pre>" );
  }
  
  
  // Get the number of affected rows of a query
  function num( $mysqlres )
  {
    $num = mysql_num_rows( $mysqlres );
    
    if( is_numeric( $num ) )
      return $num;
    else
      tkSendError( "<h4>Database Error num</h4>Res: <pre>".
                    $mysqlres."</pre><br /><br />Num:<pre>".
                    $num ."</pre><br /><br /><pre>".
                    mysql_error( )."</pre>" );
  }
  
  
  // Fetch an associated array
  function assoc( $mysqlres )
  {
    $assoc = mysql_fetch_assoc( $mysqlres );
    
    return $assoc;
  }
  
  
  // Validate a FrontID
  function checkFrontidExistance( $frontid )
  {
    $res = $this->query( "
          SELECT 
            id
          FROM
            ".PRFX.$this->table."
          WHERE
            frontid='".mysql_real_escape_string( $frontid )."'
          ;" );
        
    return $this->num( $res ) > 0 ;
  }

  
/* 
      ----------------------------
      
      Data Output Functions
      
      ----------------------------
*/
  
  // Request a result set of threads
  function requestList( $addWhere = "", $limit = "", $orderBy = "", $orderDir = "ASC" )
  {
    global $tkUser;
    if( !empty( $addWhere ) )
      $where = $addWhere;
      
    if( !empty( $orderBy ) )
      $orderBy = "ORDER BY
        `".$orderBy."` ".$orderDir." ";
      
    if( !empty( $where ) ) $where = "WHERE ".$where;
    $origSql = "
      SELECT 
        *
      FROM
        ".PRFX.$this->table."
      ".$where."
      ".$orderBy."";
        
    $sql = $origSql."
      ".$limit."
      ;";
    
    $origSql .= ";";
    
    $this->listRes = $this->query( $sql );
    
    
    if( $this->num( $this->listRes )  > 0 )
    {
      $this->numListElements = $this->num( $this->listRes );
      
      $cache = $this->query( $origSql );
      $this->numTotalElements = $this->num( $cache );
      
      // One request to see, how many threads there are at all (without LIMIT)
      
      return true;
    }
    else
      return false;
  }
  
  // Fetch one element set
  function getListRow( )
  {
    return $this->assoc( $this->listRes );
  }
  

/* 
      ----------------------------
      
      Data Access Functions
      
      ----------------------------
*/
  
  // Write a bunch of field values into the thread var
  function setParams( &$params, $limit = "" )
  {
    if( $limit == "" )
      $limit = $this->confFields;
      
    
    foreach( $this->confFields as $field )
    {
      if( in_array( $field, $limit ) )
        $this->dataRaw[$field] = $this->prepareField( $field, $params[$field] );
    }
    return 0;
  }
  
  
  // Write the saved values into the DB
  function writeParams( )
  {
    if( $this->real )
    {
      // Update
      
      // Kick all static fields
      $update = Array( );
      foreach( $this->dataRaw as $name=>$val )
        if( !in_array( $name, $this->confStaticFields ) )
          $update[$name] = $val;
      
      // Create the update string
      $updateStr = "";
      $spacer = ", ";
      foreach( $update as $name=>$val )
      {
        if( end( array_keys( $update ) ) == $name )
          $spacer = "";
        
        $updateStr .= "`".$name."` = '".$update[$name]."'".$spacer;
      }
      
      $sql = "
      UPDATE
        ".PRFX.$this->table."
      SET
        ".$updateStr."
      WHERE
        id='".$this->dataRaw["id"]."'
        ;";
    }
    else
    {
      // Insert
      $fieldStr = "( ";
      $valStr = "( ";
      $spacer = ", ";
      foreach( $this->confFields as $field )
      {
        if( end( $this->confFields ) == $field )
          $spacer = "";
        
        $fieldStr .= "`".$field."`".$spacer;
        $valStr .= "'".$this->dataRaw[$field]."'".$spacer;
      }
      $fieldStr .= " )";
      $valStr .= " )";
      
      $sql = "
      INSERT INTO
        ".PRFX.$this->table."
        ".$fieldStr."
      VALUES
        ".$valStr."
        ;";
      
    }
    #die( $sql );
    // Make the Query
    $res = $this->query( $sql );
    
    if( $res )
    {
      // We need a id to work in both cases, update and insert
      if( !$this->real )
        $this->dataRaw["id"] = mysql_insert_id( );
      return TRUE;
    }
    else
    {
      tkLog( "MySQL Error @ setting Parrams for element: $sql , ".mysql_error() );
      tkSendError( $ln_db_update_error );
    }
  }
  
  
  // Delete the element
  function drop( )
  {
    $sql = "
    DELETE FROM
      ".PRFX.$this->table."
    WHERE
      id='".$this->dataRaw["id"]."'
      ;";
    #echo ( $sql."<br>" );
    return $this->query( $sql );
    #return false;
  }
      
}

?>
