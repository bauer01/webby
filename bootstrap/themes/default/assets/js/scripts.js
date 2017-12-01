$(function () {
    $(document).on("submit", "form.ajax", function(e) {

        var form$ = $(this);
        var url = form$.prop('action');
        var values = form$.serializeArray();
        var sendValues = {};

        for (var i = 0; i < values.length; i++) {
            var name = values[i].name;

            // multi
            if (name in sendValues) {
                var val = sendValues[name];

                if (!(val instanceof Array)) {
                    val = [val];
                }

                val.push(values[i].value);
                sendValues[name] = val;
            } else {
                sendValues[name] = values[i].value;
            }
        }

        form$.find(':input').prop("disabled", true);

        $.ajax({
            type: form$.prop('method'),
            url: url ? url : window.location.href,
            data: sendValues
        }).done(function(data) {
            $.each(data, function(id, html) {
                $("#" + id).html(html);
            });
        }).always(function () {
            form$.find(':input').prop("disabled", false);
        });

        e.preventDefault();
    });

    $(document).on("click", "a.ajax", function(e) {

        e.preventDefault();

        var url = $(this).prop("href");
        $.get(url ? url : window.location.href).done(function (data) {
            $.each(data, function(id, html) {
                $("#" + id).html(html);
            });
        }).always(function () {

        });
    });
});
