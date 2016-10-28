<?php 
function xml2js($id,$xmlfile,$jsfile){
   $new = file_get_contents($xmlfile);                                                # Open the xml file as an array
   $new = str_replace("<?xml version=\"1.0\"?>","",$new);
   $new = str_replace("<sub>", ",[", $new);
   $new = str_replace("</sub>", "]", $new);
   $new = str_replace("<task>", "[", $new);
   $new = str_replace("</task>", "],", $new);
   $new = str_replace("<name>", "'", $new);
   $new = str_replace("</name>", "',[", $new);
   $new = str_replace("<done>", "", $new);
   $new = str_replace("</done>", ",", $new);
   $new = str_replace("<todo>", "", $new);
   $new = str_replace("</todo>", "]", $new);
   $new = str_replace("<".$id.">", "code_hierarchy_data = [", $new);
   $new = str_replace("</".$id.">", "];", $new);
   $file = fopen($jsfile,"wb");                                                                                     # Contains the array to plotted
   fwrite($file,$new);
   fclose($file);
   echo '<script src="data.js"></script>';
   echo '<script type="text/javascript">';
   echo '   init_plots();';
   echo '</script>';

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

function xmlCreateIfNone($name){
   $filename = rmWhiteSpace($name).'.xml';
   if(!file_exists($filename)){  
      $xml = new SimpleXMLElement('<'.rmWhiteSpace($name).'></'.rmWhiteSpace($name).'>');
      $xml->addChild('name');
      $xml->addChild('done');
      $xml->addChild('todo');
      $xml->name = $name;
      $xml->todo = 0;
      $xml->done = 0; 
      xmlSave($xml,$filename);
   }
}

function xmlCheckHeir($xmlfile){
   $xml = new SimpleXMLElement(stripslashes(file_get_contents($xmlfile)));     
   if(isset($xml->sub[0])){                                                            // Make the heirarchy sum up
      $one = $xml->sub[0]->count();
      $one_todo = 0;
      $one_done = 0;
      for($i=0;$i<$one;$i++){
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
         $one_todo += $xml->sub[0]->task[$i]->todo;
         $one_done += $xml->sub[0]->task[$i]->done;  
      }
      $xml->todo = $one_todo;
      $xml->done = $one_done;       
   }    
   xmlSave($xml,$xmlfile);                                                             // Maintain only the unswapped file 
   if(isset($xml->sub[0])){                                                            // Swap all but the lead cells
      $one = $xml->sub[0]->count();
      for($i=0;$i<$one;$i++){
         $one_not_leaf = False;
         if(isset($xml->sub[0]->task[$i]->sub[0])){
            $one_not_leaf = True; 
         }
         if($one_not_leaf){                                                            // Switch if not a leaf
            $switch = (int)$xml->sub[0]->task[$i]->todo;                         
            $xml->sub[0]->task[$i]->todo = (int)$xml->sub[0]->task[$i]->todo;
            $xml->sub[0]->task[$i]->done = $switch;        
         }
      }
      $switch = (int)$xml->todo;                                                       // Always switch the core
      $xml->todo = (int)$xml->done;
      $xml->done = $switch;       
      
   }   
   xmlSave($xml,"display.xml");                                                        // This will be the displayed xml file
}

function queryCheckValid($name){
   if(isset($_GET[$name])){
      if(strlen($_GET[$name]) > 0){
         return True;
      }else{
         return False;
      }
   }else{
      return False;
   }
}

function rmWhiteSpace($text){
   return preg_replace('/\s+/', '', $_GET['id']);
}

function main(){
   $contents = file_get_contents('SubTask.html');
   
   if(isset($_GET['id'])){
      $id = $_GET['id'];
      xmlCreateIfNone($id); 
      $filename = rmWhiteSpace($id).'.xml';
      if(   isset($_GET['name']) and
            isset($_GET['todo']) and
            isset($_GET['done']) 
         ){
         $xml = new SimpleXMLElement(stripslashes(file_get_contents($filename)));    
         if(queryCheckValid('one')){ 
            $one_length = $xml->sub[0]->count();
            for($i=0;$i<$one_length;$i++){
               if($xml->sub[0]->task[$i]->name == $_GET['one']){
                  $found = $i;
               }
            }
            if(!isset($xml->sub[0]->task[$found]->sub[0])){
               $xml->sub[0]->task[$found]->addChild('sub');
            }
            $xml->sub[0]->task[$found]->sub[0]->addChild('task');
            $last = $xml->sub[0]->task[$found]->sub[0]->count()-1;
            $xml->sub[0]->task[$found]->sub[0]->task[$last]->addChild('name');
            $xml->sub[0]->task[$found]->sub[0]->task[$last]->addChild('done');
            $xml->sub[0]->task[$found]->sub[0]->task[$last]->addChild('todo');
            $xml->sub[0]->task[$found]->sub[0]->task[$last]->name = $_GET['name'];
            $xml->sub[0]->task[$found]->sub[0]->task[$last]->done = $_GET['done'];
            $xml->sub[0]->task[$found]->sub[0]->task[$last]->todo = $_GET['todo']; 
         }else{ 
            if(!isset($xml->sub[0])){
               $xml->addChild('sub');
            } 
            $new = True;
            $root_length = $xml->sub[0]->count();
            for($i=0;$i<$root_length;$i++){
               if(isset($xml->sub[0]->task[$i]->name)){
                  if($xml->sub[0]->task[$i]->name == $_GET['name']){
                     $edit = $i;
                     $new = False;
                  }
               }
            }
            if($new){
               $xml->sub[0]->addChild('task');
               $edit = $xml->sub[0]->count()-1;
               $xml->sub[0]->task[$edit]->addChild('name');
               $xml->sub[0]->task[$edit]->addChild('done');
               $xml->sub[0]->task[$edit]->addChild('todo');
               $xml->sub[0]->task[$edit]->name = $_GET['name'];
            }
            $xml->sub[0]->task[$edit]->done = $_GET['done'];
            $xml->sub[0]->task[$edit]->todo = $_GET['todo']; 
         } 
         xmlSave($xml,$filename);
         header("Location: http://www.ajrobinson.org/SubTask/SubTask.php?id=".rmWhiteSpace($id));
         exit;   
      }
   
      xmlCheckHeir($filename);            # Make sure the hierarchy adds up in the xml
   
      xml2js(rmWhiteSpace($id),"display.xml",'data.js');        # convert the xml file to js array

      $xml = new SimpleXMLElement(stripslashes(file_get_contents($filename)));    
      $title = $xml->name;
      $contents .='
         <div>                                          
            <div id="title">
               <h1>SubTask: '.$title.'</h1>
            </div> 
            <div id="input">
               <form id="add" name="add" method="get" action="">
                  <input type="hidden" name="id" value='.rmWhiteSpace($id).'>
                  <table>
                     <tr>
                        <td><label for="new">Name</label></td>
                        <td><input type="text" id="name" name="name"></td>
                     </tr>
                     <tr>
                        <td><label for="todo">Todo</label></td>
                        <td><input id="todo" type="range" min="0" max="99" value="50" name="todo" onChange="clamp_box(done.value,todo.value);"/></td>
                        <td><label id="todo_label" name="todo_label" type="text">50</label></td>
                     </tr>
                     <tr>
                        <td><label for="done">Done</label></td>
                        <td><input id="done" type="range" min="0" max="99" value="25" name="done" onChange="clamp_box(done.value,todo.value);"/></td>
                        <td><label id="done_label" name="done_label" type="text">25</label></td> 
                     </tr>
                     <tr>
                        <td><label for="one">One</label></td>
                        <td><input type="text" id="one" name="one"></td>
                     </tr>
                     <tr>
                        <td><input type="checkbox" id="child" onChange ="child_switch();" name="child" value="Bike">Child</td>
                     </tr>
                  </table>
                  <input type="submit" value="Submit">
               </form>
               <form id="add" name="add" method="get" action="">
                  <input type="submit" value="New">
               </form>
            </div>
               <div id="code_hierarchy_select">&nbsp;</div>
               <div id="code_hierarchy_legend">&nbsp;</div>
               <div id="code_hierarchy">&nbsp;</div>
            </div>
            <script>'.file_get_contents('data.js').'</script>
            <script type="text/javascript">
               init_plots();
            </script>
            </body>
         </html>';
   }else{
      $files = scandir('.');
      $list = "";
      foreach ($files as $file) {
         $ext = pathinfo($file, PATHINFO_EXTENSION);
         if($ext == "xml"){
            $list .= '<option value="'.pathinfo($file, PATHINFO_FILENAME).'">'; 
         }
      } 
      $contents .= '
         <div id="title">
            <h1>SubTask</h1>
         </div>
         <div id="input">
            <form id="new" name="new" method="get" action=""> 
               <input list="id" name="id" autocomplete="off">
                  <datalist id="id">'.$list.'</datalist>
               <input type="submit" value="Load">
            </form>
         </div>';
   } 
   echo $contents;
}

main();
?>

