var slot = {
// Need to adjust JSON parsing of this data to reflect new fields
// & account for the ENTRIES[] array in fileshare api
    parse: function (data) {
        try {
            if (isnul(data))
                return null;
            else {
                var type = (data.type) ? data.type : (data.map) ? 'map' : '';
                switch (type.toLowerCase()) {
                    case 'map':
                        return {
                            type: 0,
                            map_id: (data.map_id) ? data.map_id : 0,
                            title: (data.title) ? data.title : '',
                            thread: (data.thread) ? data.thread : '',
                            caption: (data.caption) ? data.caption : '',
                            details: (data.info) ? data.info : '',
                            author: (data.author) ? data.author : '',
                            submitter: (data.uploader) ? data.uploader : '',
                            uid: (data.uid) ? data.uid : 11,
                            dewid: (data.dewid) ? data.dewid : 0,
                            created: (data.date) ? data.date : '',
                            updated: (data.updated) ? data.updated : '',
                            edited: (data.edited) ? data.edited : '',
                            download: (data.directURL) ? data.directURL : data.mirror,
                            data: {
                                thumb: (data.thumbnail) ? data.thumbnail : getMapImage(data.map),
                                image: (data.img) ? data.img : getMapImage(data.map),
                                gametype: data.gametype ? data.gametype : getVariantImage(data.gametype),
                                objectsLeft: (data.forgeData && data.forgeData.TotalObjectsLeft) ? data.forgeData.TotalObjectsLeft : 0,
                                objectsUsed: (data.forgeData && data.forgeData.UserObjectsPlaced) ? data.forgeData.UserObjectsPlaced : 0,
                                lastsaved: (data.forgeData && data.forgeeData.LastSaved) ? data.forgeData.LastSaved : '',
                                lasteditor: (data.forgeData && data.forgeData.LastEditor) ? data.forgeData.LastEditor : '',
                                size: (data.forgeData && data.forgeData.FileSize) ? data.forgeData.FileSize : '0 Bytes'
                            },
                            stats: {
                                views: (data.views) ? data.views : 0,
                                votes: (data.votes) ? data.votes : 0,
                                replies: (data.replies) ? data.replies : 0,
                                downloads: (data.downloads) ? data.downloads : 0
                            }
                        };
                    case 'variant':
                        return {
                            type: 1,
                            var_id: (data.mod_id) ? data.mod_id : 0,
                            title: (data.title) ? data.title : '',
                            thread: (data.thread) ? data.thread : '',
                            caption: (data.caption) ? data.caption : '',
                            details: (data.info) ? data.info : '',
                            author: (data.author) ? data.author : '',
                            submitter: (data.uploader) ? data.uploader : '',
                            uid: (data.uid) ? data.uid : 11,
                            dewid: (data.dewid) ? data.dewid : 0,
                            created: (data.date) ? data.date : '',
                            updated: (data.updated) ? data.updated : '',
                            edited: (data.edited) ? data.edited : '',
                            download: (data.directURL) ? data.directURL : data.mirror,
                            data: {
                                image: (data.variantData && data.variantData.GameImage) ? data.variantData.GameImage : 'src/images/gametypes/large/unknown.png',
                                gametype: (data.variantData && data.variantData.GameType) ? data.variantData.GameType : 'None',
                                description: (data.variantData && data.variantData.Description) ? data.variantData.Description : '',
                                quote: (data.variantData && data.variantData.GameQuote) ? data.variantData.GameQuote : '',
                                lastsavedby: (data.variantData && data.variantData.LastSavedBy) ? data.variantData.LastSavedBy : '',
                                weappickup: (data.variantData && data.variantData.WeapPickup) ? data.variantData.WeapPickup : 'false',
                                dmgdealer: (data.variantData && data.variantData.DmgDealer) ? data.variantData.DmgDealer : '100%',
                                dmgresistance: (data.variantData && data.variantData.DmgResistance) ? data.variantData.DmgResistance : '100%',
                                shieldmulti: (data.variantData && data.variantData.ShieldMulti) ? data.variantData.ShieldMulti : 'Normal',
                                playerspeed: (data.variantData && data.variantData.PlayerSpeed) ? data.variantData.PlayerSpeed : '100%',
                                primeweapon: (data.variantData && data.variantData.PrimeWeapon) ? data.variantData.PrimeWeapon : 'Unknown',
                                secondweapon: (data.variantData && data.variantData.SecondWeapon) ? data.variantData.SecondWeapon : 'Unknown',
                                lastsavedate: (data.variantData && data.variantData.LastSaveDate) ? data.variantData.LastSaveDate : '',
                                modifiedonserver: (data.variantData && data.variantData.ModifiedOnServer) ? data.variantData.ModifiedOnServer : '',
                                size: (data.variantData && data.variantData.FileSize) ? data.variantData.FileSize : '0 Bytes',
                                verified: (data.variantData && data.variantData.isVariant) ? data.variantData.isVariant : false,
                                subvariant: (data.subTitle) ? true : false
                            },
                            stats: {
                                views: (data.views) ? data.views : 0,
                                votes: (data.votes) ? data.votes : 0,
                                replies: (data.replies) ? data.replies : 0,
                                downloads: (data.downloads) ? data.downloads : 0
                            },

                        };
                    case 'screeenshot':
                        return {
                            type: 2,
                            media_id: (data.media_id) ? data.media_id : 0,
                            title: (data.name) ? data.name : '',
                            thread: (data.thread) ? data.thread : '',
                            caption: (data.caption) ? data.caption : '',
                            details: (data.caption) ? data.caption : '',
                            author: (data.author) ? data.author : '',
                            submitter: (data.uploader) ? data.uploader : '',
                            uid: (data.uid) ? data.uid : 11,
                            dewid: (data.dewid) ? data.dewid : 0,
                            created: (data.date) ? data.date : '',
                            updated: (data.updated) ? data.updated : '',
                            edited: (data.edited) ? data.edited : '',
                            data: {
                                image: (data.url) ? data.url : '',
                                thumb: (data.url) ? data.url + '&w=204&h=116' : '',
                                type: (data.screenshotData) ? (data.screenshotData.MimeType) ? data.screenshotData.MimeType : '' : '',
                                width: (data.screenshotData) ? (data.screenshotData.PhotoWidth) ? data.screenshotData.PhotoWidth : '0px' : '0px',
                                height: (data.screenshotData) ? (data.screenshotData.PhotoHeight) ? data.screenshotData.PhotoHeight : '0px' : '0px',
                                size: (data.screenshotData) ? (data.screenshotData.FileSize) ? data.screenshotData.FileSize : '0 Bytes' : '0 Bytes',
                            },
                            stats: {
                                views: (data.views) ? data.views : 0,
                                votes: (data.votes) ? data.votes : 0,
                                replies: (data.replies) ? data.replies : 0
                            }
                        };
                    case 'video':
                        return { // not available yet
                            type: 3
                        };
                    default:
                }
            }
        }
        catch (e) {
            console.log(e);
            return null;
        }
    },
    create: function (type = "", cache) {
        try {
            if (isnul(cache))
                return null;
            else {
                var slotData = document.createElement('div');
                    slotData.setAttribute("data", JSON.stringify(cache));
                    slotData.setAttribute("class", (type) ? 'content_slot ' + type : 'content_slot');

                switch (cache.type) {
                    case 0:
                        slotData.innerHTML =
                            '<div class="content_slot_header">' +
                                '<img class="content_slot_header_image map" src="' + cache.data.image + '"/>' +
                                '<img class="content_slot_header_overlay" src="src/images/overlays/large/overlay_mt.png" />' +
                            '</div>';
                        break;
                    case 1:
                        slotData.innerHTML =
                            '<div class="content_slot_header">' +
                                '<img class="content_slot_header_variant variant" src="' + cache.data.image + '"/>' + 
                                '<img class="content_slot_header_overlay" src="src/images/overlays/large/overlay_gt.png" />' +
                            '</div>';
                        break;
                    case 2:
                        slotData.innerHTML =
                            '<div class="content_slot_header">' +
                                '<img class="content_slot_header_image screenshot" src="' + cache.data.thumb + '"/>' +
                                '<img class="content_slot_header_overlay" src="src/images/overlays/large/overlay_ss.png" />' +
                            '</div>';
                        break;
                    case 3: // not available yet
                    default:
                        return null;
                }
                return (slotData.innerHTML) ? slotData : null;
            }
        }
        catch (e) {
            console.log(e);
            return null;
        }
    },
    clear: function (type) {
        try {
            type = parseInt(type);
            switch (type) {
                case 0:
                    $('.tab_user_slots').empty();
                    $('.tab_user_slots').off('click', '.content_slot_header_overlay');
                    $('.tab_user_slots').off('mouseenter', '.content_slot_header_overlay');
                    $('.tab_user_slots').off('mouseleave', '.content_slot_header_overlay');
                    break;
                case 1:
                    $('.tab_browse_slots').empty();
                    $('.tab_browse_slots').off('click');
                    break;
                case 2:
                    $('.tab_recent_slots').empty();
                    $('.tab_recent_slots').off('click');
                    break;
                case 3: // not available yet
                    $('.tab_recent_slots').empty();
                    $('.tab_recent_slots').off('click');
                    break;
                default:
                    console.log('[Slot.Clear] - Unknown type was specified!');
            }
        }
        catch (e) {
            console.log(e);
        }
    }

};
var slotConfig = {
    'selectedSlot': null
};
var slotSettings = {
    'debug': true,
    'maxSlots': 24,
    'endpoints': {
        'user':   '/src/scripts/php/share.api.php?p=1&r=23&type=0&author=',
        'browse': '/src/scripts/php/share.api.php?p=1&r=23&type=0&gametype=',
        'recent': '/src/scripts/php/share.api.php?p=1&r=23&type=0&o=date'
    }
};


function loadUser(user) {
    try {
        if (isnul(user))
            return;
        else {
            slot.clear(0);
            getRequest(slotSettings.endpoints.user + user, function (data) {

                if (isnul(data))
                    return;
                else {
                    var slotData  = null;
                    var slotCache = null;
                    for (var i = 0; i < data.ENTRIES.length; i++) {

                        slotData = slot.parse(data.ENTRIES[i]);
                        if (isnul(slotData))
                            continue;
                        slotCache = slot.create("user", slotData);
                        if (isnul(slotCache))
                            continue;

                        $('.tab_user_slots').append($(slotCache)
                            .click(function (e) {
                                try {
                                    if (isnul($(e.currentTarget).attr('data')))
                                        return;
                                    else {
                                        var slotDetails = JSON.parse($(e.currentTarget).attr('data'));
                                        if (isnul(slotDetails))
                                            return;
                                        else {

                                            slotConfig.selectedSlot = {
                                                data: slotDetails,
                                                hasDetails: isnul(slotDetails.details) === false
                                            };

                                            $('#details_top-title').text(slotDetails.title);
                                            $('#details_top-description').text(slotDetails.caption);
                                            $('.cnt_rightfootheader_container_image').attr('src', slotDetails.data.image);
                                            $('.input-button_details').css({
                                                'display': (slotConfig.selectedSlot && slotConfig.selectedSlot.hasDetails)
                                                    ? 'flex'
                                                    : 'none'
                                            });

                                        }
                                    }                                    
                                }
                                catch (e) {
                                    console.log(e);
                                }
                            })
                            .mouseenter(function (e) {
                                $(e.currentTarget).css({ 'opacity': '1' });
                            })
                            .mouseleave(function (e) {
                                $(e.currentTarget).css({ 'opacity': '0.7' });
                            })
                        );

                    }
                }

            });
        }
    }
    catch (e) {
        console.log(e);
        slot.clear(0);
        slotConfig.selectedSlot = null;
    }
};

$(document).ready(function () {
    try {
        document.oncontextmenu = new Function((slotSettings.debug) ? "return true" : "return false");
        //setTimeout(loadUser('Twigzie'), 1000);
    }
    catch (e) {
        console.log(e);
    }
});
$(document).keydown(function (e) {
    try {
        var code = parseInt(e.keyCode);
        switch (code) {
            case 192: console.log('[DEBUG] has been pressed.'); break; //DEBUG
            case 27:
             if ($('.main_overlay').hasClass('active'))
              $('.main_overlay').removeClass('active');
             break; //ESC
            case 8:   console.log('[BACK] has been pressed.');  break; //BACK
            case 13:  console.log('[ENTER] has been pressed.'); break; //ENTER
            case 38:  console.log('[UP] has been pressed.');    break; //UP
            case 40:  console.log('[DOWN] has been pressed.');  break; //DOWN
            case 37:  console.log('[LEFT] has been pressed.');  break; //LEFT
            case 39:  console.log('[RIGHT] has been pressed.'); break; //RIGHT
            case 89:
                if (!slotConfig.selectedSlot && slotConfig.selectedSlot.hasDetails)
                    return;
                else {
                    $('.main_overlay').addClass('active');
                    $('.main_overlay-header-title').text('FILE DETAILS');
                }
                break; //Y
            default:
        }
    }
    catch (e) {
        console.log(e);
    }
});
$(document).mouseup(function (e) {
    try {
        var code = parseInt(e.which);
        switch (code) {
            case 1: console.log('[M-LEFT] has been pressed.');   break; //LEFT BUTTON
            case 2: console.log('[M-MIDDLE] has been pressed.'); break; //MIDDLE BUTTON
            case 3: console.log('[M_RIGHT] has been pressed.');  break; //RIGHT BUTTON
            default:
        }
    }
    catch (e) {
        console.log(e);
    }
});