        <div>
            <div id="vespa_search_panel">

                <div id="search_panel">

                    <input type="hidden" id="hid_plant" />
                    <input type="hidden" id="hid_bug" />
                    <input type="hidden" id="hid_disease" />

                    <div id="div_plant" class="form_panel" func="GetPlants">
                        <div id="plante" class="title_form">Plante </div>
                        <input type="text" id="txt_plant" />
                        <div class="autocomplete_div"><span class="autocomplete_span"></span></div>
                        <div id="list_plant" class="autocomplete_list"></div>
                    </div>

                    <div id="div_disease" class="form_panel" func="GetDiseases">
                        <div id="maladie" class="title_form">Maladie</div>
                        <input type="text" id="txt_disease" />
                        <div class="autocomplete_div"><span class="autocomplete_span"></span></div>
                        <div id="list_disease" class="autocomplete_list"></div>
                    </div>

                    <div id="div_bug" class="form_panel" func="GetBugs">
                        <div id="bioagresseur" class="title_form">Ravageur</div>
                        <input type="text" id="txt_bug" />
                        <div class="autocomplete_div"><span class="autocomplete_span"></span></div>
                        <div id="list_bug" class="autocomplete_list"></div>
                    </div>

                    <div id="form_date">
                        <div class="title_date" id="debut">
                            <span>Date de début</span>
                            <input type="text" id="txt_date_start" />
                        </div>
                        <div class="title_date" id="fin">
                            <span>Date de fin</span>
                            <input type="text" id="txt_date_end" />
                        </div>
                    </div>

                </div><!--fin search panel-->

                <div id="bt_search">
                    <div id="report_text_filter">
                        <input id="txt_filter_text" type="text" placeholder="  Recherche Textuelle " />
                    </div>
                    <input type="button" id="btn_search" value="Lancer la recherche" />
                </div>
            </div>

        </div><!--fin header panel-->

        <div id="vesp_center_panel">
            <div id="vespa_left_panel">
                <div id="report_panel">
                    <div id="report_left_panel">
                        <h2>Les Bulletins</h2>
                        <h3>Grandes Cultures</h3>
                        <div id="report_filter">
                            <div class="report_filter_title">Années</div>
                            <div id="filter_reset_years" class="filter_reset">Toutes</div>
                            <div id="report_filter_years" class="border"></div>
                        </div>
                    </div>
                    <div id="report_right_panel">
                        <div>
                            <div id="report_count">
                                <span id="report_list_count">0 </span>/<span id="report_total_count"> 0</span>
                            </div>
                        </div>
                        <div id="report_sorter">
                            Trier :&nbsp;&nbsp;
                            <span class="report_sorter_item" sort="name">Nom</span>
                            <span class="report_sorter_item" sort="area_name">Région</span>
                            <span class="report_sorter_item selected" sort="date">Date</span>
                        </div>
                        <div id="report_list">
                        </div>
                    </div>
                </div>
            </div>
            <div id="vespa_right_panel">
                <div id="title_map"></div>
                <div id="map_container"></div>
                <div id="map_gradient">
                    <div>Nb. de bulletins</div>
                    <span id="min_count"></span>
                    <div class="color_grad" id="grad_1"></div>
                    <div class="color_grad" id="grad_2"></div>
                    <div class="color_grad" id="grad_3"></div>
                    <div class="color_grad" id="grad_4"></div>
                    <div class="color_grad" id="grad_5"></div>
                    <span id="max_count"></span>
                </div>
            </div>
        </div>
