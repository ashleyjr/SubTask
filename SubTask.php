<!DOCTYPE html>
<html>
   <meta charset="utf-8" />
   <style>
   
      html
      {
          font-family: Century Gothic, Arial, Helvetica;
          font-size:1em;
      }
      
      body
      {
          padding:10px;
      }
      
      form
      {
      
      }
      
      #code_hierarchy
      {
         margin:0 auto;
         position: absolute;
         width: 40%;
         height: 40%;
         top: 10%;
         left: 55%;
      }
      
      #code_hierarchy_legend
      {
         position:absolute;
         width:40%;
         top:8%;
         left:20%;
         font-size:1.4em;
      }
      
      #title
      {
         position:absolute;
         width:40%;
         top:0%;
         left:2%;
         font-size:1.4em;
      }
   
      #input
      {
         position:absolute;
         width:40%;
         top:40%;
         left:2%;
         font-size:1.4em;
      } 
   </style>
   <head>
      <script src="d3.js"></script>
      <script src="chart.js"></script>
      </head>
   <body>  
      <div>

<?php

if( isset($_GET['id'])){
   $id = $_GET['id'];
   $filename = $id.'.xml';
   if(!file_exists($filename)){ 
      $file = fopen($filename,"wb");
      $entry ="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<".$id.">\n</".$id.">";
      fwrite($file,$entry);
      fclose($file);
   }
   $xml = file_get_contents($filename);     
   echo $xml;
   echo '<div>';
   echo '   <div id="title">';
   echo '      <h1>SubTask</h1>';
   echo '      <h2>'.$id.'</h2>';
   echo '   </div>';
   echo '   <div id="input">';
   echo '      <input type="text" size="25" value="Enter your name here!">';
   echo '      <input type="submit" value="Submit" onclick="init_plots(1)"><br>';
   echo '      <input type="submit" value="Submit" onclick="init_plots(0)"><br>';
   echo '   </div>';
   echo '      <div id="code_hierarchy_legend">&nbsp;</div>';
   echo '      <div id="code_hierarchy">&nbsp;</div>';
   echo '</div>';
} 
?> 
   </body>
</html>
