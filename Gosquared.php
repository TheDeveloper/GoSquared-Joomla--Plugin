<?php
/**
 * GoSquared Plugin for Joomla!
 * @version		0.2
 * @copyright   Copyright (C) 2010 GoSquared Ltd. All rights reserved.
 * @author		Aaron Parker <parkeyparker@gmail.com> and Geoff Wagstaff <geoff@gosquared.com>
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

//No direct access is allowed
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgSystemGosquared extends JPlugin {
	function plgSystemGosquared(&$subject, $config) {
		parent::__construct($subject, $config);
	}
	
	function onAfterRender() {
		//Get params
		$acct = $this->params->get('acct', 'GSN-000000-X');
		$runAdmin = $this->params->get('runAdmin', '1');
		$name = $this->params->get('name', '1');
		
		//First of all check if the acct code is valid.
		if($acct !='' && $acct != 'GSN-000000-X' && preg_match('/GSN-[0-9]{6,7}-[A-Z]{1}/', $acct)) {
			//acct code is valid so continue
			
			//Check if runAdmin is false, if so check if this is an admin page
			if(!$runAdmin) {
				global $mainframe;
				if($mainframe->isAdmin()) {
					return; //This is an admin page so don't run
				}
			}

			//Now get the page
			$buffer = JResponse::getBody();
			//If the user is not a guest then find their username
			$user =& JFactory::getUser();
			if(!$user->guest && $name != 0) {
				//name->0 is ID; name->1 is username; name->2 is real name
				switch ($name) {
					case 1:
						$GSUsername = $user->id;
						break;
						
					case 2:
						$GSUsername = $user->username;
						break;
						
					case 3:
						$GSUsername = $user->name;
						break;
					
					default:
						$GSUsername = '';
						break;
				}
			}
			//Set the initial JS
			$GSJavascript = '

		<!--LiveStats Joomla! Plugin-->
		<script type="text/javascript">
					var GoSquared={};
					GoSquared.acct = "' . $acct . '";
					GoSquared.TrackDelay = 0;
					';
					if($GSUsername) {
						$GSJavascript .= 'GoSquared.VisitorName = "' . $GSUsername . '";
						';
					}
					$GSJavascript .= '(function(w){
				    function gs(){
				    	w._gstc_lt=+(new Date); var d=document;
				        var g = d.createElement("script"); g.type = "text/javascript"; g.async = true; g.src = "//d1l6p2sc9645hc.cloudfront.net/tracker.js";
				        var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(g, s);
				    }
				    w.addEventListener?w.addEventListener("load",gs,false):w.attachEvent("onload",gs);
				})(window);
		</script>
		<!--End LiveStats Joomla! Plugin-->

		';
			//Now add in GSJavascript to the buffered page just before the </body> tag
			$buffer = str_replace('</body>', $GSJavascript . '</body>', $buffer);
			JResponse::setBody($buffer);

			return true;
		}
	}
}
?>