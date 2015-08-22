<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OmniFlow;

/**
 * Description of FormView
 *
 * @author ralph
 */
class ModlerView extends Views {
    public function showEditor($file)
    {
        $xmlFile = Config::getConfig()->processPath.'/'.$file;
	$xml = file_get_contents($xmlFile);
        $bUrl=Context::getInstance()->omniBaseURL;
        
?>
<style>
 div.entry-content { margin:5px !important;}
</style>
    <div class="content" style="min-height:400px" id="js-drop-zone">

        <?php echo $file; ?> diagram

    <div class="message error">
      <div class="note">
        <p>Ooops, we could not display the BPMN 2.0 diagram.</p>

        <div class="details">
          <span>cause of the problem</span>
          <pre></pre>
        </div>
      </div>
    </div>

    <div class="canvas" style="height:800px" id="js-canvas"></div>
    
  </div>
  <ul class="buttons">
    <li>
      <a id="js-download-diagram" href title="download BPMN diagram">
        
      </a>
    </li>
    <li>
      <a id="js-download-svg" href title="download as SVG image">
        
      </a>
    </li>
    <li>
    </li>
  </ul>

<br />
  <script src="<?php echo $bUrl; ?>js/modeler.js"></script>
<script id="xmlSrc" type="text/xmldata">
<?php echo $xml; ?>
</script>
<script> 
	var file='<?php echo $file; ?>';

	
	var OmniXML;
	var OmniSVG;
	var OmniChangesCallback=diagramChanged;
        window.saveDiagramFunct=saveDiagram;

        omniMenusLocal.setItemDisabled("local.saveModel");        
//              
//                      
//        omniMenus.addButton("local.saveModel", 7, "Save Model", "open.gif", "open_dis.gif");
//        omniMenus.disableItem("local.saveModel");
        
	omniMenusLocal.attachEvent('onClick', function(id,zoneId,cas)
	{
                
	function diagramChanged()
	{
           
           
            omniMenusLocal.setItemEnabled("local.saveModel");
	}
    });

</script>
<?php
    }
}
