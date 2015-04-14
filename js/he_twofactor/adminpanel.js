//jquery substitute for document ready (no guarantee that either jquery OR prototype will be available)
(function(g,b){function c(){if(!e){e=!0;for(var a=0;a<d.length;a++)d[a].fn.call(window,d[a].ctx);d=[]}}function h(){"complete"===document.readyState&&c()}b=b||window;var d=[],e=!1,f=!1;b[g||"he_twofactor_ready"]=function(a,b){e?setTimeout(function(){a(b)},1):(d.push({fn:a,ctx:b}),"complete"===document.readyState?setTimeout(c,1):f||(document.addEventListener?(document.addEventListener("DOMContentLoaded",c,!1),window.addEventListener("load",c,!1)):(document.attachEvent("onreadystatechange",h),window.attachEvent("onload",
c)),f=!0))}})("he_twofactor_ready",window);
//on document ready
he_twofactor_ready(function() {
  //if the provider selector is on this page
  var providerSelect=document.getElementById('he2faconfig_control_provider');
  if(providerSelect!=undefined){
    //FUNCTION: cache the toggle-able elements on initial page load
    var toggleElems;
    var cacheToggleSections=function(){
      toggleElems=[];
      //for each available select option
      var options=providerSelect.getElementsByTagName('option');
      for(var s=0;s<options.length;s++){
        //if this is NOT the first (disabled option)
        var oVal=options[s].value;
        if(oVal!=undefined&&oVal.length>0&&oVal.toLowerCase().indexOf('disable')!=0){
          //get the a link for this toggle-able section (if exists)
          var alink=document.getElementById('he2faconfig_'+oVal+'-head');
          //if this link exists
          if(alink!=undefined){
            //get the section wrap
            var wrap=alink.parentNode.parentNode;
            //if the wrap exists
            if(wrap!=undefined){
            		var hideShowElems=[];
            		//if this wrap is the entry-edit div (older version of magento has different html)
            		if(wrap.className.indexOf('entry-edit')!==-1){
            			//toggle hide/show link and fieldset elements separately because there is no common parent element in this version of magento
					hideShowElems.push(alink.parentNode);
					var fieldset=document.getElementById('he2faconfig_'+oVal);
					hideShowElems.push(fieldset);
            		}else{
            			//toggle hiding and showing this wrapper for this version of magento
            			hideShowElems.push(wrap);
            		}
              //cache the toggle-able elements for this select item
              toggleElems.push({'key':oVal,'wrap':wrap,'alink':alink,'toggleList':hideShowElems});
            }
          }else{
            console.log('"'+oVal+'" does NOT appear as an admin section although it appears in the provider selection dropdown!');
          }
        }
      }
    };
    //FUNCTION: update which wrapped section is visible, based on current provider selection
    var updateVisibleWrap=function(){
      //if the toggle-able wraps are cached
      if(toggleElems!=undefined){
        //if providerSelect is loaded (if exists)
        if(providerSelect!=undefined){
          //get the selected value
          var selKey=providerSelect.value;
          //for each toggle-able wrap
          for(var w=0;w<toggleElems.length;w++){
            //get the toggle-able wrap's data
            var dat=toggleElems[w];
            //if this wrap is selected to be visible
            if(dat['key']==selKey){
              //show each of the elements in the toggle list
              for(var i=0;i<dat['toggleList'].length;i++){
              	//show this toggle list element
              	dat['toggleList'][i].style.display='block';
              }
              //if this browser supports classList.contains
              if(dat['wrap'].classList){
                if(dat['wrap'].classList.contains){
                  //if the class list does NOT contain active
                  if(!dat['wrap'].classList.contains('active')){
                    //open the panel
                    dat['alink'].onclick();
                  }
                }
              }
            }else{
              //DESELECTED section... hide the panel wrapper

              //hide each of the elements in the toggle list
              for(var i=0;i<dat['toggleList'].length;i++){
              	//hide this toggle list element
              	dat['toggleList'][i].style.display='none';
              }
            }
          }
        }
      }
    };
    //==STUFF TO DO ON PAGE LOAD==
    //attach the onchange event to the provier dropdown
    providerSelect.onchange=function(){
      //make the selected section visible (if any) and hide any (deselected) sections
      updateVisibleWrap();
    };
    //cache list of toggle-able wraps
    cacheToggleSections();
    //make the selected section visible (if any) and hide any (deselected) sections
    updateVisibleWrap();
  }
});
