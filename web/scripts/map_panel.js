

/* Map parameters *********************************************************/ 
// Map bullets position shift
var map_bullet_shifts = [{ top: 5, left: -10 }              // Champagne-ardennes
                        , { top: -10, left: 0 }             // Rhones alpes
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0}               //
                        , { top: 0, left: 0 }];            //

// Map bullets display options                     
var map_bullet_options = [{ class: "bullet_occurrence", top: 63, left: 14 }];

// Map areas fill levels 
var map_fill_levels = ["url(#grad0)", "#a6dbdb", "#82cdcd", "#50baba", "#2d9393", "#0c4847"];

/***************************************************************************************************************************/
/* vespa_map */

function vespa_map(panel, map) {
    map.container = jQuery("#map_container");
    map.areas = jQuery("path.area");
    map.area = function (id) { return map.find("path#area_" + id); }
    map.departments = map.find("#department");
    map.cover_up = panel.get_waiting_cover_up(map.parent().parent(), 100);
    map.title = map.container.parent().find("#title_map");
    map.detail_panel = new area_detail_panel(panel, jQuery("#area_details_dialog"))

    // Legend
    map.legend = jQuery("#map_gradient").hide();
    map.min_count = jQuery("#min_count");
    map.max_count = jQuery("#max_count");

    // Button clear selection
    map.btn_clear_area_filter = jQuery("<div></div>")
                                    .attr("id", "btn_clear_area_filter")
                                    .attr("type", "button")
                                    .text("Désélectionner la région")
                                    .click(function () {
                                        map.clear_filter();
                                    })
                                    .appendTo(map.container);

    /* Search Methods *********************************************************/   
    // Search data received with succes
    map.search_succeeded = function (response) {
        //console.timeEnd("[Area list] Get data");
        map.legend.show();
        map.set_title(response);
        map.refresh_areas(response);
        if (map.selected_area != null) {
            map.select_area(map.selected_area);
        }
        map.cover_up.doHide();
    }
    // Set new title from serach
    map.set_title = function (response) {
        var title = "";
        var nb_entities = 0;
        var plant_title = "";
        var disease_title = "";
        var bioagressor_title = "";

        // Entités
        if (response.PlantName != null) {
            plant_title = "<span class='plant_title'>" + response.PlantName + "</span>";
            nb_entities++;
        }
        if (response.DiseaseName != null) {
            disease_title = "<span class='disease_title'>" + response.DiseaseName + "</span>";
            nb_entities++;
        }
        if (response.BioagressorName != null) {
            bioagressor_title = "<span class='bioagressor_title'>" + response.BioagressorName + "</span>";
            nb_entities++;
        }
        
        if (nb_entities == 1) {
            title += "Bulletins citant ";
            title += plant_title + disease_title + bioagressor_title;
        }
        else {
            title += "Bulletins citant une relation entre ";
            title += plant_title;
            title += " et ";
            title += disease_title + bioagressor_title;
        }
        if (response.SearchText != "") {
            title += "<span class='search_text_title'> et contenant </span><span class='search_text_content_title'>" + response.SearchText + "</span>";
        }
            
        // Dates
        title += " <span class='date_title'>du " + response.DateStart + " au " + response.DateEnd + "</span>"

        map.title.empty().append(title);
    }

    /* Map drawing *********************************************************/
    // Return style to set top left element_corner position in the center of 'path'
    map.get_style_for_path_center = function (path, shift_left, shift_top) {

        var bounds = document.getElementById(path.attr("id")).getBoundingClientRect();
        var id = path.attr("id").replace("area_", "");

        var t = bounds.top - map.container.position().top + window.scrollY;
        var l = bounds.left - map.container.position().left;
        var w = bounds.width;
        var h = bounds.height;

        var top = (t + (h / 2) - (shift_top)) + map_bullet_shifts[id - 1].top;
        var left = (l + (w / 2) - (shift_left)) + map_bullet_shifts[id - 1].left;

        return "top:" + top + "px;left:" + left + "px";
    }
    // clear map
    map.reset_areas = function () {
        map.detail_panel.close();
        map.departments.show();
        map.clear_selection();
        var fill = map_fill_levels[0];
        map.areas.each(function () {
            jQuery(this)
                .attr("fill", fill)
                .attr("selectable", "false");
        });
        map.container.find(".map_bullet").remove();
        map.min_count.text("0");
        map.max_count.text("0");
    }
    // Refresh map
    map.refresh_areas = function (response) {
        console.time("[Area list] Create DOM");
        map.reset_areas();
        map.departments.hide();

        var reports_by_area = d3.nest()
                                .key(function (d) { return d.Id_Area; })
                                .rollup(function (g) { return g.length; })
                                .entries(response.Reports);

        var min_count = d3.min(reports_by_area, function (d) { return d.values; });
        var max_count = d3.max(reports_by_area, function (d) { return d.values; });
        map.min_count.text(min_count);
        map.max_count.text(max_count);

        var scale_area_levels = d3.scale.linear()
                                        .domain([min_count - 0.0001
                                                , max_count])
                                        .range([0, 5]);

        for (var i = 0; i < reports_by_area.length; i++) {
            var id = reports_by_area[i].key;
            var nb_reports = reports_by_area[i].values;
            var area = map.area(id);

            // skip non existing areas
            if(!area.length) continue;

            // fill with level
            var level = Math.ceil(scale_area_levels(nb_reports));
            area
                .attr("fill", map_fill_levels[level])
                .attr("selectable", "true");

            // create bullet
            map.create_bullet(area, nb_reports);
        }
        console.timeEnd("[Area list] Create DOM");
    }
    // Create bullets
    map.create_bullet = function (area, nb_reports) {

        // Retrieve bullet options and text
        var bullet_options = map_bullet_options[0];
        var bullet_text = nb_reports;
        var style = map.get_style_for_path_center(area, bullet_options.left, bullet_options.top);

        // Create and append bullet
        var bullet = jQuery("<div></div>")
                        .addClass("map_bullet " + bullet_options.class)
                        .attr("style", style)
                        .append("<span>" + bullet_text + "</span>")
                        .appendTo(map.container);

        var btn_details = jQuery("<div></div>")
                        .addClass("btn_select_details")
                        .appendTo(bullet)
                        .click(function () {
                            panel.map.click_for_details(area);
                        });

        var btn_select_area = jQuery("<div></div>")
                        .addClass("btn_select_area")
                        .appendTo(bullet)
                        .click(function () {
                            panel.map.click_for_filter(area);
                        });

        return bullet;
    }
    /* Click handlers *********************************************************/
    // Open details
    map.click_for_details = function (area) {
        map.detail_panel.open(area);
    }
    // Select for filter
    map.click_for_filter = function (area) {
        if (area.attr("area_selected") != "true") {
            if (area.attr("selectable") == "true") {
                map.select_area(area);
            }
        }
        else {
            map.clear_filter();
        }
    }
    /* Select management *********************************************************/
    // Area click handler
    // Reset selection
    map.clear_selection = function () {
        map.areas.each(function () {
            jQuery(this).attr("area_selected", "");
        });
    }
    // Area selected
    map.select_area = function (area) {
        map.clear_selection();
        map.find(".area").attr("area_selected", "false");
        area.attr("area_selected", "true");
        map.selected_area = area;
        panel.report_panel.filter_area(area.attr("id_area"));
        map.btn_clear_area_filter.show();
    }
    // Reset area filter
    map.clear_filter = function () {
        map.clear_selection();
        map.selected_area = null;
        panel.report_panel.filter_area(null);
        map.btn_clear_area_filter.hide();
    }

    map.reset_areas();
    
    return map;
}






