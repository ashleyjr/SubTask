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
         
         function xml2js($xmlfile,$jsfile){
            $new = file_get_contents($xmlfile);                                                # Open the xml file as an array
            $new = str_replace("<?xml version=\"1.0\"?>","",$new);
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
            $file = fopen($jsfile,"wb");                                                                                     # Contains the array to plotted
            fwrite($file,$new);
            fclose($file);
         }

         function xmlSave($xml,$xmlfile){
               $output = $xml->asXML();
               $doc = new DOMDocument();
               $doc->preserveWhiteSpace = false;
               $doc->formatOutput = true;
               $doc->loadXML($output);
               $output =  $doc->saveXML();
               file_put_contents($xmlfile,$output);

         }

         function xmlCheckHeir($xmlfile){
               $xml = new SimpleXMLElement(stripslashes(file_get_contents($xmlfile)));     
               $one = $xml->sub[0]->count();
               $one_todo = 0;
               $one_done = 0;
               for($i=0;$i<$one;$i++){
                  $one_todo += $xml->sub[0]->task[$i]->todo;
                  $one_done += $xml->sub[0]->task[$i]->done;
                  if(isset($xml->sub[0]->task[$i]->sub[0])){
                     $two = $xml->sub[0]->task[$i]->sub[0]->count();
                     $two_todo = 0;
                     $two_done = 0;
                     for($j=0;$j<$two;$j++){
                        $two_todo += $xml->sub[0]->task[$i]->sub[0]->task[$j]->todo; 
                        $two_done += $xml->sub[0]->task[$i]->sub[0]->task[$j]->done; 
                     }
                     $xml->sub[0]->task[$i]->todo = $two_todo;
                     $xml->sub[0]->task[$i]->done = $two_done;
                  }
               }
               $xml->todo = $one_todo;
               $xml->done = $one_done;
               xmlSave($xml,$xmlfile);
         }

         if(isset($_GET['id'])){
            $id = $_GET['id'];
            $filename = $id.'.xml';
            if(!file_exists($filename)){ 
               $file = fopen($filename,"wb");
               $entry ="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<".$id.">\n</".$id.">";     # Make an empty xml file
               fwrite($file,$entry);
               fclose($file);
            }
            
            if(   isset($_GET['name']) and
                  isset($_GET['todo']) and
                  isset($_GET['done']) 
               ){
               $xml = new SimpleXMLElement(stripslashes(file_get_contents($filename)));   
               if(isset($_GET['one'])){
                  if(isset($_GET['two'])){
                     echo "two"; 
                     $one_length = $xml->sub[0]->count()-1;
                     for($i=0;$i<$one_length;$i++){
                        if($xml->sub[0]->task[$i]->name == $_GET['one']){
                           $found = $i;
                        }
                     }
                     echo $found;
                     if(!isset($xml->sub[0]->task[$found]->sub[0])){
                        $xml->sub[0]->task[$found]->addChild('sub');
                     }
                     $xml->sub[0]->task[$found]->sub[0]->addChild('task');
                     $last = $xml->sub[0]->task[$found]->sub[0]->count()-1;
                     $xml->sub[0]->task[$found]->sub[0]->task[$last]->addChild('name');
                     $xml->sub[0]->task[$found]->sub[0]->task[$last]->addChild('todo');
                     $xml->sub[0]->task[$found]->sub[0]->task[$last]->addChild('done');
                     $xml->sub[0]->task[$found]->sub[0]->task[$last]->name = $_GET['name'];
                     $xml->sub[0]->task[$found]->sub[0]->task[$last]->todo = $_GET['todo'];
                     $xml->sub[0]->task[$found]->sub[0]->task[$last]->done = $_GET['done'];

                  }else{  
                     $xml->sub[0]->addChild('task');
                     $last = $xml->sub[0]->count()-1;
                     $xml->sub[0]->task[$last]->addChild('name');
                     $xml->sub[0]->task[$last]->addChild('todo');
                     $xml->sub[0]->task[$last]->addChild('done');
                     $xml->sub[0]->task[$last]->name = $_GET['name'];
                     $xml->sub[0]->task[$last]->todo = $_GET['todo'];
                     $xml->sub[0]->task[$last]->done = $_GET['done'];
                  }
               }
               xmlSave($xml,$filename);
            }

            xmlCheckHeir($filename);            # Make sure the hierarchy adds up in the xml

            xml2js($filename,'data.js');        # convert the xml file to js array
         
            
            echo '<div>';                                                                       # Print html
            echo '   <div id="title">';
            echo '      <h1>SubTask</h1>';
            echo '      <h2>'.$id.'</h2>';
            echo '      <h3>'.$layers.' layers</h3>';
            echo '   </div>';
            echo '   <div id="input">';
            echo '      <input type="text" size="25" value="Enter your name here!">';
            echo '      <input type="submit" value="Submit" onclick="init_plots()"><br>';
            echo '   </div>';
            echo '      <div id="code_hierarchy_legend">&nbsp;</div>';
            echo '      <div id="code_hierarchy">&nbsp;</div>';
            echo '</div>';
         }
   ?>
   <script src="data.js"></script>
   <script type="text/javascript">
      init_plots();
   </script>
   </body>
</html>

