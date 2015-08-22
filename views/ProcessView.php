<?php
namespace OmniFlow;

class ProcessView extends Views
{

public function DescribeProcess(Process $proc,$file)
{
	?>
<script>
	jQuery( document ).ready(function() {
			BuildPage();
			getJson();
			initFields();
			
	});		

</script>
	<div id="MainLayout" style="position: relative; width: 100%; height: 800px;">
	<!-- js will embed layout here -->
            <div id='diagramContents'>
            <?php 
            $imageFile = 'processes/'.str_replace(".bpmn", ".svg",$file);
            SVGHandler::displayDiagram($proc,array());
            ?>
            </div>
            <!-- end of diagram -->
            <div id="proessItems">
                    <table><tr><td width="25%">
                            <div id="ItemsList">
                            </div> <!-- end of Items list -->
                      </td>
                      <td>
                                    <div id="itemDetails">
                                    <p>Please select an item from the list on the left or diagram above to view details.
                                    </div>
                      </td>
                      </tr></table>
            </div>
            <div id="process-workArea">

                    <div id="DataModel"></div>
            </div>
	</div>
	<!-- Diagram here -->
        <div id="omniFooter"><hr />OmniWorkflow Footer</div>
<?php

}

}
