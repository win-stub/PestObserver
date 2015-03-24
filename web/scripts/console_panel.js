

/***************************************************************************************************************************/
/* vespa_panel */

function console_panel(console_panel) {

    console_panel.dialog({
        modal: true,
        width: 600,
        height:400,
        buttons: {
            Ok: function () {
                $(this).dialog("close");
            }
        },
        autoOpen: false,
        title: "Console Vespa Mining"
    });
    console_panel.content = console_panel.find("#console_content");
    /* On error *********************************************************/
    console_panel.add_error = function (error_title, error_content, error_stack_trace) {
        var date_string = new Date(Date.now()).toLocaleString()
        var div_error = jQuery("<div class='console_error'></div>")
                            .append("<div class='error_title'>"
                                + "<span class='date'>" + date_string + "</span>"
                                + "<span class='title'>" + error_title + "</span>"
                                + "</div>")
                        .prependTo(console_panel.content);
        if (error_content != null)
            div_error.append("<div class='error_message'>" + error_content + "</div>")
        if (error_stack_trace != null)
            div_error.append("<div class='error_stack_trace'>" + error_stack_trace + "</div>")

        console_panel.dialog("open");
    }
    

    ///* Console *********************************************************/
    //panel.console_panel = panel.find("#vespa_console_panel").dialog({
    //    modal: true,
    //    buttons: {
    //        Ok: function () {
    //            $(this).dialog("close");
    //        }
    //    },
    //    autoOpen: false,
    //    title: "Console Vespa Mining"
    //});

    return console_panel;
}


