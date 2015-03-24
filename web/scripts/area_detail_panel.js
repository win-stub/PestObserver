
var panel_tabs_height = 50;

/***************************************************************************************************************************/
/* area_detail */

function area_detail_panel(panel, dialog_panel) {
    
    dialog_panel.panel = panel;
    //dialog_panel.detail_panel = dialog_panel.find("#detail_panel");

    dialog_panel.dialog({
        modal: false,
        autoOpen: false,
        width: 300,
        height: 360,
        resize: function (event, ui) {
            dialog_panel.on_resize(event, ui);
        },
        //close: function (event, ui) {
        //    dialog_panel.on_close(event, ui);
        //}
    });
    
    dialog_panel.open = function (area) {
        dialog_panel.current_area = area;
        dialog_panel.load_details(area);
    }

    dialog_panel.close = function () {
        dialog_panel.dialog("close");
        dialog_panel.current_area = null;
    }

    dialog_panel.on_resize = function () {
        dialog_panel.find(".list_content").height(dialog_panel.height() - panel_tabs_height);
    }
   
    // AJAX detail data loading
    dialog_panel.load_details = function (area) {
        var detail_params = dialog_panel.panel.current_search;
        detail_params["Id_Area"] = area.attr("id_area");
        detail_params = JSON.stringify(detail_params);

        console.time("[Area details] Get data");
        dialog_panel.panel.post_ajax("GetAreaDetails", detail_params, dialog_panel.on_data_received);
    }
    // On data reception
    dialog_panel.on_data_received = function (response) {
        dialog_panel.init();
        console.timeEnd("[Area details] Get data");

        dialog_panel.show_plants = (response.Plants != null && response.Plants.length > 0);
        dialog_panel.show_bioagressors = (response.Bioagressors != null && response.Bioagressors.length > 0);
        dialog_panel.show_diseases = (response.Diseases != null && response.Diseases.length > 0);
        dialog_panel.show_occurences = (response.Occurences != null && response.Occurences.length > 0);

        if (dialog_panel.show_plants || dialog_panel.show_bioagressors || dialog_panel.show_diseases || dialog_panel.show_occurences) {
            dialog_panel.create_details_panel(response);
        }
    }
    dialog_panel.init = function () {
        dialog_panel.empty();
        dialog_panel.detail_panel = jQuery("<div></div>")
                                        .appendTo(dialog_panel)
                                        .attr("id", "detail_panel")
                                        .addClass("");
        dialog_panel.detail_panel.tabs = jQuery("<ul></ul>").appendTo(dialog_panel.detail_panel);
    }
    /* Details panel creation *********************************************************/
    // Create DOM panel
    dialog_panel.create_details_panel = function (response) {
        console.time("[Area details] Create DOM");

        // Position
        if (dialog_panel.dialog("isOpen") == false){
            dialog_panel.dialog({
                position: {
                    my: "right top"
                            , at: "center center"
                            , of: dialog_panel.current_area
                }
            });
        }
        // Title
        dialog_panel.dialog({
            title: dialog_panel.get_title(response)
        });
        //dialog_panel.dialog({ title: response.AreaName });
        dialog_panel.create_details_content(response);
        console.timeEnd("[Area details] Create DOM");

        jQuery(dialog_panel.detail_panel).tabs();

        dialog_panel.dialog("open");
        dialog_panel.on_resize();
    }
    dialog_panel.get_title = function (response) {
        var title = response.AreaName + " : ";
        if (dialog_panel.show_plants) {
            title += "Plantes en relation";
        }
        if (dialog_panel.show_bioagressors) {
            title += "Maladies et ravageurs en relation";
        }
        if (dialog_panel.show_occurences) {
            title += "Citations de la relation";
        }
        return title;
    }
    // Create DOM content
    dialog_panel.create_details_content = function (response) {
        // Panel content

        // Panel lists contents
        if (dialog_panel.show_plants) {
            dialog_panel.detail_panel.tabs.append("<li id='plant_tab' class='detail_list_tab'>"
                                                      + "<a href='#plant_list'>" + response.Plants.length + "</a>"
                                                    +"</li>");
            var plant_list = dialog_panel.create_list_content(response.Plants
                                                                , "plant_list"
                                                                , response.Plants.length + " Plantes"
                                                                , panel.search_panel.autocomplete_plants
                                                                , "id_plant");
            dialog_panel.detail_panel.append(plant_list);
        }
        if (dialog_panel.show_bioagressors) {
            dialog_panel.detail_panel.tabs.append("<li id='bioagressor_tab' class='detail_list_tab'>"
                                                        + "<a href='#bioagressor_list'>"
                                                            + response.Bioagressors.length
                                                        + "</a>"
                                                +" </li>")
            var bioagressor_list = dialog_panel.create_list_content(response.Bioagressors
                                                                , "bioagressor_list"
                                                                , response.Bioagressors.length + " Ravageurs"
                                                                , panel.search_panel.autocomplete_bioagressors
                                                                , "id_bioagressor");
            dialog_panel.detail_panel.append(bioagressor_list);
        }
        if (dialog_panel.show_diseases) {
            dialog_panel.detail_panel.tabs.append("<li id='disease_tab' class='detail_list_tab'><a  href='#disease_list'>" + response.Diseases.length + "</a></li>")
            var disease_list = dialog_panel.create_list_content(response.Diseases
                                                                , "disease_list"
                                                                , response.Diseases.length + " Maladies"
                                                                , panel.search_panel.autocomplete_diseases
                                                                , "id_disease");
            dialog_panel.detail_panel.append(disease_list);
        }
        if (dialog_panel.show_occurences) {
            dialog_panel.detail_panel.tabs.append("<li id='occurence_tab' class='detail_list_tab'><a  href='#occurence_list'>" + response.Occurences.length + "</a></li>")
            var occurence_list = dialog_panel.create_list_content(response.Occurences
                                                                , "occurence_list"
                                                                , response.Diseases.length + " Occurences"
                                                                , null
                                                                , "id_report");
            dialog_panel.detail_panel.append(occurence_list);
            occurence_list.find("li").click(function () {
                //alert(jQuery(this).attr("id_report"));
                var report_id = jQuery(this).attr("id_report");
                panel.report_panel.open_report(report_id);
            })
        }
        
    }
    // Create DOM list
    dialog_panel.create_list_content = function (data, css, title, autocomplete, id_attribute) {
        var list_content = null;
        if ((data != null) && (data.length > 0)) {
            list_content = jQuery("<div></div>")
                            .attr("id",css)
                            .addClass(css)
                            .addClass("list_content");
                        
            var list_panel = jQuery("<ul></ul>").appendTo(list_content);
            for (i = 0; i < data.length; i++) {
                list_panel.append("<li class='" + ((i % 2 == 1) ? "alt" : "") + "' " + id_attribute + "='" + data[i].Id + "'>"
                                        + "<span>"
                                            + ((data[i].Date != null)
                                                ? "<span class='span_date'>" + data[i].Date + "</span>"
                                                : "")
                                            + ((data[i].Text != null)
                                                ? data[i].Text
                                                : "")
                                        + "</span>"
                                    + "</li>");
            }
            if(autocomplete != null){
                list_panel.find("li").click(function () {
                    var id = jQuery(this).attr(id_attribute);
                    var text = jQuery(this).text();
                    autocomplete.select(id, text);
                    panel.search();
                });
            }
        }
        return list_content;
    }
   
    return dialog_panel;
}






