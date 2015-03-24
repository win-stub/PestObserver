

/***************************************************************************************************************************/
/* area_detail */

function area_detail_panel2_old(panel, dialog_panel) {
    
    //var detail_panel = jQuery("<div></div>");
    dialog_panel.panel = panel;
    dialog_panel.dialog({
        modal: false,
        //buttons: {
        //    Ok: function () {
        //        $(this).dialog("close");
        //    }
        //},
        autoOpen: false,
        width: 202,
        minHeight: 296
    });
    dialog_panel.detail_panel = dialog_panel.find("#detail_panel");
    dialog_panel.show_plants;
    dialog_panel.show_bioagressors;
    dialog_panel.show_diseases;
    
    dialog_panel.open = function (area_id, area) {
        dialog_panel.current_area = area;
        dialog_panel.detail_panel.empty();
        dialog_panel.load_details(area_id);
    }

    // AJAX detail data loading
    dialog_panel.load_details = function (area_id) {
        var detail_params = dialog_panel.panel.current_search;
        detail_params["Id_Area"] = area_id;
        detail_params = JSON.stringify(detail_params);

        console.time("[Area details] Get data");
        dialog_panel.panel.post_ajax("GetAreaDetails", detail_params, dialog_panel.on_data_received);
    }
    // On data reception
    dialog_panel.on_data_received = function (response) {
        console.timeEnd("[Area details] Get data");

        dialog_panel.show_plants = (response.Plants != null && response.Plants.length > 0);
        dialog_panel.show_bioagressors = (response.Bioagressors != null && response.Bioagressors.length > 0);
        dialog_panel.show_diseases = (response.Diseases != null && response.Diseases.length > 0);

        if (dialog_panel.show_plants || dialog_panel.show_bioagressors || dialog_panel.show_diseases) {
            dialog_panel.create_details_panel(response);
        }
    }
    /* Details panel creation *********************************************************/
    // Create DOM panel
    dialog_panel.create_details_panel = function (response) {
        console.time("[Area details] Create DOM");

        // Position
        var selected_area = dialog_panel.panel.map.area(response.Id_Area);
        //var style = dialog_panel.panel.map.get_style_for_path_center(selected_area, 0, 0);
        // Main panel
        dialog_panel.detail_panel.addClass("map_detail_panel")
                    .addClass(dialog_panel.show_plants ? "plant_panel" : "disease_bioagressor_panel")
                    //.attr("style", style)
                    //.hide()
                    .click(function () {
                        dialog_panel.panel.map.clear_selection();
                    });

        //.appendTo(dialog_panel.panel.map.container)
        dialog_panel.dialog({title: response.AreaName})//.append("<div class='area_name'>" + response.AreaName + "</div>");
        dialog_panel.create_details_content(response);
        console.timeEnd("[Area details] Create DOM");
        //detail_panel.fadeIn(duration_fade_long);
        //dialog_panel.dialog("open").position("center", "center", area);
        
        //var area = dialog_panel.panel.map.area(response.Id_Area);
        dialog_panel.dialog("open");
    }
    // Create DOM content
    dialog_panel.create_details_content = function (response) {
        // Panel content
        var detail_panel_content = jQuery("<div></div>")
                                    .addClass("map_detail_panel_content")
                                    .appendTo(dialog_panel.detail_panel)

        // Panel lists contents
        if (dialog_panel.show_plants) {
            var plant_list = dialog_panel.create_list_content(response.Plants
                                                                , "plant_list"
                                                                , response.Plants.length + " Plantes"
                                                                , panel.search_panel.autocomplete_plants
                                                                , "id_plant");
            detail_panel_content.append(plant_list);
        }
        if (dialog_panel.show_bioagressors) {
            var bioagressor_list = dialog_panel.create_list_content(response.Bioagressors
                                                                , "bioagressor_list"
                                                                , response.Bioagressors.length + " Ravageurs"
                                                                , panel.search_panel.autocomplete_bioagressors
                                                                , "id_bioagressor");
            detail_panel_content.append(bioagressor_list);
        }
        if (dialog_panel.show_diseases) {
            var disease_list = dialog_panel.create_list_content(response.Diseases
                                                                , "disease_list"
                                                                , response.Diseases.length + " Maladies"
                                                                , panel.search_panel.autocomplete_diseases
                                                                , "id_disease");
            detail_panel_content.append(disease_list);
        }
    }
    // Create DOM list
    dialog_panel.create_list_content = function (data, css, title, autocomplete, id_attribute) {
        var list_content = null;
        if ((data != null) && (data.length > 0)) {
            list_content = jQuery("<div></div>")
                            .addClass(css)
                            .append("<div class='list_title'>"
                                        + title
                                    + "</div>")
                        
            var list_panel = jQuery("<ul></ul>").appendTo(list_content);
                //list_content.append(list_panel);
                for (i = 0; i < data.length; i++) {
                    list_panel.append("<li class='" + ((i % 2 == 1) ? "alt" : "") + "'>"
                                            + "<span " + id_attribute + "='" + data[i].Id + "'>"
                                                + data[i].Text
                                            + "</span>"
                                        + "</li>");
                }
                list_panel.find("li span").click(function () {
                    var id = jQuery(this).attr(id_attribute);
                    var text = jQuery(this).text();
                    autocomplete.select(id, text);
                });
        }
        return list_content;
    }

    //detail_panel.load_details(area_id);

    
    return dialog_panel;
}






