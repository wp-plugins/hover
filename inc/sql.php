<?php

/* code needed to create our table */
$ddl[HOVER_TABLE] = "CREATE TABLE ".HOVER_TABLE." (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	   type tinytext NOT NULL,
	   search tinytext NOT NULL,
	   link text NOT NULL,
	   description text not NULL,
	   UNIQUE KEY id (id)
		   );";

$ddl[HOVER_IMAGES] = "CREATE TABLE ".HOVER_IMAGES." (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	  src tinytext NOT NULL,
	  text tinytext NOT NULL,
	  UNIQUE KEY id (id)
	);";

?>
