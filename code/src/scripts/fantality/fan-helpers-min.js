function getRequest(u, callback) {
    try {
        $.ajax({
            url: u,
            type: "GET",
            dataType: "json",
            error: function (error) {
                callback(null);
            },
            success: function (data) {
                if (data)
                    callback(data);
                else
                    callback(null);
            }
        });
    }
    catch (e) {
        console.log(e);
        callback(null);
    }
}
function getParentFromTarget(target, name) {
    try {
        var p = target;
        if (p) {
            if (p.classList[0] === name)
                return p;
            else
                return getParentFromTarget(p.parentNode, name);
        }
        else return null;
    }
    catch (e) {
        console.log(e);
        return null;
    }
}
function getTypeFromSlotData(cache) {
    try
    {
        if (!cache)
            return -1;
        else {
            if (cache.map) return 0;        
            if (cache.type) {
                if (cache.type == "variant")    return 1;
                if (cache.type == "screenshot") return 2;
            }
            if (cache.caption) return 2;
            return -1;
        }
    }
    catch (e) {
        console.log(e);
        return -1;
    }
}

function getMapImage(name) {
    if (name) {
        var target = name.toLowerCase();
        if (target.includes("bunkerworld") || target.includes("standoff")      || target.includes("stand off"))    return "src/images/maps/medium/bunkerworld.png";
        if (target.includes("cyberdyne")   || target.includes("the pit")       || target.includes("thepit"))       return "src/images/maps/medium/cyberdyne.png";
        if (target.includes("deadlock")    || target.includes("high ground")   || target.includes("highground"))   return "src/images/maps/medium/deadlock.png";
        if (target.includes("turf")        || target.includes("s3d_turf")      || target.includes("icebox"))       return "src/images/maps/medium/turf.png";
        if (target.includes("zanzibar")    || target.includes("lastresort")    || target.includes("last resort"))  return "src/images/maps/medium/zanzibar.png";
        if (target.includes("avalanche")   || target.includes("s3d_avalanche") || target.includes("diamondback"))  return "src/images/maps/medium/avalanche.png";
        if (target.includes("chill")       || target.includes("narrows"))       return "src/images/maps/medium/chill.jpg";
        if (target.includes("edge")        || target.includes("s3d_edge"))      return "src/images/maps/medium/edge.jpg";
        if (target.includes("riverworld")  || target.includes("valhalla"))      return "src/images/maps/medium/riverworld.jpg";
        if (target.includes("shrine")      || target.includes("sandtrap"))      return "src/images/maps/medium/shrine.jpg";
        if (target.includes("hangem-high") || target.includes("hang"))          return "src/images/maps/medium/hangem-high.jpg";
        if (target.includes("reactor")     || target.includes("s3d_reactor"))   return "src/images/maps/medium/reactor.jpg";
        if (target.includes("flatgrass")) return "src/images/maps/medium/flatgrass.jpg";
        if (target.includes("guardian"))  return "src/images/maps/medium/guardian.jpg";
        if (target.includes("lockout"))   return "src/images/maps/medium/lockout.jpg";
        if (target.includes("mainmenu"))  return "src/images/maps/medium/mainmenu.jpg";
        if (target.includes("station"))   return "src/images/maps/medium/station.jpg";
        if (target.includes("unknown"))   return "src/images/maps/medium/unknown.jpg";
        else return "src/images/maps/medium/unknown.jpg";
    }
    return "src/images/maps/medium/unknown.jpg";
}
function getMapQuote(name) {
    if (name) {
        name = name.toLowerCase();
        if (name.includes("bunkerworld") || name.includes("standoff")      || name.includes("stand off"))    return "Once, nearby telescopes listened for a message from the stars. Now, these silos contain our prepared response.";
        if (name.includes("cyberdyne")   || name.includes("the pit")       || name.includes("thepit"))       return "Software simulations are held in contempt by the veteran instructors who run these training facilities.";
        if (name.includes("deadlock")    || name.includes("high ground")   || name.includes("highground"))   return "A relic of older conflicts, this base was reactivated after the New Mombasa Slipspace Event.";
        if (name.includes("turf")        || name.includes("s3d_turf")      || name.includes("icebox"))       return "Downtown Tyumen's Precinct 13 offers an ideal context for urban combat training.";
        if (name.includes("zanzibar")    || name.includes("lastresort")    || name.includes("last resort"))  return "Remote industrial sites like this one are routinely requisitioned & used as part of Spartan training exercises.";
        if (name.includes("avalanche")   || name.includes("s3d_avalanche") || name.includes("diamondback"))  return "Hot winds blow over what should be a dead moon. A reminder of the power Forerunners once wielded.";
        if (name.includes("chill")       || name.includes("narrows"))       return "Without cooling systems such as these, excess heat from the Ark's forges would render the construct uninhabitable.";
        if (name.includes("edge")        || name.includes("s3d_edge"))      return "The remote frontier world of Partition has provided this ancient databank with the safety of seclusion.";
        if (name.includes("riverworld")  || name.includes("valhalla"))      return "The crew of V-398 barely survived their unplanned landing in this gorge, but they know they are not alone.";
        if (name.includes("shrine")      || name.includes("sandtrap"))      return "Although the Brute occupiers have been driven from this ancient structure, they left plenty to remember them by.";
        if (name.includes("hangem-high") || name.includes("hang"))          return "src/images/maps/medium/hangem-high.png";
        if (name.includes("reactor")     || name.includes("s3d_reactor"))   return "Being constructed just prior to the Invasion, its builders had to evacuate before it was completed.";
        if (name.includes("flatgrass")) return "Modders offering a plain flat map with an extended pallet of items ideal for Forge.";
        if (name.includes("guardian"))  return "Millennia of tending has produced trees as ancient as the Forerunner structures they have grown around.";
        if (name.includes("lockout"))   return "Some believe this remote facility was once used to study the Flood. But few clues remain amidst the snow and ice.";
        if (name.includes("mainmenu"))  return "A custom map imported by a modder of the Halo Online community";
        if (name.includes("station"))   return "A custom map imported by a modder of the Halo Online community";
        if (name.includes("unknown"))   return "A custom map imported by a modder of the Halo Online community";
        return "A custom map imported by a modder of the Halo Online community";
    }
    return "A custom map imported by a modder of the Halo Online community";
}
function getVariantImage(name) {
    if (name) {
        name = name.toLowerCase();
        if (name.includes("oddball"))           return "src/images/gametypes/oddball.png";
        if (name.includes("slayer"))            return "src/images/gametypes/slayer.png";
        if (name.includes("infection"))         return "src/images/gametypes/infection.png";
        if (name.includes("assault"))           return "src/images/gametypes/assault.png";
        if (name.includes("king of the hill"))  return "src/images/gametypes/koth.png";
        if (name.includes("juggernaut"))        return "src/images/gametypes/juggernaut.png";
        if (name.includes("territories"))       return "src/images/gametypes/territories.png";
        if (name.includes("vip"))               return "src/images/gametypes/vip.png";
        if (name.includes("capture the flag"))  return "src/images/gametypes/ctf.png";
        if (name.includes("forge"))             return "src/images/gametypes/forge.png";
        return "src/images/gametypes/unknown.png";
    }
    return "src/images/gametypes/unknown.png";
}
function getVariantQuote(name) {
    if (name) {
        name = name.toLowerCase();
        if (name.includes("oddball"))           return "src/images/gametypes/oddball.png";
        if (name.includes("slayer"))            return "src/images/gametypes/slayer.png";
        if (name.includes("infection"))         return "src/images/gametypes/infection.png";
        if (name.includes("assault"))           return "src/images/gametypes/assault.png";
        if (name.includes("king of the hill"))  return "src/images/gametypes/koth.png";
        if (name.includes("juggernaut"))        return "src/images/gametypes/juggernaut.png";
        if (name.includes("territories"))       return "src/images/gametypes/territories.png";
        if (name.includes("vip"))               return "src/images/gametypes/vip.png";
        if (name.includes("capture the flag"))  return "src/images/gametypes/ctf.png";
        if (name.includes("forge"))             return "src/images/gametypes/forge.png";
        return "src/images/gametypes/unknown.png";
    }
    return "src/images/gametypes/unknown.png";
}
function getErrorImage(type) {
    if (type) {
        return "";
    }
    return "";
}

function isnul(value) {
    return (value === undefined && value === null);
}
function isset(value, fallback) {
    return (value !== undefined &&
            value !== null && 
            value !== "") 
        ? value
        : fallback;
}

$(document).ready(function () {

    console.log('Initializing [Helpers] - ' + new Date().toLocaleTimeString());

});