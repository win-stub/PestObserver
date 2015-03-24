

/***************************************************************************************************************************/
/* area_detail */

function area_detail_panel(panel, area_id) {
    
    var detail_panel = jQuery("<div></div>");
    detail_panel.panel = panel;

    var show_plants;
    var show_bioagressors;
    var show_diseases;

    // AJAX detail data loading
    detail_panel.load_details = function (area_id) {
        var detail_params = detail_panel.panel.current_search;
        detail_params["Id_Area"] = area_id;
        detail_params = JSON.stringify(detail_params);

        console.time("[Area details] Get data");
        detail_panel.panel.post_ajax("GetAreaDetails", detail_params, detail_panel.on_data_received);
    }
    // On data reception
    detail_panel.on_data_received = function (response) {
        console.timeEnd("[Area details] Get data");

        show_plants = (response.Plants != null && response.Plants.length > 0);
        show_bioagressors = (response.Bioagressors != null && response.Bioagressors.length > 0);
        show_diseases = (response.Diseases != null && response.Diseases.length > 0);

        if (show_plants || show_bioagressors || show_diseases) {
            detail_panel.create_details_panel(response);
        }
    }
    /* Details panel creation *********************************************************/
    // Create DOM panel
    detail_panel.create_details_panel = function (response) {
        console.time("[Area details] Create DOM");

        // Position
        var selected_area = detail_panel.panel.map.area(response.Id_Area);
        var style = detail_panel.panel.map.get_style_for_path_center(selected_area, 0, 0);
        // Main panel
        detail_panel.addClass("map_detail_panel")
                    .addClass(show_plants ? "plant_panel" : "disease_bioagressor_panel")
                    .attr("style", style)
                    .hide()
                    .click(function () {
                        detail_panel.panel.map.clear_selection();
                    })
                    .appendTo(detail_panel.panel.map.container)
                    .append("<div class='area_name'>" + response.AreaName + "</div>");
        detail_panel.create_details_content(response);
        console.timeEnd("[Area details] Create DOM");
        detail_panel.fadeIn(duration_fade_long);
    }
    // Create DOM content
    detail_panel.create_details_content = function (response) {
        // Panel content
        var detail_panel_content = jQuery("<div></div>")
                                    .addClass("map_detail_panel_content")
                                    .appendTo(detail_panel)

        // Panel lists contents
        if (show_plants) {
            var plant_list = detail_panel.create_list_content(response.Plants
                                                                , "plant_list"
                                                                , response.Plants.length + " Plantes"
                                                                , panel.search_panel.autocomplete_plants
                                                                , "id_plant");
            detail_panel_content.append(plant_list);
        }
        if (show_bioagressors) {
            var bioagressor_list = detail_panel.create_list_content(response.Bioagressors
                                                                , "bioagressor_list"
                                                                , response.Bioagressors.length + " Ravageurs"
                                                                , panel.search_panel.autocomplete_bioagressors
                                                                , "id_bioagressor");
            detail_panel_content.append(bioagressor_list);
        }
        if (show_diseases) {
            var disease_list = detail_panel.create_list_content(response.Diseases
                                                                , "disease_list"
                                                                , response.Diseases.length + " Maladies"
                                                                , panel.search_panel.autocomplete_diseases
                                                                , "id_disease");
            detail_panel_content.append(disease_list);
        }
    }
    // Create DOM list
    detail_panel.create_list_content = function (data, css, title, autocomplete, id_attribute) {
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

    detail_panel.load_details(area_id);

    
    return detail_panel;
}






