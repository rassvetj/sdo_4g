function chain_add_item(source, dest) {    
    var source = document.getElementById(source);
    var dest = document.getElementById(dest);
    var obj;

    if (source && dest) {
        obj = source.options[source.selectedIndex];
        for(var i=0;i<dest.length;i++) {
            if (dest.options[i].value == obj.value) return;
        }
        obj2 = new Option(obj.text, obj.value);
        dest.options[dest.options.length] = obj2;
   }
}

function chain_select_all(elm) {
    var obj = document.getElementById(elm);
    for(var j=0;j<obj.options.length;j++) {
        obj.options[j].selected = true;
    }
}

function chain_select_clear(elm) {
    var obj = document.getElementById(elm);
    obj.length = 0;
}

function chain_select_clear_item(elm) {
    var arr = new Array();
    var obj = document.getElementById(elm);
    for (var i=0;i<obj.length;i++) {
        if (obj.options[i].selected!=true) 
            arr[arr.length] = obj.options[i];
    }
    
    obj.length = 0;

    for(i=0; i<arr.length; i++)
        obj.options[ obj.length ] = arr[i];

}
