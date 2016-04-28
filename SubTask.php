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
   if(isset($xml->sub[0])){
      $one = $xml->sub[0]->count();
      $one_todo = 0;
      $one_done = 0;
      for($i=0;$i<$one;$i++){
         $one_todo += $xml->sub[0]->task[$i]->todo;
         $one_done += $xml->sub[0]->task[$i]->done;
         //if(isset($xml->sub[0]->task[$i]->sub[0])){
         //   $two = $xml->sub[0]->task[$i]->sub[0]->count();
         //   $two_todo = 0;
         //   $two_done = 0;
         //   for($j=0;$j<$two;$j++){
         //      $two_todo += $xml->sub[0]->task[$i]->sub[0]->task[$j]->todo; 
         //      $two_done += $xml->sub[0]->task[$i]->sub[0]->task[$j]->done; 
         //      if(isset($xml->sub[0]->task[$i]->sub[0]>task[$j]->sub[0])){
         //         $leaf = False;
         //      }else{
         //   }
         //   $xml->sub[0]->task[$i]->todo = $two_todo;
         //   $xml->sub[0]->task[$i]->done = $two_done;
         //}
      }
      $xml->todo = $one_done;
      $xml->done = $one_todo;
   }   
   xmlSave($xml,$xmlfile);
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
            $xml->sub[0]->addChild('task');
            $last = $xml->sub[0]->count()-1;
            $xml->sub[0]->task[$last]->addChild('name');
            $xml->sub[0]->task[$last]->addChild('done');
            $xml->sub[0]->task[$last]->addChild('todo');
            $xml->sub[0]->task[$last]->name = $_GET['name'];
            $xml->sub[0]->task[$last]->done = $_GET['done'];
            $xml->sub[0]->task[$last]->todo = $_GET['todo'];
         } 
         xmlSave($xml,$filename);
         header("Location: http://www.ajrobinson.org/SubTask/SubTask.php?id=".rmWhiteSpace($id));
         exit;   
      }
   
      xmlCheckHeir($filename);            # Make sure the hierarchy adds up in the xml
   
      xml2js(rmWhiteSpace($id),$filename,'data.js');        # convert the xml file to js array

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
                        <td><input type="text" name="name"></td>
                     </tr>
                     <tr>
                        <td><label for="todo">Todo</label></td>
                        <td><input type="range" min="0" max="50" value="25" name="todo"/></td>
                     </tr>
                     <tr>
                        <td><label for="done">Done</label></td>
                        <td><input type="range" min="0" max="50" value="25" name="done"/></td>
                     </tr>
                     <tr>
                        <td><label for="one">One</label></td>
                        <td><input type="text" name="one"></td>
                     </tr>
                     <tr>
                        <td><label for="two">Two</label></td>
                        <td><input type="text" name="two"></td>
                     </tr>
                     <tr>
                        <td><label for="three">Three</label></td>
                        <td><input type="text" name="three"></td>
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

