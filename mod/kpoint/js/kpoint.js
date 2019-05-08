var player = null;
var kvt;
var videoID;
var videoduration;
var coursemoduleID;
var activity_complete_msg;
var activity_completionpending_msg;
var wait_msg;

window.onload = function () {
    //initiallize player with kpoint instance
    player = window.kPlayer;
}

/*
 *  fetch coursemoduleID ,activity comletion, activity pending and wait msg from view.php page
 */
function get_parameters(Y, cmid, complete_msg, general_errormsg, wait) {
    coursemoduleID = cmid;
    activity_complete_msg = complete_msg;
    general_error_msg = general_errormsg;
    wait_msg = wait;
}

/*
 *  check video viewed completely or not  and log video end event
 */
function onkPointPlayerReady(player) {
    videoID = player.info['kvideoId'];
    player.startVideo();
    player.addEventListener(player.events.onStateChange, function () {
        
        /*
         *  check video viewed completely or not  and log video end event
         */
        if (player.getPlayState() == player.playStates.ENDED) {
            $('#msg').css('display', 'block');
            $('#msg').html(wait_msg);
            $.ajax({
                type: "GET",
                url: "/mod/kpoint/video_view_status.php",
                data: {
                    videoID: videoID,
                    kvTocken: kvt,
                    duration: videoduration,
                    courseModuleID: coursemoduleID
                },
                dataType: "json",
                success: function (data) {
                    if (!data.isError) {
                        if (data.isComplete) {
                            $('#msg').css('display', 'block');
                            $('#msg').html(activity_complete_msg);
                        } else {
                            $('#msg').css('display', 'none');
                        }
                    } else {
                        $('#msg').css('display', 'none');
                    //    $('#msg').html(general_error_msg);
                    }
                },
                error: function () {
                    $('#msg').css('display', 'none');
                    //$('#msg').html(general_error_msg);
                }
            });
        }
    });

    /*
     *  check video viewrship
     */
    player.addEventListener(player.events.started, function (ev) {
        var ms = player.getDuration();
        videoduration = parseInt((ms / 1000));
        kvt = ev.data["kvtoken"];
        $.ajax({
            type: "GET",
            url: "/mod/kpoint/video_started_handler.php",
            data: {
                courseModuleID: coursemoduleID
            },
            dataType: "json",
            success: function (data) { },
            error: function () {
                console.log("error");
            }
        });
    });
}
