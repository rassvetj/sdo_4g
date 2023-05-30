(function () {

function NewHttpReq() {
    var httpReq = false;
    if (typeof XMLHttpRequest!='undefined') {
        httpReq = new XMLHttpRequest();
    } else {
        try {
            httpReq = new ActiveXObject("Msxml2.XMLHTTP.4.0");
        } catch (e) {
            try {
                httpReq = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (ee) {
                try {
                    httpReq = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (eee) {
                    httpReq = false;
                }
            }
        }
    }
    return httpReq;
}


function DoRequest(url, param, async) {
    var httpReq = NewHttpReq();
    
    // httpReq.open (Method("get","post"), URL(string), Asyncronous(true,false))
    httpReq.open("POST", url, !!async);
    httpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    httpReq.send(param);
    
    if (!async && httpReq.status == 200) {
        return httpReq.responseText;
    } else {
        return "true\n0";
    }
}

window.DoRequest = DoRequest;

})();

function popupwin(content) {
    op = window.open();
    op.document.open('text/plain');
    op.document.write(content);
    op.document.close();
}
