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
            $layers = 0;                                                                        # Find the number of layers in the xml file
            for($i=0;$i<count($vals);$i++){
               if($vals[$i]['level'] > $layers) $layers = $vals[$i]['level'];   
            }
            $layers = $layers/2; 
            $arr_str = "";                                                                      # Build a string containing the array data
            for($i=0;$i<count($vals);$i++){
               if($vals[$i]['tag'] == "NAME"){
                  $arr_str .= "<br>";
                  for($j=0;$j<$vals[$i]['level'];$j++){
                     $arr_str .= "...";
                  }
                  $arr_str .= $vals[$i]['value']." ";
               }
               if($vals[$i]['tag'] == "TODO"){
                  $arr_str .= $vals[$i]['value']."/";
               }
               if($vals[$i]['tag'] == "DONE"){
                  $arr_str .= $vals[$i]['value'];
               }
            }
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
            echo '      '.$arr_str;
            echo '   </div>';
            echo '      <div id="code_hierarchy_legend">&nbsp;</div>';
            echo '      <div id="code_hierarchy">&nbsp;</div>';
            echo '</div>';
         
         } 
      ?> 
   </body>
</html>

<script type="text/javascript">
code_hierarchy_data_1 = 
   ["",[10,10],{  
      "1": ["1",[8,8],{
         "a":["a",[6,6],{}],
         "b":["b",[1,1],{}],
         "C":["C",[1,1],{}],
      }],
      "2":["2",[1,1],{}],
      "3":["3",[1,1],{}],
      }
   ];
</script>
