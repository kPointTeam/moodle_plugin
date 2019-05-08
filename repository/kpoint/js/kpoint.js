/*
 *  js to vaildate given credentials
 */
var moodleroot;
require(['jquery'], function ($) {
  // JQuery is available via $

    $(document).ready(function () {
            $('#id_submitbutton').attr('disabled', 'disabled');

            $('#id_client_id, #id_secret, #id_domain').on('input', function () {
                $('#id_submitbutton').attr('disabled', 'disabled');
                removeMark();
            });
            $('#id_account_no').change('input', function () {
                $('#id_submitbutton').attr('disabled', 'disabled');
                removeMark();
            });
            $('#id_email').change('input', function () {
                $('#id_submitbutton').attr('disabled', 'disabled');
                removeMark();
                var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if(!regex.test($(this).val())){
                    $('#id_btn_test').attr('disabled', 'disabled');
                    $('#error_email').remove();
                    $("#id_btn_test").after("<div id='error_email' width=100%  style=' color:red;'>Invalid Email..</div>");
                }
                else {
                    $('#id_btn_test').removeAttr('disabled');
                    $('#error_email').remove();
                }
            });

            $('#id_auth_via_accountno').change(function() {
                $('#id_submitbutton').attr('disabled', 'disabled');
                removeMark();

                if($(this). prop("checked") == true){
                    $("#id_account_no").after('<div id="lbl_accountno_required"><label  style="color:#e21d1d;padding-top:10px;">Account Number is a required field</label></div>');
                }
                else {
                    $('#id_account_no').next('#lbl_accountno_required').remove();
                }
            });
            testCredentials();
        });
  });

function get_moodleroot(Y,root)
{
    moodleroot=root;
}

/*
 *  test credentials
 */
function testCredentials() {
    removeMark();
    
    let formData = $('#id_client_id')
    .closest('form')
    .serialize();
    
    removeMark();
    
    if((($('#id_auth_via_accountno'). prop("checked") == true) && ($('#id_account_no').val()!=="")) || ($('#id_auth_via_accountno'). prop("checked") == false)){
           
        $("#id_btn_test")
        .after("<img class='correct_wrong' src='" + moodleroot + "/repository/kpoint/pix/loading.gif' />");

        $.ajax({
            type: "POST",
            url: moodleroot+"/repository/kpoint/testcredentials.php",
            data: formData,
            dataType: "json",
            success: function (data) {
                $('#id_btn_test').next('div').remove();
                $('#id_btn_test').next('img').remove();
                if(!data.isError) {
                    if (data.isValid) {
                        $("#id_btn_test").after("<img class='correct_wrong' src='" + moodleroot + "/repository/kpoint/pix/correct.png' style='padding-left: 10px;' />");
                        $('#id_submitbutton').removeAttr('disabled');
                    } else {
                        $("#id_btn_test").after("<img class='correct_wrong' src='" + moodleroot +"/repository/kpoint/pix/wrong.png' style='padding-left: 10px;' />");
                        $('#id_submitbutton').attr('disabled', 'disabled');
                    }

                }
                else {
                    $("#id_btn_test").after("<img class='correct_wrong' src='" + moodleroot +"/repository/kpoint/pix/wrong.png' style='padding-left: 10px;' />");
                    $('#id_submitbutton').attr('disabled', 'disabled');
                    $('#error_msg').remove();
                    $(".correct_wrong").after("<div id='error_msg' width=100%  style=' color:red;'>"+data.error_msg+"</div>");
                }
            },
            error: function () {
                console.log("net disconnected");
                $('#id_submitbutton').attr('disabled', 'disabled');
                $('#error_msg').remove();
                $(".correct_wrong").after("<div id='error_msg' width=100%  style=' color:red;'>Network Connection Failed..</div>");
            }
        });
    }
    else { 
        $("#id_btn_test").after("<img class='correct_wrong' src='" + moodleroot +"/repository/kpoint/pix/wrong.png' style='padding-left: 10px;' />");
        $('#id_submitbutton').attr('disabled', 'disabled');
    }
}

/*
 *  remove marks
 */
function removeMark() {
    $('#id_btn_test').next('.correct_wrong').remove();
    $('#error_msg').remove();
}

function postData(url = '', data = {}) {

  // Default options are marked with *
    return fetch(url, {
        method: "POST", // *GET, POST, PUT, DELETE, etc.
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
        credentials: "same-origin", // include, *same-origin, omit
        headers: {
          "Content-Type": "application/json; charset=utf-8",
        },
        redirect: "follow", // manual, *follow, error
        referrer: "no-referrer", // no-referrer, *client
        body: JSON.stringify(data), // body data type must match "Content-Type" header
    })
    .then(response => response.json()); // parses response to JSON
}
