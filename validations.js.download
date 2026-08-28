	function updateUrl() {
			var currImg = $('.curr_img');
			currImg.attr("src",currImg.attr("src") + "&ts="+ (new Date).getTime());
	}
	
    function trim(stringToTrim) {
        return stringToTrim.replace(/^\s+|\s+$/g,"");
    }
    function ltrim(stringToTrim) {
        return stringToTrim.replace(/^\s+/,"");
    }
    function rtrim(stringToTrim) {
        return stringToTrim.replace(/\s+$/,"");
    }
    
function validateEmail(inputField,outputField,formName,hideField) {
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
 
    var email = $("#"+formName+"\\:"+inputField).val();
    
 	if( emailReg.test( email )) {
       $("#"+outputField).text("");
    } else {
      $("#"+outputField).text("Invalid Email Address!");
      setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);

   }
 }
function validateCNIC(inputField,outputField,formName,hideField)
{
	 var cnicReg = /^^(99999-9999999-9|0{5}-0{7}-0|1{5}-1{7}-1|2{5}-2{7}-2|3{5}-3{7}-3|4{5}-4{7}-4|5{5}-5{7}-5|6{5}-6{7}-6|7{5}-7{7}-7|8{5}-8{7}-8)$$/;
	 
	    var cnic = $("#"+formName+"\\:"+inputField).val();
	    
	    if (cnicReg.test(cnic)) {
	    	$("#"+outputField).text("Invalid Citizen Number");
		      setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	       
	    } else {	      
	    	$("#"+outputField).text("");
	   }
}

function requiredOnlyDigits(inputField,outputField,formName,hideField,fieldName) 
{
	var chk = $("#"+formName+"\\:"+inputField).val();
	if(trim(chk).length==0)
	{
		 $("#"+outputField).text(fieldName +" required");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else if(!hasOnlyDigits(chk))
	{
		 $("#"+outputField).text("Invalid Characters");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else
	{
		$("#"+outputField).text("");
	}
}
function onlyDigits(inputField,outputField,formName,hideField,fieldName) 
{
	var chk = $("#"+formName+"\\:"+inputField).val();
	
	if(trim(chk).length==0)
	{
		$("#"+outputField).text(""); 
	}
	else if(chk.match(/^\s+$/))
	{
		 $("#"+outputField).text("Invalid Characters");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else if(!hasOnlyDigits(chk))
	{
		 $("#"+outputField).text("Invalid Characters");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else
	{
		$("#"+outputField).text("");
	}
}
function requiredOnlyCharacters(inputField,outputField,formName,hideField,fieldName) 
{
	var chk = $("#"+formName+"\\:"+inputField).val();
	if(trim(chk).length==0)
	{
		 $("#"+outputField).text(fieldName +" required");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else if(!hasOnlyCharacters(chk))
	{
		 $("#"+outputField).text("Invalid Characters");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else
	{
		$("#"+outputField).text("");
	}
}
function onlyCharacters(inputField,outputField,formName,hideField,fieldName) 
{
	var chk = $("#"+formName+"\\:"+inputField).val();
	if(chk.length==0)
	{
		$("#"+outputField).text("");
	}
	else if(chk.match(/^\s+$/))
	{
		 $("#"+outputField).text("Invalid Characters");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else if(!hasOnlyCharacters(chk))
	{
		 $("#"+outputField).text("Invalid Characters");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else
	{
		$("#"+outputField).text("");
	}
}
function onlyAlphaNumeric(inputField,outputField,formName,hideField,fieldName) 
{
	var chk = $("#"+formName+"\\:"+inputField).val();
	if(chk.length==0)
	{
		$("#"+outputField).text("");
	}
	else if(chk.match(/^\s+$/))
	{
		 $("#"+outputField).text("Invalid Characters");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else if(!hasOnlyAlphaNumeric(chk))
	{
		 $("#"+outputField).text("Invalid Characters");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else
	{
		$("#"+outputField).text("");
	}
}
function matchPassword(inputField,matchField,outputField,formName,hideField)
{
	var pass=$("#"+formName+"\\:"+inputField).val();
	var matPass=$("#"+formName+"\\:"+matchField).val();
	if(pass==matPass)
	{
		$("#"+outputField).text("");
	}
	else
	{
		 $("#"+outputField).text("Password does not match");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
}
function matchEmail(inputField,matchField,outputField,formName,hideField)
{
	var email=$("#"+formName+"\\:"+inputField).val();
	var matEmail=$("#"+formName+"\\:"+matchField).val();
	if(email==matEmail)
	{
		$("#"+outputField).text("");
	}
	else
	{
		 $("#"+outputField).text("Email does not match");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
}
function checkPassword(inputField,outputField,formName,hideField)
{
	var password = $("#"+formName+"\\:"+inputField).val();
	
	var valid = password.length >= 12 &&
	hasUpperCaseCharacter(password) &&
	hasLowerCaseCharacter(password) &&
	hasNumericCharacter(password) &&
	hasSpecialCharacter(password);
	if(password.length==0)
	{
		 $("#"+outputField).text("Password required");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
	}
	else if (!valid)
	{
		 $("#"+outputField).text("Password must be at least 12 characters and must contain an upper case character, a lower case character, a numeric character, and a special character !@#$%^&*()");
	     setTimeout ( $("#"+formName+"\\:"+hideField).hide(), 100);
		//alert("Password must be at least 12 characters and must contain an upper case character, a lower case character, a numeric character, and a special character (!@#$%^&*().");
	}
	else
	{
		 $("#"+outputField).text("");
	}
}
function hasOnlyCharacters(val)
{
	return val.match("^[a-zA-Z ]+$") != null;
}
function hasOnlyAlphaNumeric(val)
{
	return val.match("^[a-zA-Z0-9 ]+$") != null;
}
function hasOnlyDigits(val)
{
	return val.match("^[0-9]+$") != null;
}
function hasUpperCaseCharacter(password)
{
return password.match("\[A-Z\]") != null;
}
 
function hasLowerCaseCharacter(password)
{
return password.match("\[a-z\]") != null;
}
 
function hasNumericCharacter(password)
{
return password.match("\[0-9\]") != null;
}
 
function hasSpecialCharacter(password)
{
return password.match("\[!@#$%^&*()\]") != null;
}