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
            <div id='diagramContents' style="position: relative; width: 100%;">
            <?php 
            $imageFile = 'processes/'.str_replace(".bpmn", ".svg",$file);
            SVGHandler::displayDiagram($proc,array());
            ?>
            </div>
            <!-- end of diagram -->
            <div id="proessItems" style="position: relative; width: 100%;">
                          <div id="ItemsList">
                            </div> <!-- end of Items list -->
                                    <div id="itemDetails">
                                    </div>
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
