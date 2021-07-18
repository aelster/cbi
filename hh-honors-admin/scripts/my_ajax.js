var format = "json";
var cellId;
$(document).ready(function() {
    $(".ajax").on("change",function() {
        if( typeof reTabulate === "function" ) {
            reTabulate();
        };
        cellId = "#" + $(this).attr('id');
        var userId = $("#userId").val();
        var flag = $(this).val();
        $(this).value = ! flag;
        $.ajax({
            type: "POST",
            url: "ajax-update.php",
            dataType: format,
            data: {type:format, userId:userId, id:$(this).attr('id'), val:$(this).val()},
        }).done(function(req,status,err) {
            var elem = $(cellId).get(0).nodeName;
            if( elem == "SELECT") {
                $(cellId + ' select')
                    .html(req.val)
                    .css("background-color","#FFF");
            } else {
                $(cellId)
                    .val(req.val)
                    .css("background-color","#FFF");
            }
            ;
        }).fail(function(msg) {
            $(cellId)
                    .css("background-color", "red")
            ;
            $("#statusBox")
                    .css("display", "block")
                    .html("Please fix the highlighted entry")
        })
    })
})