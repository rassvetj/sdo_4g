    function clearFields() {

        for(i=0;i<document.forms[0].elements.length;i++) {
            if (document.forms[0].elements[i].type=='text') {
                document.forms[0].elements[i].value='';
            }
            else if (document.forms[0].elements[i].type=='select-one') {
                for(var j=0;j<document.forms[0].elements[i].options.length;j++) {
                    document.forms[0].elements[i].options[j].selected=false;
                }
                document.forms[0].elements[i].options[0].selected=true;
            }
        }
        
        //var cats = document.getElementById('categories');
        //for(var i=0;i<cats.options.length;i++) {
            //cats.options[i].selected=false;
        //}
        //cats.options[0].selected = true;

        //var peoples = document.getElementById('peoples');
        //for(var i=0;i<peoples.options.length;i++) {
            //peoples.options[i].selected=false;
        //}
        //peoples.options[0].selected = true;

    }

    function addCategories() {

        var categories = document.getElementById('categories');
        var cats = document.getElementById('cats');

        var index = cats.options.length;
        for(var i=0;i<categories.options.length;i++) {
            if (categories.options[i].selected) {
                var exists = false;
                categories.options[i].selected = false;
                for(var j=0;j<cats.options.length;j++) {
                    if (cats.options[j].value == categories[i].value) exists = true;
                }
                if (exists) continue;
                if (index < 0) index=0;
                cats.options[index++] = new Option(categories[i].text,categories[i].value);
            }
            categories.options[i].selected = false;
        }

    }

    function removeCategories() {        
        var cats = document.getElementById('cats');
        for(var j=0;j<cats.options.length;j++) {
            if (cats.options[j].selected) {
                cats.options[j] = null;
                j=0;
            }
        }
        if (cats.options[0] && cats.options[0].selected) cats.options[0] = null;
    }

    function selectAllCategories() {
        var cats = document.getElementById('cats');
        if (cats != null) {
            for(var j=0;j<cats.options.length;j++) {
                cats.options[j].selected = true;
            }
        }
    }
