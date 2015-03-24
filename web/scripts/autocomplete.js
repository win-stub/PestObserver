/***************************************************************************************************************************/
/* Autocomplete */

function Autocomplete(autocomplete, txt_autocomplete, hid_autocomplete, on_select, on_show_list) {

    /* ui elements *********************************************************/
    //autocomplete.panel = panel;

    autocomplete.txt = txt_autocomplete;
    autocomplete.hid = hid_autocomplete;

    autocomplete.list = autocomplete.find(".autocomplete_list");
    autocomplete.list.hide();
    autocomplete.span = autocomplete.find(".autocomplete_span");
    autocomplete.btn = autocomplete.find(".autocomplete_btn");

    autocomplete.func = autocomplete.attr("func");

    autocomplete.span.hide();

    autocomplete.on_select_delegate = on_select;
    autocomplete.on_show_list_delegate = on_show_list;

    /* ui handlers *********************************************************/
    autocomplete.txt.keyup(function () {
        autocomplete.list_load();
    });
    autocomplete.txt.focus(function () {
        autocomplete.list_load();
    });
    autocomplete.span.click(function () {
        autocomplete.hid.val("");
        autocomplete.span.text("");
        autocomplete.list_reset();
        autocomplete.txt.focus();
    });
    autocomplete.txt.blur(function () {
        autocomplete.list_reset();
    });

    /* List management *********************************************************/
    autocomplete.list_load = function () {
        var text = autocomplete.txt.val();
        if (text.length >= autocomplete_min_length) {
            autocomplete.on_show_list_delegate(autocomplete.func, '{"text":"' + text + '"}', autocomplete.list_show);
        }
        else {
            autocomplete.list_hide();
        }
    }
    autocomplete.select = function (id, text) {
        autocomplete.hid.val(id);
        autocomplete.span.text(text);
        autocomplete.span.show();
        autocomplete.txt.hide();
        autocomplete.list_hide();

        autocomplete.on_select_delegate(autocomplete);
        //autocomplete.panel.autocomplete_selected(autocomplete);
    }

    autocomplete.list_show = function (response) {
        autocomplete.list.empty();
        for (var i = 0; i < response.Items.length; i++) {
            var id = response.Items[i].Id;
            var text = response.Items[i].Text;
            var search = autocomplete.txt.val();
            var highlighted_text;
            // text highlight
            var index = remove_accents(text.toLowerCase()).indexOf(remove_accents(search.toLowerCase()));
            if (index != -1) {
                text = text.substr(0, index) + "<b>" + text.substr(index, search.length) + "</b>" + text.substr(index + search.length);
            }
            var item = jQuery("<div></div>")
                                .addClass("list_item")
                                .attr("value", id)
                                .append("<span>" + text + "</span>");
            if (i % 2 == 1)
                item.addClass("alt");
            autocomplete.list.append(item);
            //autocomplete.list.append("<div class='list_item' value='" + id + "'><span>" + text + "</span></div>");
        }
        autocomplete.list.show();
        autocomplete.find(".list_item").mousedown(function () {
            var id = jQuery(this).attr("value");
            var text = jQuery(this).text();
            autocomplete.select(id, text);
        });
    };
    autocomplete.list_hide = function () {
        autocomplete.list.hide();
    };
    autocomplete.list_reset = function () {
        autocomplete.span.hide();
        autocomplete.list_hide();
        autocomplete.txt.val("");
        if (autocomplete.span.text().length > 0)
            autocomplete.span.show();
        else
            autocomplete.txt.show();
    };
    autocomplete.force_reset = function () {
        autocomplete.span.text("");
        autocomplete.hid.val("");
        autocomplete.list_reset();
    };

    return autocomplete;
}