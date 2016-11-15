/**
 * @package    AcySMS for Joomla!
 * @version    3.1.0
 * @author     acyba.com
 * @copyright  (C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

function submitacysmsform(task,formName){
	var varform = document[formName];
	var filterPhone = /^(\+[0-9]+)+$/i;

	if(!varform.elements){
		if(varform[0].elements['user[user_phone_number]'] && varform[0].elements['user[user_phone_number]'].value && filterPhone.test(varform[0].elements['user[user_phone_number]'].value)){
			varform = varform[0];
		}else{
			varform = varform[varform.length - 1];
		}
	}

	if(task != 'optout'){
		if(varform.elements['hiddengroups'].value.length < 1){
			var groupschecked = false;
			var allgroups = varform.elements['subscription[]'];
			if(allgroups && typeof allgroups.value == 'undefined'){
				for(b=0;b<allgroups.length;b++){
					if(allgroups[b].checked) groupschecked = true;
				}
				if(!groupschecked){ alert(acysms['SMS_NO_GROUP_SELECTED']); return false;}
			}
		}
	}


	if(task != 'optout' && typeof acysms != 'undefined' && typeof acysms['reqFields'+formName] != 'undefined' && acysms['reqFields'+formName].length > 0){

		for(var i =0;i<acysms['reqFields'+formName].length;i++){
			elementName = 'user['+acysms['reqFields'+formName][i]+']';
			elementToCheck = varform.elements[elementName];
			if(elementToCheck){
				var isValid = false;
				if(typeof elementToCheck.value != 'undefined'){
					if(elementToCheck.value==' ' && typeof varform[elementName+'[]'] != 'undefined'){
						if(varform[elementName+'[]'].checked){
							isValid = true;
						}else{
							for(var a=0; a < varform[elementName+'[]'].length; a++){
								if((varform[elementName+'[]'][a].checked || varform[elementName+'[]'][a].selected) && varform[elementName+'[]'][a].value.length>0) isValid = true;
							}
						}
					}else{
						if(elementToCheck.value.replace(/ /g,"").length>0){
							if(typeof acysms['excludeValues'+formName] == 'undefined' || typeof acysms['excludeValues'+formName][acysms['reqFields'+formName][i]] == 'undefined' || acysms['excludeValues'+formName][acysms['reqFields'+formName][i]] != elementToCheck.value) isValid = true;
						}
					}
				}else{
					for(var a=0; a < elementToCheck.length; a++){
						 if(elementToCheck[a].checked && elementToCheck[a].value.length>0) isValid = true;
					}
				}
				if(!isValid){
					elementToCheck.className = elementToCheck.className +' invalid';
					alert(acysms['validFields'+formName][i]);
					return false;
				}
			}else{
				if((varform.elements[elementName+'[day]'] && varform.elements[elementName+'[day]'].value<1) || (varform.elements[elementName+'[month]'] && varform.elements[elementName+'[month]'].value<1) || (varform.elements[elementName+'[year]'] && varform.elements[elementName+'[year]'].value<1902)){
					if(varform.elements[elementName+'[day]'] && varform.elements[elementName+'[day]'].value<1) varform.elements[elementName+'[day]'].className = varform.elements[elementName+'[day]'].className + ' invalid';
					if(varform.elements[elementName+'[month]'] && varform.elements[elementName+'[month]'].value<1) varform.elements[elementName+'[month]'].className = varform.elements[elementName+'[month]'].className + ' invalid';
					if(varform.elements[elementName+'[year]'] && varform.elements[elementName+'[year]'].value<1902) varform.elements[elementName+'[year]'].className = varform.elements[elementName+'[year]'].className + ' invalid';
					alert(acysms['validFields'+formName][i]);
					return false;
				}

				if((varform.elements[elementName+'[phone_country]'] && varform.elements[elementName+'[phone_country]'].value<1) || (varform.elements[elementName+'[phone_num]'] && varform.elements[elementName+'[phone_num]'].value<3)){
					if(varform.elements[elementName+'[phone_country]'] && varform.elements[elementName+'[phone_country]'].value<1) varform.elements[elementName+'[phone_country]'].className = varform.elements[elementName+'[phone_country]'].className + ' invalid';
					if(varform.elements[elementName+'[phone_num]'] && varform.elements[elementName+'[phone_num]'].value<3) varform.elements[elementName+'[phone_num]'].className = varform.elements[elementName+'[phone_num]'].className + ' invalid';
					alert(acysms['validFields'+formName][i]);
					return false;
				}
			}
		}
	}
	if(task != 'optout' && typeof acysms != 'undefined' && typeof acysms['checkFields'+formName] != 'undefined' && acysms['checkFields'+formName].length > 0){
		 for(var i =0;i<acysms['checkFields'+formName].length;i++){
			elementName = 'user['+acysms['checkFields'+formName][i]+']';
			elementtypeToCheck = acysms['checkFieldsType'+formName][i];
			elementToCheck = varform.elements[elementName].value;
			switch(elementtypeToCheck){
				case 'number':
					myregexp = new RegExp('^[0-9]*$');
					break;
				case 'letter':
					myregexp = new RegExp('^[A-Za-z\u00C0-\u017F ]*$');
					break;
				case 'letnum':
					myregexp = new RegExp('^[0-9a-zA-Z\u00C0-\u017F ]*$');
					break;
				case 'email':
					myregexp = new RegExp(/^([a-z0-9_&'\.\-\+=])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,10})+$/i);
					break;
				case 'regexp':
					myregexp = new RegExp(acysms['checkFieldsRegexp'+formName][i]);
					break;
			}
			if(!myregexp.test(elementToCheck)){
				alert(acysms['validCheckFields'+formName][i]);
				return false;
			}
		}
	 }

	var captchaField = varform.elements['acycaptcha'];
	if(captchaField){
		if(captchaField.value.length<1){
			if(typeof acysms != 'undefined'){ alert(acysms['CAPTCHA_MISSING']); }
			captchaField.className = captchaField.className +' invalid';
					return false;
		}
	}

	if(task != 'optout'){
		var termsandconditions = varform.terms;
		if(termsandconditions && !termsandconditions.checked){
			if(typeof acysms != 'undefined'){ alert(acysms['ACCEPT_TERMS']); }
			termsandconditions.className = termsandconditions.className +' invalid';
			return false;
		}
	}

	taskField = varform.task;
	taskField.value = task;

	if(!varform.elements['ajax'] || !varform.elements['ajax'].value || varform.elements['ajax'].value == '0'){
		varform.submit();
		return false;
	}

	try{
		var form = document.id(formName);
	}catch(err){
		var form = $(formName);
	}
	data = form.toQueryString();

	if (typeof Ajax == 'function'){
		new Ajax(form.action, {
			data: data,
			method: 'post',
			onRequest: function()
			{
				form.addClass('acysms_module_loading');
				form.setStyle("filter:","alpha(opacity=50)");
				form.setStyle("-moz-opacity","0.5");
				form.setStyle("-khtml-opacity", "0.5");
				form.setStyle("opacity", "0.5");
			},
			onSuccess: function(response)
			{
				response = Json.evaluate(response);
				acysmsDisplayAjaxResponse(unescape(response.message), response.type, formName);
			},
			onFailure: function(){
				acysmsDisplayAjaxResponse('Ajax Request Failure', 'error', formName);
			}
		}).request();
	}else{
		new Request.JSON({
			url: document.id(formName).action,
			data: data,
			method: 'post',
			onRequest: function()
			{
				form.addClass('acysms_module_loading');
				form.setStyle("filter:","alpha(opacity=50)");
				form.setStyle("-moz-opacity","0.5");
				form.setStyle("-khtml-opacity", "0.5");
				form.setStyle("opacity", "0.5");
			},
			onSuccess: function(response)
			{
				acysmsDisplayAjaxResponse(unescape(response.message), response.type, formName);
			},
			onFailure: function(){
				acysmsDisplayAjaxResponse('Ajax Request Failure', 'error', formName);
			}
		}).send();
	}

	return false;
}

function acysmsDisplayAjaxResponse(message, type, formName)
{
	try{
		var toggleButton = document.id('acysms_togglemodule_'+formName);
	}catch(err){
		var toggleButton = $('acysms_togglemodule_'+formName);
	}

	if (toggleButton && toggleButton.hasClass('acyactive')) {
		var wrapper = toggleButton.getParent().getParent().getChildren()[1];
		wrapper.setStyle('height', '');
	};

	try{
		var responseContainer = document.getElements('#acysms_fulldiv_'+formName+' .responseContainer')[0];
	}catch(err){
		var responseContainer = $$('#acysms_fulldiv_'+formName+' .responseContainer')[0];
	}

	if (typeof responseContainer == 'undefined'){
		responseContainer = new Element('div');
		try{
			var fulldiv = document.id('acysms_fulldiv_'+formName);
		}catch(err){
			var fulldiv = $('acysms_fulldiv_'+formName);
		}
		responseContainer.inject(fulldiv, 'top');
		oldContainerHeight = '0px';
	}else{
		oldContainerHeight = responseContainer.getStyle('height');
	}

	responseContainer.className = 'responseContainer';

	try{
		var form = document.id(formName);
	}catch(err){
		var form = $(formName);
	}
	form.removeClass('acysms_module_loading');

	responseContainer.innerHTML = message;

	if(type == 'success'){
		responseContainer.addClass('acysms_module_success');
	}else{
		responseContainer.addClass('acysms_module_error');
		form.setStyle("filter:","alpha(opacity=100)");
		form.setStyle("-moz-opacity","1");
		form.setStyle("-khtml-opacity", "1");
		form.setStyle("opacity", "1");
	}

	newContainerHeight = responseContainer.getStyle('height');

	if (typeof Ajax == 'function')
	{
		if(type == 'success'){
			var myEffect = new Fx.Styles(form, {duration: 500, transition: Fx.Transitions.linear});
			myEffect.start({
				'height': [form.getSize().size.y, 0],
				'opacity': [1, 0]
			});
		}

		try {
			responseContainer.setStyle('height', oldContainerHeight+'px');
			responseContainer.setStyle("filter:","alpha(opacity=0)");
			responseContainer.setStyle("-moz-opacity","0");
			responseContainer.setStyle("-khtml-opacity", "0");
			responseContainer.setStyle("opacity", "0");
		}
		catch (e) {}

		var myEffect2 = new Fx.Styles(responseContainer, {duration: 500, transition: Fx.Transitions.linear});
		myEffect2.start({
			'height': [oldContainerHeight, newContainerHeight],
			'opacity': [0, 1]
		});

	}
	else // Mootools >= 1.2
	{
		if(type == 'success'){
			form.set('morph');
			form.morph({
				'height': '0px',
				'opacity': 0
			});

			form.setStyles({
				'display': 'none'
			});
		}

		responseContainer.setStyles({
			'height': oldContainerHeight,
			'opacity': 0
		});

		responseContainer.set('morph');
		responseContainer.morph({
			'height': newContainerHeight,
			'opacity': 1
		});

	}
}
