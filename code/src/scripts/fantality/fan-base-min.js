var Tab = {
    update: function (event) {

        var tab = $(event.currentTarget).data('tab');
        var tabs = document.getElementsByClassName("tabItem");
        for (var i = 0; i < tabs.length; i++)
            $(tabs[i]).removeClass('selected');

        var pages = document.getElementsByClassName("tabPage");
        for (var i = 0; i < pages.length; i++) {
            var page = pages[i];
            page.style.display = 'none';
            if ($(page).data('page') === tab)
                $(page).css({ 'display': 'block' });
        }

        switch (parseInt(tab)) {
            case 0: Slot.loadUser('TheDarkConduit'); break;
            case 1: Slot.loadBrowse(); break;
            case 2: Slot.loadRecent(); break;
            default:
                break;
        }

        $(event.currentTarget).addClass('selected');

    }
}
var Settings = {
    gamePorts: [
        'src/img/controller/controller_port_1.png', //0
        'src/img/controller/controller_port_2.png', //1
        'src/img/controller/controller_port_3.png', //2
        'src/img/controller/controller_port_4.png'  //3
    ],
    gameOffset: parseInt(isset(localStorage.getItem('overlayOffset'), 8)),
    gameOffsets: [
        '75px 75px 75px 0px', //left
        '75px 0px 75px 75px', //right
        '0px 75px 75px 75px', //top
        '75px 75px 0px 75px', //bottom
        '0px 75px 75px 0px',  //topleft
        '0px 0px 75px 75px',  //topright
        '75px 75px 0px 0px',  //bottomleft
        '75px 0px 0px 75px',  //bottomright
        '75px',               //center
        '0px'                 //fill
    ],
    gameZoom: {
        minZoom: (typeof (Storage) !== "undefined" && localStorage.getItem("zoomMinimum") !== null) ? parseFloat(localStorage.getItem("zoomMinimum")) : 0.5,
        maxZoom: (typeof (Storage) !== "undefined" && localStorage.getItem("zoomMaximum") !== null) ? parseFloat(localStorage.getItem("zoomMaximum")) : 2,
        value: (typeof (Storage) !== "undefined" && localStorage.getItem("zoomValue") !== null) ? parseFloat(localStorage.getItem("zoomValue")) : 1,
        increment: (typeof (Storage) !== "undefined" && localStorage.getItem("zoomIncrement") !== null) ? parseFloat(localStorage.getItem("zoomIncrement")) : 0.05,
    },
    load: function () {

        $('.tabItem').click(function (e) { Tab.update(e); });

        if (Settings.gameZoom) {
            $("#slider").slider({
                value: Settings.gameZoom.value,
                min: Settings.gameZoom.minZoom,
                max: Settings.gameZoom.maxZoom,
                step: Settings.gameZoom.increment,
                slide: function (event, ui) {
                    localStorage.setItem("zoomValue", ui.value);
                    $('.main').stop().animate({ zoom: ui.value }, 150);
                    $("#slider").slider({ value: ui.value });
                }
            });
            $('.main').css({ 'zoom': Settings.gameZoom.value });
        }

    }
}

$(document).ready(function () {

    console.log('Initializing [Base] - ' + new Date().toLocaleTimeString());

    Settings.load();

});