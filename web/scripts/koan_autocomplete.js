
// KOAN autocomplete plugin
    /*
    jQuery(function () {
        function _initAutocomplete() {
            jQuery(".artist-auto").each(function () {
                new Autocomplete(jQuery(this));
            });
        }
    });
    */

    function Autocomplete(autocomplete) {
        // html elements
        autocomplete.input = autocomplete.find(".auto-input");
        autocomplete.link = autocomplete.find(".auto-link");
        autocomplete.reset_button = autocomplete.find(".auto-reset");
        autocomplete.list = autocomplete.find(".auto-list");
        autocomplete.hidden_id = autocomplete.find(".auto-id");
        autocomplete.hidden_name = autocomplete.find(".auto-name");
        autocomplete.action_list = autocomplete.attr("action_list");
        autocomplete.action_link = autocomplete.attr("action_link");

        // properties
        autocomplete.is_set = function () {
            return ((autocomplete.hidden_id.val() != undefined) && (autocomplete.hidden_id.val().length > 0));
        }

        // event handlers
        autocomplete.input
                            .blur(function () {
                                autocomplete.set_display();
                                jQuery(this).val("");
                            })
                            .keyup(function () {
                                var source = jQuery(this);
                                if (source.val().length > 1) {
                                    autocomplete.show_list();
                                }
                            });

        autocomplete.reset_button.click(function () {
            autocomplete.reset();
        });

        //protected methods

        autocomplete.reset = function () {
            autocomplete.hidden_id.val("").trigger('change');
            autocomplete.hidden_name.val("");
            autocomplete.input.val("").show().focus();
            autocomplete.list.hide();
            autocomplete.link.hide();
            autocomplete.reset_button.hide();
        }

        autocomplete.set_display = function () {
            var id = autocomplete.find(".auto-id").val();
            var name = autocomplete.find(".auto-name").val();
            var link = autocomplete.action_link + id;

            autocomplete.input.toggle(!autocomplete.is_set());
            autocomplete.link.text(name).attr("href", link).toggle(autocomplete.is_set());
            autocomplete.reset_button.toggle(autocomplete.is_set());
            autocomplete.list.toggle(false);
        }

        autocomplete.show_list = function () {
            var action = autocomplete.action_list;
            var text = autocomplete.input.val();
            jQuery.post(action, { "text": text },
                function (listItems) {
                    var html = "";
                    for (var i = 0; i < listItems.length; i++) {
                        html += _getHtmlForAutocompleteItem(listItems[i], i, text);
                    }
                    autocomplete.list.html(html).show();

                    autocomplete.find(".auto-item").mousedown(function () {
                        var item = jQuery(this);
                        var id = item.attr("value");
                        var name = item.attr("text");
                        autocomplete.hidden_id.val(id).trigger('change');
                        autocomplete.hidden_name.val(name);
                        autocomplete.set_display();
                    });
                }
            );
        } 
            // init
            autocomplete.set_display();

            return autocomplete;
    }

    function _getHtmlForAutocompleteItem(dataItem, index, search) {
        var html = "";
        html += "<div class='auto-item";
        if ((index % 2) == 1) {
            html += " alt";
        }
        var text = dataItem.Name;
        text = text.replace(/\'/g, "&#39;") // simple quote replace

        html += "' value='" + dataItem.Id;
        html += "' text='" + text + "'>";

        
        var index = text.toUpperCase().indexOf(search.toUpperCase());
        if (index != -1) {
            html += text.substr(0, index) + "<b>" + text.substr(index, search.length) + "</b>" + text.substr(index + search.length);
        }
        else {
            html += text;
        }
        
        html += "</div>";

        return html;
    }


    /* Autocomplete 
    ------------------------------------------------------

    function initAutocomplete() {
    // init keypressed handler
    jQuery(".artist-auto .auto-input").keyup(function () {
    var source = jQuery(this);
    if (source.val().length > 1) {
    autocompleteArtists(source);
    }
    });
    
    jQuery(".artist-auto .auto-input").blur(function () {
    //alert();
    var autocomplete = jQuery(this).parent(".artist-auto");
    initAutocompleteDisplay(autocomplete);
    autocomplete.find(".auto-input").val("");
    //alert();
    });
    
    // init reset button
    jQuery(".auto-reset").click(function () {
    var source = jQuery(this);
    resetAutocomplete(source);
    });
    // init display;
    jQuery(".artist-auto").each(function () {
    initAutocompleteDisplay(jQuery(this));
    });
    }

    function initAutocompleteDisplay(autocomplete) {
    var id = autocomplete.find(".auto-id").val();
    var name = autocomplete.find(".auto-name").val();
    var set = id.length > 0;
        
    autocomplete.find(".auto-link").text(name).attr("href", "/Artist/Edit/" + id).toggle(set);
    autocomplete.find(".auto-input").toggle(!set);
    autocomplete.find(".auto-reset").toggle(set);
    autocomplete.find(".auto-list").hide();
    }

    function autocompleteArtists(textbox) {
    jQuery.post("/Artist/SearchByName/", { "text": textbox.val() },
    function (listItems) {
    var search = textbox.val();
    var html = "";
    for (var i = 0; i < listItems.length; i++) {
    html += _getHtmlForAutocompleteItem(listItems[i], i, search);
    }
    textbox.parent().find(".auto-list").html(html).show();

    jQuery(".auto-item").mousedown(function () {
    var source = jQuery(this);
    var autocompleteBlock = source.parent().parent();
    var id = source.attr("value");

    autocompleteBlock.find(".auto-id").val(id);
    autocompleteBlock.find(".auto-name").val(source.attr("text"));

    //alert();
    initAutocompleteDisplay(autocompleteBlock);
    });
    });
    }
    function _getHtmlForAutocompleteItem(dataItem, index, search) {
    //alert(dataItem.Name);
    }

    
    function resetAutocomplete(source) {
    //var source = jQuery(this);
    var autocompleteBlock = source.parent();
    autocompleteBlock.find(".auto-id").val("");
    autocompleteBlock.find(".auto-name").val("");
    autocompleteBlock.find(".auto-input").val("").show().focus(); //attr("disabled", "disabled");
    autocompleteBlock.find(".auto-list").hide();
    autocompleteBlock.find(".auto-link").hide();
    autocompleteBlock.find(".auto-reset").hide();
    }
    */


