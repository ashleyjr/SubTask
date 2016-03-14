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
      <?php
         if(isset($_GET['id'])){
            $id = $_GET['id'];
            $filename = $id.'.xml';
            if(!file_exists($filename)){ 
               $file = fopen($filename,"wb");
               $entry ="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<".$id.">\n</".$id.">";     # Make an empty xml file
               fwrite($file,$entry);
               fclose($file);
            }
            $xml = file_get_contents($filename);                                                # Open the xml file as an array
            $p = xml_parser_create();
            xml_parse_into_struct($p, $xml, $vals, $index);
            xml_parser_free($p); 
            echo '<div>';                                                                       # Print html
            echo '   <div id="title">';
            echo '      <h1>SubTask</h1>';
            echo '      <h2>'.$id.'</h2>';
            echo '      <h3>'.$layers.' layers</h3>';
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

         $new = $xml;
         $new = str_replace("<sub>", ",[", $new);
         $new = str_replace("</sub>", "]", $new);
         $new = str_replace("<task>", "[", $new);
         $new = str_replace("</task>", "],", $new);
         $new = str_replace("<name>", "'", $new);
         $new = str_replace("</name>", "',[", $new);
         $new = str_replace("<todo>", "", $new);
         $new = str_replace("</todo>", ",", $new);
         $new = str_replace("<done>", "", $new);
         $new = str_replace("</done>", "]", $new);
         $new = str_replace("<test>", "code_hierarchy_data_1 = [", $new);
         $new = str_replace("</test>", "];", $new);
         $new = str_replace("</test>", "];", $new);

         $filename = 'data.js';                                                                 # Use php to create a js file
         $file = fopen($filename,"wb");                                                                                     # Contains the array to plotted
         fwrite($file,$new);
         fclose($file);


   ?>
   <script src="data.js"></script>

   </body>
</html>

