
/***************************************************************************************************************************/
/* Global contants */

/* Main parameters *********************************************************/
var url_webservices = "/Services/Vespa.svc/"; // url prod
var duration_fade_short = 100;
var duration_fade_long = 400;
//var url_webservices = "http://212.198.172.12:8080/Vespa/Services/Vespa.svc/"; 

/* Autocomplete parameters *********************************************************/
var autocomplete_min_length = 2;

/***************************************************************************************************************************/
/* Init */

jQuery(function () {
    new vespa_panel(jQuery("#vespa_panel"));
});

/***************************************************************************************************************************/
/* vespa_panel */

function vespa_panel(panel) {

    /* Search function  *********************************************************/
    panel.search = function () {
        panel.current_search = panel.search_panel.get_search_params();

        panel.report_panel.cover_up.doFadeIn();
        panel.map.cover_up.doFadeIn();

        console.time("[Report list] Get data");
        var search_params = panel.current_search;
        var string_params = JSON.stringify(panel.current_search);

        panel.post_ajax("GetSearchReportList", string_params, panel.search_succeeded, panel.search_error);
    }
    panel.search_succeeded = function (response) {
        console.timeEnd("[Report list] Get data");
        panel.report_panel.search_succeeded(response);
        panel.map.search_succeeded(response);
    }
    panel.search_error = function (response) {
        panel.on_error("Erreur lors de la recherche", response.ErrorMessage, response.ErrorStackTrace);
        panel.report_panel.cover_up.doFadeOut();
        panel.map.cover_up.doFadeOut();
    }

    /* Ajax *********************************************************/
    panel.post_ajax = function (function_name, data_request, on_success, on_error) {
        jQuery.ajax({
            type: "POST",
            url: url_webservices + function_name,
            data: data_request,
            contentType: "application/json",
            dataType: "json",
            processdata: true,
            success: function (response) {
                if (response.ErrorMessage != null) {
                    if (on_error != null)
                        on_error(response);
                    else
                        panel.on_error(response.ErrorMessage, response.ErrorStackTrace);
                }
                else {
                    on_success(response);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                var error_title = "Erreur " + XMLHttpRequest.status + " : " + XMLHttpRequest.statusText
                
                var error_message = "Lors du Post AJAX vers la méthode " + url_webservices + function_name;
                var error_content = (XMLHttpRequest.responseText == null ? "" : "\n" + XMLHttpRequest.responseText);
                panel.on_error(error_title, error_message,error_content);
            }
        });
    }
    /* On error *********************************************************/
    panel.on_error = function (error_title, error_content, error_stack_trace) {
        panel.console_panel.add_error(error_title, error_content, error_stack_trace);
    }
    /* DOM Methods *********************************************************/
    // Returns "waiting..." panels to cover UI
    panel.get_waiting_cover_up = function (jquery_element_to_cover, duration) {
        var cover_up = new cover_panel(jquery_element_to_cover, duration);
        return cover_up;
    }

    /* Init *********************************************************/

    // Page reload
    panel.find("#bt_home").click(function () {
        window.location.reload(true);
    });

    // Init Console 
    panel.console_panel = new console_panel(panel.find("#vespa_console_panel"));
    // Init search panel
    panel.search_panel = new search_panel(panel.find("#vespa_search_panel"), panel.post_ajax, panel.search);
    // Init map panel
    jQuery("#map_container").load('france_map.svg',
                                    null,
                                    function () {
                                        panel.map = new vespa_map(panel, jQuery("#map"));
                                    }
                                )
    // Init report_panel
    panel.report_panel = new report_panel(panel, jQuery("#report_panel"));
}
