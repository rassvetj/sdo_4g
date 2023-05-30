    
    function select_list_cmp_by_text(a,b) {
        if (a.text < b.text) return -1;
        if (a.text > b.text) return 1;
        return 0;
    }

    function select_list_cmp_by_value(a,b) {
        if (a.value < b.value) return -1;
        if (a.value > b.value) return 1;
        return 0;
    }

    function select_list_move(elm1,elm2,sort_func) {

        var list1 = document.getElementById(elm1);
        var list2 = document.getElementById(elm2);
        
        var arr1 = new Array(), arr2 = new Array();

        var obj, obj2, i;
        for(i=0; i<list1.length; ++i) {
            obj = list1.options[i];
            obj2 = new Option(obj.text, obj.value);
            obj2.parent = obj.parent;
            obj2.style.background = obj.style.background;
            obj2.dontmove = obj.dontmove;
            if(obj.selected && (obj.dontmove!='dontmove'))  {
                arr2[ arr2.length ] = obj2;
                if (obj.parent=='true') {
                    i++;
                    while(i<list1.length) {
                        obj = list1.options[i];
                        if (obj.parent=='false') {
                            obj2 = new Option(obj.text, obj.value);
                            obj2.parent = obj.parent;
                            obj2.style.background = obj.style.background;
                            obj2.dontmove = obj.dontmove;
                            arr2[arr2.length] = obj2;
                        } else break;
                        i++;
                    }
                    i--;
                }
            }
            else
            arr1[ arr1.length ] = obj2;
        }

        for(i=0;i<list2.length;++i) {
            obj = list2.options[i];
            obj2 = new Option(obj.text, obj.value);
            obj2.parent = obj.parent;
            obj2.style.background = obj.style.background;
            obj2.dontmove = obj.dontmove;
            arr2[ arr2.length ] = obj2;
        }

        eval("arr2.sort( "+sort_func+");");
//        arr2.sort( select_list_cmp_by_text );

        list2.length = list1.length = 0;

        for(i=0; i<arr1.length; i++)
        list1.options[ list1.length ] = arr1[i];
        for(i=0; i<arr2.length; i++)
        list2.options[ list2.length ] = arr2[i];    
    }

    function select_list_select_all(elm) {
        var cats = document.getElementById(elm);
        for(var j=0;j<cats.options.length;j++) {
            cats.options[j].selected = true;
        }
    }

    function select_list_clear(elm1, elm2) {        
        var list1 = document.getElementById(elm1);
        var list2 = document.getElementById(elm2);
        var arr1 = new Array();
        var arr2 = new Array();
        if (list1 && list2) {
            if (list2.length && list1.length) {
                for(i=0; i<list2.length;i++) {
                   arr2[list2.options[i].value] = list2.options[i].value; 
                }
                for(i=0; i<list1.length;i++) {
                   if (typeof(arr2[list1.options[i].value])=='undefined') {
                      arr1.push(list1.options[i]);  
                   }
                }
                list1.length=0;
                for(i=0; i<arr1.length; i++) {
                    list1.options[list1.length] = arr1[i];
                }
            }
        }        
    }
