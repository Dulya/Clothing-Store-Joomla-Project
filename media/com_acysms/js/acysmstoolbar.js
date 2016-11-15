/**
 * @package    AcySMS for Joomla!
 * @version    3.1.0
 * @author     acyba.com
 * @copyright  (C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

(function(){
	var topMenu, leftMenu, initialTopMenuacysmsaffix;
	var affixOption = function(){
		var element;
		var topValue = 0;
		var elementsFixed = [];
		var elementsToAffix = [];
		var scroll = window.scrollY || document.documentElement.scrollTop;

		elementsFixed = elementsFixed.concat(_convertToArray(document.getElementsByClassName('navbar-fixed-top')));
		elementsFixed = elementsFixed.concat(_convertToArray(document.getElementsByClassName('affix')));

		for(var i = 0; i < elementsFixed.length; i++){
			if(!hasClassName(elementsFixed[i].className, 'navbar-fixed-top') && !hasClassName(elementsFixed[i].className, 'affix')) continue;
			element = elementsFixed[i].getBoundingClientRect();
			topValue += element.bottom;
		}


		elementsToAffix = elementsToAffix.concat(_convertToArray(document.getElementsByClassName('acysmsaffix-top')));
		elementsToAffix = elementsToAffix.concat(_convertToArray(document.getElementsByClassName('acysmsaffix')));

		for(var i = 0; i < elementsToAffix.length; i++){
			element = elementsToAffix[i].getBoundingClientRect();
			if(element.top <= topValue && scroll != 0){
				element = elementsToAffix[i];
				element.className = element.className.replace('acysmsaffix-top', 'acysmsaffix');
				element.style.top = topValue + 'px';
			}
			if(scroll == 0 || scroll < initialTopMenuacysmsaffix - topValue){
				element = elementsToAffix[i];
				if(element.className.indexOf('acysmsaffix-top') == -1){
					element.className = element.className.replace('acysmsaffix', 'acysmsaffix-top');
				}
				element.style.top = 0;
			}
		}
	};

	window.addEvent('domready', function(){
		topMenu = document.getElementById('acysmsmenu_top');
		leftMenu = document.getElementById('acysmsmenu_leftside');
		initialTopMenuacysmsaffix = topMenu.getBoundingClientRect().top;

		var width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

		if(width > 900){
			affixOption();
			window.addEvent("scroll", affixOption);
		}

	});

	function _convertToArray(collection){
		return [].slice.call(collection);
	}

	function hasClassName(classNames, className){
		var classes = classNames.split(' ');
		for(var i = 0; i < classes.length; i++){
			if(classes[i] == className) return true;
		}
		return false;
	}
})();
