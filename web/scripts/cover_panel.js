function cover_panel(panel_to_cover, fade_duration) {


    var cover_panel = jQuery("<div></div>")
                            .addClass("cover_up")
                            .width(panel_to_cover.width())
                            .height(panel_to_cover.height())
                            .append("<span>Loading...</span>")
                            .hide();

    cover_panel.panel_to_cover = panel_to_cover;
    cover_panel.fade_duration = fade_duration;

   

    panel_to_cover.parent().append(cover_panel);

    
    cover_panel.doShow = function () {
        cover_panel.show();
    }

    cover_panel.doHide = function () {
        cover_panel.hide();
    }

    cover_panel.set_position = function () {
        cover_panel.css({
            top: cover_panel.panel_to_cover.position().top
            , left: cover_panel.panel_to_cover.position().left
        });
    }

    cover_panel.position({
        my: "left top"
                       , at: "left top"
                       , of: cover_panel.panel_to_cover
    });

    cover_panel.doFadeIn = function (delegate) {
        cover_panel.set_position();
        cover_panel.fadeIn(cover_panel.fade_duration, delegate);
    }

    cover_panel.doFadeOut = function (delegate) {
        //cover_panel.set_position();
        if (delegate == null)
            cover_panel.fadeOut();
        else
            cover_panel.fadeOut(cover_panel.fade_duration, delegate);
    }


    return cover_panel;
}