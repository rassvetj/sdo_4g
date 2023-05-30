        /**
        * Function: dynamicSelect
        *
        * Usage:
        *
        * <head>
        *     <script type="text/javascript">
        *     var a1 = [
        *         ['Выберите курс...'],
        *         ['name_a1_1','value_a1_1',[1,2,3]],
        *         ['name_a1_2','value_a1_2',[1]],
        *         ['name_a1_3','value_a1_3'],
        *         ['name_a1_4','value_a1_4',[1,2,3,4]],
        *         ['name_a1_5','value_a1_5',[1]],
        *         ['name_a1_6','value_a1_6',[2,3]],
        *         ['name_a1_7','value_a1_7',[2,3]],
        *         ['name_a1_8','value_a1_8',[2,3]],
        *         ['name_a1_9','value_a1_9',[2,3,4,5,6]]
        *     ]
        *
        *     var a2 = [
        *         ['Выберите слушателя...'],
        *         ['name_a2_1','value_a2_1'],
        *         ['name_a2_2','value_a2_2'],
        *         ['name_a2_3','value_a2_3'],
        *         ['name_a2_4','value_a2_4'],
        *         ['name_a2_5','value_a2_5'],
        *         ['name_a2_6','value_a2_6']
        *     ]
        *     dynamicSelect('s1', 's2', a1, a2)
        *     </script>
        * </head>
        * <body onload="dynamicSelect('s1', 's2', a1, a2)">
        *
        *     <form action="#">
        *         <select id="s1"></select>
        *         <select id="s2"></select>
        *     </form>
        *
        * </body>
        */
        function dynamicSelect(id1, id2, a1, a2, funcName)
        {
            if (dynamicSelect.arguments.length>5) {
                var def = dynamicSelect.arguments[5];
            }    

            if (document.getElementById && document.getElementsByTagName) // check for dom support
            {
                var sel1 = document.getElementById(id1)
                var sel2 = document.getElementById(id2)
                
                // initial fill
                for (var i = 0; i < a1.length; i++)
                {
                    var x = document.createElement('option')
                    x.appendChild(document.createTextNode(a1[i][0]))
                    var v = a1[i][1]
                    if (typeof v != "undefined") {
                        x.value = v
                        if ((typeof def != "undefined") && (def==v)) {
                            x.selected = true
                        }
                    }
                    sel1.appendChild(x)
                }
                
                sel1.onchange = function()
                {
                    while (sel2.options.length)
                        sel2.remove(0)
                    var e = a1[sel1.selectedIndex]
                    
                    var links = new Array()
                    if (typeof e != "undefined" && e.length == 3)
                        links = e[2]
                    
                    if (a2[0].length == 1)
                    {
                        var s = [0]
                        links = s.concat(links)
                    }
                    for (var i = 0; i < links.length; i++)
                    {
                        var x = document.createElement('option')
                        x.appendChild(document.createTextNode(a2[links[i]][0]))
                        var v = a2[links[i]][1]
                        if (typeof v != "undefined")
                            x.value = v
                        sel2.appendChild(x)
                    }
                    if (funcName) {
                        eval(funcName+'();');
                    }
                }
                sel1.onchange()
                sel1.style.width="auto"
                sel2.style.width="auto"
                
           }
        }
