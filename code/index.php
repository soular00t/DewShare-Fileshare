<?php require_once 'src/scripts/php/functions.php';
$pEmblem = (isset($_AUTH->avatar)) ? $_AUTH->avatar : "src/images/player/emblems/default.png";
$pName = (isset($_AUTH->uname)) ? $_AUTH->uname : "Unknown"; ?>

<!DOCTYPE html>
<html>
<head>
    <title>ElDewrito Fileshare</title>
    <meta charset="utf-8" />
    <link href="src/css/share.css" rel="stylesheet" />
    <link href="src/css/external/jquery-ui.min.css" rel="stylesheet" />
    <script type="text/javascript" src="src/scripts/external/jquery.min.js"></script>
    <script type="text/javascript" src="src/scripts/external/jquery-ui.min.js"></script>
    <script type="text/javascript" src="src/scripts/vault/main.js"></script>

</head>
<body>

    <div class="main" style="zoom: 1;">

        <div class="main_header">
            <div class="header_title_container">
                <img class="title_container_emblem" src="<?=$pEmblem;?>"/>
                <span class="title_container_name"><?=$pName;?></span>
                <img class="title_container_port" src="src/images/input/controller/port0.png"/>
            </div>
        </div>
        <div class="main_content">
            <span class="content_name_container">FILE SHARE</span>
            <div class="content_head_container">
                <div class="content_head_container_left">
                    <img class="head_container_emblem" src="src/images/player/emblems/default.png" />
                    <span class="head_container_name">Unknown</span>
                    <span class="head_container_puid">Unknown - 000</span>
                </div>
                <div class="content_head_container_right">
                    <img id="input_back" src="src/images/input/controller/lb.png" />
                    <span id="input_page"></span>
                    <img id="input_next" src="src/images/input/controller/rb.png" />
                </div>
            </div>
            <div class="content_foot_container">
                <div class="content_foot_container_left">
                    <div class="foot_container_tab_header">
                        <ul>
                            <li>
                                <span data-tab="0" class="tabItem selected">My Files</span>
                            </li>
                            <li>
                                <span data-tab="1" class="tabItem">Browse</span>
                            </li>
                            <li>
                                <span data-tab="2" class="tabItem">Recent</span>
                            </li>
                        </ul>
                    </div>
                    <div class="foot_container_tab_footer" id="thumbbar">
                        <div data-page="0" class="tabPage">
                            <div class="tab_user_slots content_slot_container">

                                <!--<div class="content_slot user">
                                    <div class="content_slot_header">
                                        <img class="content_slot_header_image map" src="src/images/maps/medium/cyberdyne.png">
                                        <img class="content_slot_header_overlay" src="src/images/overlays/large/overlay_mt.png" />
                                    </div>
                                    <div class="content_slot_footer user">
                                        <span class="content_slot_footer_left">Slot: 1</span>
                                        <a class="content_slot_footer_right" href="index.html">Remove</a>
                                    </div>
                                </div>
                                <div class="content_slot user">
                                    <div class="content_slot_header">
                                        <img class="content_slot_header_image variant">
                                        <img class="content_slot_header_variant variant" src="src/images/gametypes/large/zombiez.png">
                                        <img class="content_slot_header_overlay" src="src/images/overlays/large/overlay_gt.png" />
                                    </div>
                                    <div class="content_slot_footer user">
                                        <span class="content_slot_footer_left">Slot: 2</span>
                                        <a class="content_slot_footer_right" href="index.html">Remove</a>
                                    </div>
                                </div>
                                <div class="content_slot user">
                                    <div class="content_slot_header">
                                        <img class="content_slot_header_image screenshot" src="src/images/maps/medium/guardian.png">
                                        <img class="content_slot_header_overlay" src="src/images/overlays/large/overlay_rv.png" />
                                    </div>
                                    <div class="content_slot_footer user">
                                        <span class="content_slot_footer_left">Slot: 3</span>
                                        <a class="content_slot_footer_right" href="index.html">Remove</a>
                                    </div>
                                </div>
                                <div class="content_slot user">
                                    <div class="content_slot_header">
                                        <img class="content_slot_header_image video" src="src/images/maps/medium/shrine.png">
                                        <img class="content_slot_header_overlay" src="src/images/overlays/large/overlay_ss.png" />
                                    </div>
                                    <div class="content_slot_footer user">
                                        <span class="content_slot_footer_left">Slot: 4</span>
                                        <a class="content_slot_footer_right" href="index.html">Remove</a>
                                    </div>
                                </div>-->

                            </div>
                        </div>
                        <div data-page="1" class="tabPage" style="display:none">
                            <div class="tab_browse_slots content_slot_container">

                            </div>
                        </div>
                        <div data-page="2" class="tabPage" style="display:none">
                            <div class="tab_recent_slots content_slot_container">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="content_foot_container_right">
                    <div class="content_foot_container_right_header">
                        <div class="cnt_rightfootheader_container">
                            <img class="cnt_rightfootheader_container_image" src="src/images/maps/medium/guardian.png" />
                            <img class="cnt_rightfootheader_container_overlay" src="src/images/overlays/large/overlay_def.png" />
                        </div>
                    </div>
                    <div class="content_foot_container_right_footer">
                        <div class="cnt_rightfoot_details_top">
                            <span id="details_top-title">TITLE:</span>
                            <span id="details_top-description">DESCRIPTION:</span>
                        </div>
                        <div class="spacer"></div>
                        <div class="cnt_rightfoot_details_bottom" id="thumbbar">
                            <div class="details_bottom_entry">
                                <span id="details_bottom_entry-name">NAME_1:</span>
                                <a id="details_bottom_entry-text" href="index.html">LINK TEST</a>
                            </div>
                            <div class="details_bottom_entry">
                                <span id="details_bottom_entry-name">NAME_2:</span>
                                <span id="details_bottom_entry-text">TEXT</span>
                            </div>
                            <div class="details_bottom_entry">
                                <span id="details_bottom_entry-name">NAME_3:</span>
                                <img src="src/images/player/emblems/default.png" />
                            </div>
                            <div class="details_bottom_entry">
                                <span id="details_bottom_entry-name">NAME_4:</span>
                                <span id="details_bottom_entry-text">TEXT</span>
                            </div>
                            <div class="details_bottom_entry">
                                <span id="details_bottom_entry-name">NAME_5:</span>
                                <a id="details_bottom_entry-text" href="index.html">LINK TEST</a>
                            </div>
                            <div class="details_bottom_entry">
                                <span id="details_bottom_entry-name">NAME_6:</span>
                                <span id="details_bottom_entry-text">TEXT</span>
                            </div>
                            <div class="details_bottom_entry">
                                <span id="details_bottom_entry-name">NAME_7:</span>
                                <span id="details_bottom_entry-text">TEXT</span>
                            </div>
                            <div class="details_bottom_entry">
                                <span id="details_bottom_entry-name">NAME_8:</span>
                                <a id="details_bottom_entry-text" href="index.html">LINK TEST</a>
                            </div>
                            <div class="details_bottom_entry">
                                <span id="details_bottom_entry-name">NAME_9:</span>
                                <a id="details_bottom_entry-text" href="index.html">LINK TEST</a>
                            </div>
                            <div class="details_bottom_entry">
                                <span id="details_bottom_entry-name">NAME_10:</span>
                                <span id="details_bottom_entry-text">TEXT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="main_footer">
            <div class="footer_content_left_container">
                <ul>
                    <li class="input-button_select">
                        <img id="input_select" src="src/images/input/controller/a.png" />
                        <span>Select</span>
                    </li>
                    <li class="input-button_back">
                        <img id="input_back" src="src/images/input/controller/b.png" />
                        <span>Back</span>
                    </li>
                    <li class="input-button_details">
                        <img id="input_details" src="src/images/input/controller/y.png" />
                        <span>Details</span>
                    </li>
                </ul>
            </div>
            <div class="footer_content_right_container">
                <div id="slider" class="slider ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all">
                    <span class="ui-slider-handle ui-state-default ui-corner-all" tabindex="0" style="left: 10%;"></span>
                </div>
            </div>
        </div>
        <div class="main_overlay">
            <div class="main_overlay-header">
                <span class="main_overlay-header-title">Unknown</span>
            </div>

            <div class="main_overlay-details">

            </div>

            <div class="main_overlay-footer">

            </div>
        </div>

    </div>

    <script type="text/javascript" src="src/scripts/fantality/fan-helpers-min.js"></script>
    <script type="text/javascript" src="src/scripts/fantality/fan-base-min.js"></script>
    <script type="text/javascript" src="src/scripts/fantality/fan-slot-min.js"></script>
    <!-- not ready yet: <script type="text/javascript" src="src/scripts/share.js"></script> -->

</body>
</html>