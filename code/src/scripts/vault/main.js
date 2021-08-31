$(document).ready(function () {
    authGetLogin(); 
    $.getScript("/src/scripts/vault/512.js");
    $.getScript("/src/scripts/vault/d5.js");
    $.getScript("dew://lib/dew.js").done(function () {
        dew.on('show', function (d) {
            dew.command('player.name', {}).then(function (e) {
                $('#loginForm').find( "input[name='uname']" ).val() = e;
                document.cookie = "dewName=" + document.createTextNode(e === undefined ? 'Unknown' : e) + ";";
            });
            dew.command('player.uid', {}).then(function (f) {
                document.cookie = "dewUID=" + document.createTextNode(f === undefined ? 'Unknown' : f) + ";";
            });
        });
    });
    
    (function() {
      'use strict';
      document.body.addEventListener('click', copy, true);
      function copy(e) {
        var
          t = e.target,
          c = t.dataset.copytarget,
          inp = (c ? document.querySelector(c) : null);
        if (inp && inp.select) {
          inp.select();
          try {
            document.execCommand('copy');
            inp.blur();
          }
          catch (err) {
            alert('please press Ctrl/Cmd+C to copy');
          }
        }
      }

    })();
});

this.authGetLogin = function(callback) { 
    try {
        $.ajax({
            url: "http://haloshare.org/inc/api/isLogged.api" + '?callback=?',
            type:"GET", dataType:"jsonp", crossDomain:true, jsonp:'json_callback',
            success: function (data) {
                if (!data || !data.uname) {
                    if (callback) callback(false);
                    document.cookie = "hvAuth=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                }
                else {
                    var auth = data;
                    if (callback) callback(true);
                    document.cookie = "hvAuth="+btoa(btoa(JSON.stringify(auth)))+";";
                }
            },
            error: function (error) {
                if (callback) callback(false);
                document.cookie = "hvAuth=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            }
        });
    } 
    catch (ex) {
        console.log(ex.message);
        if (callback) callback(false);
    }
} 

this.tryLogin = function() {
    $("#add_err").css('display', 'none', 'important');
    try {
        var username = $('#loginForm').find( "input[name='uname']" ).val();
        var password = $('#loginForm').find( "input[name='pass']" ).val();
        $.ajax({
            type: "GET", dataType:"jsonp", crossDomain:true, jsonp:'json_callback',
            url: "http://haloshare.org/inc/authorize.php" + '?callback=?',
            data: "a="+btoa(username)+"&z="+btoa(md5(password)),
            success: function(logcheck){
                if(!logcheck) {
                    $("#add_err").css('display', 'inline', 'important');
                    $("#add_err").html("<span style='color:orange;'>Error! Try Again.</span>");
                }
                else {
                    $("#add_err").html("<span style='color:green;'>CORRECT</span>");
                    window.location = 'loop.html';
                }
            },
            beforeSend:function() {
                $("#add_err").css('display', 'inline', 'important');
                $("#add_err").html("Authorizing...")
            },
            error:function (error) {
                console.log(error);
                $("#add_err").css('display', 'inline', 'important');
                $("#add_err").html("<span style='font-size:x-small; color:red;'>Error! Try Again.</span>");

            }
        });
        return false;
    }
    catch (exx) {
        console.log(exx.message);
    }
}