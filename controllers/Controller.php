<?php
namespace OmniFlow;


class Controller
{
	
public function headerDelete($menus=true,$modeler=false)
{
	
/*	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); 

<link rel="stylesheet" href="css\workflow.css" type="text/css">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/workflow.js"></script>
<link rel="stylesheet" href="lib/jquery-ui/jquery-ui.css">
<link rel="stylesheet" href="lib/jquery-ui/jquery-ui.theme.css">
<script src="lib/jquery-ui/jquery-ui.min.js"></script>
	
	*/
	Helper::HeaderInclude('',$modeler);
if ($menus)
	MenusView::displayMenus();
}
/*
 * 
 * 
 * 
 */
public function DisplayErrors()
{
        $msgs=Context::getInstance()->errors;
        foreach($msgs as $msg)
        {
            echo '<br />'.$msg;
        }


}
public function Action($req=null)
{
    
    $user=  Context::getUser();
    
    
 /*   if (!$user->isLoggedIn())
    {
        $this->login();
        return;
    } */
       try {
      
       $this->doAction($req);
       QueueEngine::checkQueue();
       
       } 
    catch (\Exception $ex) {
        Context::Exception($ex);
        }

}
public function login()
{
 ?>
Please Login
<form >
    
</form>
<?php
}
public function doAction($req=null)
{
	if ($req==null)
		$req=$_REQUEST;
	
	
	Context::Log(INFO,'--------------------');
	Context::Log(INFO,"Controller".print_r($req,true));
	
	if (!isset($req["action"]))
	{
		Views::header();
                Views::endPage();
		return;
	}

	$action=$req["action"];

        $pos=strpos($action,'.');
        if ($pos !== false) {        
            $className=__NAMESPACE__.'\\'.ucwords(substr($action,0,$pos)).'Controller';
            $methodName='Action_'.substr($action,$pos+1);
            
            Context::Log(INFO, $className.' method:'.$methodName);
            $contr=new $className();
            if (method_exists($contr, $methodName))
            {
                $contr->$methodName($req);
                QueueEngine::checkQueue();

                return;
            }
        }
        $methodName='Action_'.$action;
        if (method_exists($this, $methodName))
        {
            $this->$methodName();
            QueueEngine::checkQueue();
            return;
        }
        
	$v=new Views();

	switch ($action)
	{
	case "getDataTree":
		header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="iso-8859-1"?>
<tree id="0" radio="1">
	<item   text="Books" id="books" open="1">
		<item text="Mystery &amp; Thrillers" id="mystery">
			<item text="Lawrence Block" id="lb">
				<item text="All the Flowers Are Dying" id="lb_1"></item>
				<item text="The Burglar on the Prowl" id="lb_2"></item>
				<item text="The Plot Thickens" id="lb_3"></item>
				<item text="Grifters Game" id="lb_4"></item>
				<item text="The Burglar Who Thought He Was Bogart" id="lb_5"></item>
			</item>
			<item text="Robert Crais" id="rc">
				<item text="The Forgotten Man" id="rc_1"></item>
				<item text="Stalking the Angel" id="rc_2"></item>
				<item text="Free Fall" id="rc_3"></item>
				<item text="Sunset Express" id="rc_4"></item>
				<item text="Hostage" id="rc_5"></item>
			</item>
			<item text="Ian Rankin" id="ir"></item>
			<item text="James Patterson" id="jp"></item>
			<item text="Nancy Atherton" id="na"></item>
		</item>
		<item text="History" id="history">
			<item text="John Mack Faragher" id="jmf"></item>
			<item text="Jim Dwyer" id="jd"></item>
			<item text="Larry Schweikart" id="ls"></item>
			<item text="R. Lee Ermey" id="rle"></item>
		</item>
		<item text="Horror" id="horror" open="1">
			<item text="Stephen King" id="sk"></item>
			<item text="Dan Brown" id="db">
				<item text="Angels &amp; Demons" id="db_1"></item>
				<item text="Deception Point" id="db_2"></item>
				<item text="Digital Fortress" id="db_3"></item>
				<item text="The Da Vinci Code" id="db_4"></item>
				<item text="Deception Point" id="db_5"></item>
			</item>
			<item text="Mary Janice Davidson" id="mjd"></item>
			<item text="Katie Macalister" id="km"></item>
		</item>
		<item text="Science Fiction &amp; Fantasy" id="fantasy">
			<item text="Audrey Niffenegger" id="af"></item>
			<item text="Philip Roth" id="pr"></item>
		</item>
		<item text="Sport" id="sport">
			<item text="Bill Reynolds" id="br"></item>
		</item>
		<item text="Teens" id="teens">
			<item text="Joss Whedon" id="jw">
				<item text="Astonishing X-Men" id="jw_1"></item>
				<item text="Joss Whedon: The Genius Behind Buffy" id="jw_2"></item>
				<item text="Fray" id="jw_3"></item>
				<item text="Tales Of The Vampires" id="jw_4"></item>
				<item text="The Harvest" id="jw_5"></item>
			</item>
			<item text="Meg Cabot" id="mc"></item>
			<item text="Garth Nix" id="gn"></item>
			<item text="Ann Brashares" id="ab"></item>
		</item>
	</item>
</tree>';

		break;
	case "help":
		Views::header();
		$v->ShowHelp();
                Views::endPage();
		break;
        default :
		Views::header();
            echo 'No such action:'.$action;
                Views::endPage();
	}
        
        QueueEngine::checkQueue();
}

function getProcessTypes()
{
	$arr=Array();
	
if ($handle = opendir(Config::getConfig()->processPath)) {

	/* This is the correct way to loop over the directory. */
	while (false !== ($entry = readdir($handle))) {
		if (substr($entry, -5)==".bpmn")
			$arr[]=$entry;
	}
	closedir($handle);
 }
 return $arr;
}

}
?>