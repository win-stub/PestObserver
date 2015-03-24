
/***************************************************************************************************************************/
/* Global contants */

/***************************************************************************************************************************/
/* vespa_panel */

function search_panel(search_panel, post_function, search_function) {

    search_panel.post_delegate = post_function;
    search_panel.on_search_delegate = search_function;
    /* ui elements *********************************************************/
    // Plants
    search_panel.div_plant = jQuery("#div_plant");
    search_panel.txt_plant = jQuery("#txt_plant");
    search_panel.hid_plant = jQuery("#hid_plant");
    // Bugs
    search_panel.div_bug = jQuery("#div_bug");
    search_panel.txt_bug = jQuery("#txt_bug");
    search_panel.hid_bug = jQuery("#hid_bug");
    // Diseases
    search_panel.div_disease = jQuery("#div_disease");
    search_panel.txt_disease = jQuery("#txt_disease");
    search_panel.hid_disease = jQuery("#hid_disease");
    // Dates
    search_panel.txt_date_start = jQuery("#txt_date_start");
    search_panel.txt_date_end = jQuery("#txt_date_end");
    search_panel.txt_date_start.datepicker();
    search_panel.txt_date_end.datepicker();

    // Text search
    search_panel.txt_filter_text = jQuery("#txt_filter_text");
    // Search
    search_panel.btn_search = jQuery("#btn_search");

    /* ui handlers *********************************************************/

    // Search
    search_panel.btn_search.click(function () {
        search_panel.on_search_delegate();
    });
    // Get search parameters
    search_panel.get_search_params = function () {
        var params = {
            Id_Plant: search_panel.hid_plant.val()
            , Id_Bioagressor: search_panel.hid_bug.val()
            , Id_Disease: search_panel.hid_disease.val()
            , DateStart: search_panel.txt_date_start.val()
            , DateEnd: search_panel.txt_date_end.val()
            , SearchText: search_panel.txt_filter_text.val()
        };
        return params;
    }

    /* Other Methods *********************************************************/
    // On autcomplete selection
    search_panel.autocomplete_selected = function (autocomplete) {
        if (autocomplete == search_panel.autocomplete_bioagressors)
            search_panel.autocomplete_diseases.force_reset();
        else if (autocomplete == search_panel.autocomplete_diseases)
            search_panel.autocomplete_bioagressors.force_reset();
    }
    // Init dates
    search_panel.init_dates = function (response) {
        search_panel.txt_date_start.val(response.MinDate);
        search_panel.txt_date_end.val(response.MaxDate);
    }

    /* Initialisation *********************************************************/
    // Autocompletes
    search_panel.autocomplete_plants = new Autocomplete(search_panel.div_plant
                                                , search_panel.txt_plant
                                                , search_panel.hid_plant
                                                , search_panel.autocomplete_selected
                                                , search_panel.post_delegate);
    search_panel.autocomplete_bioagressors = new Autocomplete(search_panel.div_bug
                                                , search_panel.txt_bug
                                                , search_panel.hid_bug
                                                , search_panel.autocomplete_selected
                                                , search_panel.post_delegate);
    search_panel.autocomplete_diseases = new Autocomplete(search_panel.div_disease
                                                , search_panel.txt_disease
                                                , search_panel.hid_disease
                                                , search_panel.autocomplete_selected
                                                , search_panel.post_delegate);
    // dates
    search_panel.post_delegate("GetInitializationInfos", null, search_panel.init_dates);

    return search_panel;
}

