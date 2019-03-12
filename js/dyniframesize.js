//此程式目的在於能自動變更iframe的大小以適合內容
function dyniframesize(iframename) {
	var getFFVersion=navigator.userAgent.substring(navigator.userAgent.indexOf("Firefox")).split("/")[1]
    //extra height in px to add to iframe in FireFox 1.0+ browsers
    var FFextraHeight=getFFVersion>=0.1? 16 : 0
    var pTar = null;
   	if (document.getElementById){
    	pTar = document.getElementById(iframename);
    }
    else{
        eval('pTar = ' + iframename + ';');
    }
    if (pTar && !window.opera){
        //begin resizing iframe
        pTar.style.display="block"

	    if (pTar.contentDocument && pTar.contentDocument.body.offsetHeight){
      	    //ns6 syntax
           	pTar.height = pTar.contentDocument.body.offsetHeight+ FFextraHeight;
       		pTar.height = pTar.contentDocument.body.offsetHeight;   //這行解決 FireFox 重整會變長的問題
	        //無法更動高度這是唯讀屬性  document.body.scrollHeight= pTar.contentDocument.body.offsetHeight;
        }
        else if (pTar.Document && pTar.Document.body.scrollHeight){
            //ie5+ syntax
        	pTar.height = pTar.Document.body.scrollHeight;
		}
	}
}

