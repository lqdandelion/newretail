
$("#maniForm").submit(function (envent)
{
    envent.preventDefault();

    var form = $(this);
    $.ajax({
        url: form.attr("action"),
        type: form.attr("mathod"),
        data: form.serialize(),
        dataType: "json",
        beforeSend: function ()
        {
            $("#ajax-loader").show();
        },
        error: function ()
        {

        },
        complete:function () {
            $("#ajax-loader").hide();
        },
        success: function (data)
        {
            $("#article").html(data);
        }
    });
});