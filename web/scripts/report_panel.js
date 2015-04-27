
var report_test_url = "reports\\BSV_GC_n_08_du_22_octobre_2013.pdf";
var report_dir = "files/";
var report_extension = ".pdf";

/***************************************************************************************************************************/
/* report_panel */

function report_panel(panel, report_panel){//, on_search_report) {

    report_panel.report_list = report_panel.find("#report_list");
    report_panel.report_list_count = report_panel.find("#report_list_count");
    report_panel.report_total_count = report_panel.find("#report_total_count");

    report_panel.report_filter = report_panel.find("#report_filter");
    report_panel.report_filter_years = report_panel.find("#report_filter_years");
    report_panel.report_filter_areas = report_panel.find("#report_filter_areas");
    report_panel.report_filter_reset = report_panel.find(".filter_reset");

    report_panel.report_sorter_panel = report_panel.find("#report_sorter").hide();
    report_panel.report_sorters = report_panel.report_sorter_panel.find(".report_sorter_item");
    report_panel.current_sort = "date";

    report_panel.report_text_filter = report_panel.find("#report_text_filter").hide();
    report_panel.btn_filter_text = report_panel.find("#btn_filter_text");
    report_panel.opened_report_ids = new Array();

    // Init filter reset
    report_panel.report_filter.hide();
    report_panel.find("#filter_reset_years").click(function () {
        jQuery("#report_filter_years div").removeClass("selected");
        report_panel.selected_year = null;
        report_panel.filter_on_change();
    });
    report_panel.find("#filter_reset_areas").click(function () {
        jQuery("#report_filter_areas div").removeClass("selected");
        report_panel.selected_area = null;
        report_panel.filter_on_change();
    });
    // Sorters
    report_panel.report_sorters.click(function () {
        report_panel.sort_changed(jQuery(this));
    });

    report_panel.cover_up = panel.get_waiting_cover_up(report_panel, 100);

    /* List management *********************************************************/
    // Search succeeded
    report_panel.search_succeeded = function (response) {
        console.time("[Report list] Create DOM on new search");
        report_panel.opened_report_ids = new Array();
        report_panel.selected_year = null;
        report_panel.selected_area = null;
        report_panel.report_sorter_panel.show();
        report_panel.report_text_filter.show();
        report_panel.clear_list();
        report_panel.reports = response.Reports;
        if (report_panel.current_sort != "date")
            report_panel.sort_reports_array(report_panel.current_sort);

        report_panel.set_counts();
        report_panel.create_list(response);
        report_panel.create_filters(response);
        console.timeEnd("[Report list] Create DOM on new search");
        report_panel.cover_up.doFadeOut();
    }
    /* Report list DOM creation *********************************************************/
    // Show report list
    report_panel.set_counts = function () {
        report_panel.report_list_count.text(report_panel.reports.length);
        report_panel.report_total_count.text(report_panel.reports.length);
    }
    // Show report list
    report_panel.create_list = function () {
        var html = "";
        for (i = 0; i < report_panel.reports.length; i++) {
            html += report_panel.create_report_item(report_panel.reports[i],i);
        }
        
        report_panel.report_list.html(html);

        jQuery("#report_list a").click(function () {
            var report_item = jQuery(this).parent().parent();
            report_panel.opened_report_ids.push(report_item.attr("id_report"));
            report_item.addClass("opened");
        });
    }
    // Create report item list
    report_panel.create_report_item = function (data, index) {
        var opened = jQuery.inArray("" + data.Id, report_panel.opened_report_ids) != -1;
        var report_item = "<div class='report_item" + ( (index % 2 == 1) ? " alt" : "")
                                + ((opened) ? " opened" : "")
                                + "' year='" + data.Year
                                + "' id_area='" + data.Id_Area
                                + "' id_report='" + data.Id
                                + "' >"
                                + "<div class='report_area'>"
                                    + "<div class='cube'></div>"
                                    + "<div class='report_area_name'>" + data.AreaName + "</div>"
                                    + "<div class='report_date'>" + data.DateString + "</div>"
                                + "</div>"
                                + "<div class='report_name'>"
                                    + "<a href='" + report_dir + data.Name + report_extension + "' target='_blank' title='" + data.Name + "'>" + data.Name + "</a>"
                                    + "<div class='report_pdf'></div>"
                                + "</div>"
                            + "</div>"
        return report_item;
    }
    // Clear list
    report_panel.clear_list = function () {
        report_panel.report_list.empty();
        report_panel.report_filter_areas.empty();
        report_panel.report_filter_years.empty();
        report_panel.report_list_count.text("0");
        report_panel.report_total_count.text("0");
    }
    /* Filter Methods *********************************************************/
    // Filters creation
    report_panel.create_filters = function (response) {

        var reports_by_year = d3.nest()
                                .key(function (d) { return d.Year; })
                                .rollup(function (g) { return g.length; })
                                .entries(response.Reports);

        for (i = 0; i < response.Years.length; i++) {
            var year_item = jQuery("<div year='" + reports_by_year[i].key + "'></div>")
                            .append("<span class='filter_year_item_text'>" + reports_by_year[i].key + "</span>")
                            .append("<span class='filter_year_item_count'>(" + reports_by_year[i].values + ")</span>")
                            .click(function () {
                                jQuery("#report_filter_years div").removeClass("selected");
                                jQuery(this).addClass("selected");
                                report_panel.selected_year = jQuery(this).attr("year");
                                report_panel.filter_on_change();
                            })
                            .appendTo(report_panel.report_filter_years);
        }

        report_panel.report_filter.show();
    }
    report_panel.filter_area = function (id_area) {
        report_panel.selected_area = id_area;
        report_panel.filter_on_change();
    }
    // On filter selection
    report_panel.filter_on_change = function () {
        report_panel.report_list.find(".report_item").hide();
        var class_to_show = ".report_item";
        if (report_panel.selected_area != null)
            class_to_show += "[id_area='" + report_panel.selected_area + "']";
        if (report_panel.selected_year != null)
            class_to_show += "[year='" + report_panel.selected_year + "']";

        var to_show = report_panel.report_list.find(class_to_show);
        to_show.show();

        report_panel.report_list_count.text(to_show.length);
    }
    /* Sort Methods *********************************************************/
    // on Sort
    report_panel.sort_changed = function (sorter) {
        report_panel.report_sorters.removeClass("selected");
        sorter.addClass("selected")

        var previous_sort = report_panel.current_sort;
        report_panel.current_sort = sorter.attr("sort");
        if (previous_sort == report_panel.current_sort) {
            if (report_panel.current_sort.indexOf("_desc") != -1) {
                report_panel.current_sort = report_panel.current_sort.replace("_desc", "");
            }
            else {
                report_panel.current_sort = report_panel.current_sort + "_desc";
            }
        }

        report_panel.cover_up.fadeIn(duration_fade_short, function () {
            report_panel.sort_list(report_panel.current_sort);
            report_panel.cover_up.fadeOut(duration_fade_short);
        });
    }
    // Sort list
    report_panel.sort_list = function (sort_type) {
        report_panel.report_list.empty();
        report_panel.sort_reports_array(report_panel.current_sort);

        report_panel.create_list();
        report_panel.filter_on_change();
    }
    // Data sorting function
    report_panel.sort_reports_array = function (sort_type) {
        var sort_func = null;
        if (sort_type == "name") {
            sort_func = report_panel.sort_name;
        }
        else if (sort_type == "name_desc") {
            sort_func = report_panel.sort_name_desc;
        }
        else if (sort_type == "area_name") {
            sort_func = report_panel.sort_area_name;
        }
        else if (sort_type == "area_name_desc") {
            sort_func = report_panel.sort_area_name_desc;
        }
        else if (sort_type == "date") {
            sort_func = report_panel.sort_date;
        }
        else if (sort_type == "date_desc") {
            sort_func = report_panel.sort_date_desc;
        }

        report_panel.reports.sort(sort_func);
    }
    // Date sort delegate
    report_panel.sort_date = function (e_1, e_2) {
        var a1 = parseInt(e_1.Date.substr(6)), b1 = parseInt(e_2.Date.substr(6));
        if (a1 == b1) return 0;
        return a1 > b1 ? 1 : -1;
    }
    // Arean name sort delegate
    report_panel.sort_area_name = function (e_1, e_2) {
        var a1 = e_1.AreaName, b1 = e_2.AreaName;
        if (a1 == b1) return 0;
        return a1 > b1 ? 1 : -1;
    }
    // file name sort delegate
    report_panel.sort_name = function (e_1, e_2) {
        var a1 = e_1.Name.toLowerCase(), b1 = e_2.Name.toLowerCase();
        if (a1 == b1) return 0;
        return a1 > b1 ? 1 : -1;
    }
    // Date sort delegate
    report_panel.sort_date_desc = function (e_1, e_2) {
        var a1 = parseInt(e_1.Date.substr(6)), b1 = parseInt(e_2.Date.substr(6));
        if (a1 == b1) return 0;
        return a1 < b1 ? 1 : -1;
    }
    // Arean name sort delegate
    report_panel.sort_area_name_desc = function (e_1, e_2) {
        var a1 = e_1.AreaName, b1 = e_2.AreaName;
        if (a1 == b1) return 0;
        return a1 < b1 ? 1 : -1;
    }
    // file name sort delegate
    report_panel.sort_name_desc = function (e_1, e_2) {
        var a1 = e_1.Name.toLowerCase(), b1 = e_2.Name.toLowerCase();
        if (a1 == b1) return 0;
        return a1 < b1 ? 1 : -1;
    }

    report_panel.open_report = function (id_report) {
        var report_item_anchor = report_panel.find("#report_list .report_item[id_report='" + id_report + "'] a");
        report_item_anchor.click();
        window.open(report_item_anchor.attr("href"), "_blank");
    }

    return report_panel;
}
